<?php
namespace AppZz\Http\CurlClient;
use \AppZz\Helpers\Arr;

class Cookies extends ArrayAccess {

    private $_default = [
        'name'    => '',
        'value'   => '',
        'expires' => 0,
        'Max-Age' => 0,
        'path'    => '/',
        'domain'  => '*',
        'secure'  => FALSE
    ];

    private $_exists = [];

    public function __construct (array $cookies = [])
    {
        if ($cookies) {

            foreach ($cookies as $key=>$value) {

                if ( ! is_numeric($key) AND ! is_array ($value)) {
                    $value = array_merge ($this->_default, ['name'=>$key, 'value'=>$value]);
                } elseif (is_array ($value)) {
                    $value = array_intersect_key ($this->_default, $value);
                }

                $this->add_cookie ($value);

            }
        }

        //parent::__construct ($cookies);
    }

    public function add_cookie ($cookie = [])
    {
        if (is_string ($cookie)) {
            $cookie = $this->_parse_cookie_string ($cookie);
        }

        if ( ! empty ($cookie)) {
            $name = Arr::get($cookie, 'name');

            if ( ! in_array ($name, $this->_exists)) {
                $this->_container[] = $cookie;
                $this->_exists[] = $name;
            }
        }

        return $this;
    }

    public function __toString()
    {
        $cookies_str = '';

        if ( ! empty ($this->_container)) {

            foreach ($this->_container as $cookie) {
                $name = Arr::get ($cookie, 'name');
                $value = Arr::get ($cookie, 'value');
                $cookies_str .= sprintf ('%s=%s; ', $name, urlencode ($value));
            }

            $cookies_str = rtrim ($cookies_str, '; ');
        }

        return $cookies_str;
    }

    public function pairs ()
    {
        $pairs = [];

        if ( ! empty ($this->_container)) {
            foreach ($this->_container as $k=>$v) {
                $pairs[Arr::get ($v, 'name')] = Arr::get ($v, 'value');
            }
        }

        return $pairs;
    }

    private function _parse_cookie_string ($string)
    {
        $keys_regex = '#(?<key>'.implode ('|', array_keys ($this->_default)).')\=(?<val>.*)#iu';
        $ret = [];

        $parts = explode ('; ', $string);

        if (empty($parts)) {
            return FALSE;
        }

        $name_val = array_shift ($parts);

        if ( ! empty ($name_val)) {
            $name_val_parts = explode ('=', $name_val);

            if ($name_val_parts) {
                $ret['name'] = Arr::get ($name_val_parts, 0);
                $ret['value'] = urldecode (Arr::get ($name_val_parts, 1));
            }
        }

        foreach ($parts as $p) {
            if (preg_match ($keys_regex, $p, $values)) {
                $key = Arr::get ($values, 'key');
                $val = Arr::get ($values, 'val');
                $ret[strtolower($key)] = $val;
            }
        }

        return $ret;
    }
}
