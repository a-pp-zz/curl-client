<?php
namespace AppZz\Http\CurlClient;

class Headers extends ArrayAccess {

    public function __construct (array $headers = [])
    {
        parent::__construct ($headers);
    }

    public static function parse_headers ($content = '', $headers_size = 0)
    {
        $headers = [];

        if ($headers_size > 0) {

            $lines = array_slice(explode("\r\n", trim(substr($content, 0, $headers_size))), 1);
            $cookies = new Cookies;

            foreach ($lines as $line) {
                if (strpos(trim($line), ': ') !== FALSE ) {
                    list($key, $value) = explode(': ', $line);
                    $key = mb_strtolower ($key);

                    if ($key == 'content-disposition' AND preg_match ('#filename\="(.*)"#iu', $value, $pr ) ) {
                        $headers['content-disposition-filename'] = $pr[1];
                    }

                    if ($key == 'set-cookie') {
                        $cookies->add_cookie ($value);
                    } else {
                        $headers[$key] = $value;
                    }
                }
            }

            if ($cookies->count()) {
                $headers['cookies'] = $cookies;
            }

            return new Headers ($headers);
        }
    }
}
