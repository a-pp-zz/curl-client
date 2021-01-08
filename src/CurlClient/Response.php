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
    private $_multi = FALSE;
    private $_verbose = FALSE;
    private $_get_content_func;

    public function __construct ($request, $multi = FALSE)
    {
        $this->_request = $request;
        $this->_multi   = (bool)$multi;
        $this->_get_content_func = $this->_multi ? 'curl_multi_getcontent' : 'curl_exec';
    }

    public static function factory ($request, $multi = FALSE)
    {
        return new Response ($request, $multi);
    }

    public function verbose ($verbose = FALSE)
    {
        $this->_verbose = $verbose;
        return $this;
    }

    public function get_status ()
    {
        return (int) $this->get_info ('http_code');
    }

    public function get_url ()
    {
        return $this->get_info ('url');
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

    public function execute ()
    {
        if (is_resource($this->_request)) {

            $this->_body = $this->_rawbody = call_user_func ($this->_get_content_func, $this->_request);

            if ( ! empty ($this->_verbose)) {
                rewind ($this->_verbose);
                $this->_log = stream_get_contents ($this->_verbose);
            }

            $this->_info = curl_getinfo ($this->_request);
            $this->_parse_headers();
            $this->_populate_body();
        }

        return $this;
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

    private function _parse_headers ()
    {
        $headers_size = Arr::get ($this->_info, 'header_size', 0);
        $headers = [];

        if ($headers_size > 0) {

            $this->_headers = Headers::parse_headers ($this->_rawbody, $headers_size);

            if ($this->_headers->count()) {
                $this->_body = mb_substr ($this->_rawbody, $headers_size);
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
