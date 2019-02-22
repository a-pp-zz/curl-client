<?php
namespace AppZz\Http\CurlClient;
use AppZz\Http\CurlClient;
use AppZz\Helpers\Arr;

class Response {

    private $_request;
    private $_body;
    private $_rawbody;
    private $_info;
    private $_headers;
    private $_log;

    public function __construct ($request, $verbose = FALSE)
    {
        $this->_request = $request;
        $this->_execute($verbose);
    }

    public function get_status ()
    {
        return (int) Arr::get ($this->_info, 'http_code', 0);
    }

    public function get_body ()
    {
        return $this->_body;
    }

    public function get_rawbody ()
    {
        return $this->_rawbody;
    }

    public function get_info ($param = NULL)
    {
        if ($this->_request) {
            if (empty($param)) {
                return $this->_info;
            } else {
                return Arr::get ($this->_info, $param);
            }
        }

        return FALSE;
    }

    public function get_headers ()
    {
        return $this->_headers;
    }

    public function get_log ()
    {
        return $this->_log;
    }

    /**
     * Download file
     * @param  string  $download_path download dir or file
     * @param  string  $filename      filename
     * @param  boolean $rename        Rename to real extension or no
     * @return mixed
     */
    public function download ($download_path = '/tmp', $filename = NULL, $rename = FALSE)
    {
        if (file_exists ($download_path) AND is_dir ($download_path) AND is_writeable ($download_path)) {
            $filename = empty ($filename) ? basename($this->_url) : $filename;
            $download_path .= DIRECTORY_SEPARATOR . $filename;
        }
        elseif (file_exists ($download_path) AND ! is_writeable ($download_path)) {
            return FALSE;
        }
        elseif ( ! is_writeable (dirname($download_path))) {
            return FALSE;
        }

        if ($this->get_status() === 200) {
            file_put_contents ($download_path, $this->get_body());

            if ($rename) {
                $headers = $this->get_headers();
                $content_type = $headers->offsetGet ('content-type');
                $mime = new CurlClient\Mime;

                $ext = $mime->get_ext_by_mime ($content_type);

                if ($ext) {
                    $pi = pathinfo ($download_path);
                    $new_filename = sprintf ('%s/%s.%s', $pi['dirname'], $pi['filename'], $ext);

                    if (rename ($download_path, $new_filename)) {
                        return $new_filename;
                    } else {
                        return FALSE;
                    }
                }
            }

            return $download_path;
        }

        return FALSE;
    }

    private function _execute ($verbose = FALSE)
    {
        if ($this->_request) {
            $this->_body = $this->_rawbody = curl_exec ($this->_request);
            $this->_info = curl_getinfo ($this->_request);
            $this->_parse_headers();
            $this->_populate_body();
            curl_close ($this->_request);

            if ($verbose) {
                rewind ($verbose);
                $this->_log = stream_get_contents ($verbose);
            }

            return TRUE;
        }

        return FALSE;
    }

    private function _parse_headers ()
    {
        $headers_size = Arr::get ($this->_info, 'header_size', 0);
        $headers = [];

        if ($headers_size > 0) {

            $lines = array_slice(explode("\r\n", trim(substr($this->_rawbody, 0, $headers_size))), 1);
            $cookies = new CurlClient\Cookies;

            foreach ($lines as $line) {
                if (strpos(trim($line), ': ') !== FALSE ) {
                    list($key, $value) = explode(': ', $line);
                    $key = mb_strtolower ($key);

                    if ($key == 'content-disposition' AND preg_match ('#filename\="(.*)"#iu', $value, $pr ) ) {
                        $headers['content-disposition-filename'] = $pr[1];
                    }

                    if ($key == 'set-cookie') {
                        //$headers['cookies_raw'][] = $value;
                        $cookies->add_cookie ($value);
                    } else {
                        $headers[$key] = $value;
                    }
                }
            }

            if ($cookies->count()) {
                $headers['cookies'] = $cookies;
            }

            if (sizeof ($headers) > 0) {
                $this->_body = mb_substr ($this->_rawbody, $headers_size);
                $this->_headers = new Headers ($headers);
            }
        }

        return (bool) $headers_size;
    }

    private function _populate_body ()
    {
        $content_type = $this->_headers ? $this->_headers->offsetGet ('content-type') : null;

        if (strpos($content_type, 'json')) {
            $this->_body = json_decode ($this->_body, TRUE);
        }
        elseif (class_exists('\SimpleXMLElement') AND strpos($content_type, 'xml')) {
            $object = simplexml_load_string ($this->_body, "SimpleXMLElement", LIBXML_NOCDATA);
            $this->_body = json_decode(json_encode($object), TRUE);
            unset ($object);
        }

        return $this;
    }
}
