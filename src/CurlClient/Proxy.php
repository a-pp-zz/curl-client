<?php
namespace AppZz\Http\CurlClient;
use AppZz\Helpers\Arr;

/**
 * Proxy gen
 */
class Proxy {

    public $hostname = NULL;
    public $port     = NULL;
    public $authtype = NULL;
    public $userpwd  = NULL;
    public $tunnel   = FALSE;
    public $type     = 'HTTP';

    function __construct ($hostname = '', array $params = array ())
    {
        $this->hostname = $hostname;

        if ( ! empty ($params) AND is_array ($params)) {

            $this->port   = Arr::get ($params, 'port', '8080');
            $this->type   = Arr::get ($params, 'type', 'http');
            $this->tunnel = Arr::get ($params, 'tunnel');
            $authtype     = Arr::get ($params, 'authtype', 'basic');
            $username     = Arr::get ($params, 'username', '');
            $password     = Arr::get ($params, 'password', '');

            if ($authtype AND $username AND $password) {
                $this->login ($username, $password, $authtype);
            }

            unset ($params);
        }

        $this->_populate ();
    }

    public static function factory ($hostname, $params)
    {
        return new Proxy ($hostname, $params);
    }

    public function port ($port = 8080)
    {
        if (empty ($this->port)) {
            $this->port = intval ($port);
        }

        return $this;
    }

    public function tunnel ($tunnel = FALSE)
    {
        $this->tunnel = (bool) $tunnel;
        return $this;
    }

    public function login ($username = '', $password = '', $authtype = 'basic')
    {
        $authtype = strtoupper ($authtype);

        if (in_array ($authtype, array ('BASIC', 'NTLM'))) {
            $this->authtype = $authtype;
            $this->authtype = constant('CURLAUTH_'.$authtype);
        }

        if ( ! empty ($username) AND ! empty ($password)) {
            $this->userpwd = sprintf ('%s:%s', $username, $password);
        }

        return $this;
    }

    public function type ($type = 'http')
    {
        if ( ! empty ($type)) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * Check for minimal config
     * @return boolean
     */
    public function is_valid ()
    {
        return ( ! empty ($this->hostname) AND ! empty ($this->port));
    }

    private function _populate ()
    {
        if (strpos($this->hostname, ':') !== FALSE) {
            $url_parts = explode (':', $this->hostname);
            $this->hostname = Arr::get($url_parts, 0);
            $this->port = Arr::get($url_parts, 1);
        }

        $this->type = strtoupper ($this->type);

        if (in_array ($this->type, array ('HTTP', 'SOCKS4', 'SOCKS5', 'SOCKS4A', 'SOCKS5_HOSTNAME'))) {
            $this->type = constant('CURLPROXY_'.$this->type);
        } else {
            $this->type = constant('CURLPROXY_HTTP');
        }

        return $this;
    }
}
