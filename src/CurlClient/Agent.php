<?php
namespace AppZz\Http\CurlClient;
use AppZz\Helpers\Arr;
use \ReflectionClass;

class Agent {

    const UA_DEFAULT= 'Mozilla/5.0 (compatible; AppZz-Curl-Client/3.0)';
    private $_agents = [];

    public function __construct ()
    {
        $this->_agents['chrome_mac']      = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36';
        $this->_agents['chrome_win']      = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36';
        $this->_agents['chrome_iphone']   = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/71.0.3578.89 Mobile/15E148 Safari/605.1';
        $this->_agents['chrome_ipad']     = 'Mozilla/5.0 (iPad; CPU OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/71.0.3578.89 Mobile/15E148 Safari/605.1';
        $this->_agents['chrome_linux']    = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu/18.10 Chromium/71.0.3578.98 Chrome/71.0.3578.98s Safari/537.36';
        $this->_agents['chrome_android']  = 'Mozilla/5.0 (Linux; Android 9; Pixel Build/PPR2.180905.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.91 Mobile Safari/537.36';

        $this->_agents['firefox_mac']     = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:64.0) Gecko/20100101 Firefox/64.0';
        $this->_agents['firefox_win']     = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:64.0) Gecko/20100101 Firefox/64.0';
        $this->_agents['firefox_iphone']  = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/7.0.4 Mobile/16B91 Safari/605.1.15';
        $this->_agents['firefox_ipad']    = 'Mozilla/5.0 (iPad; CPU OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/7.0.4 Mobile/16B91 Safari/605.1.15';
        $this->_agents['firefox_linux']   = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20130331 Firefox/64.0';
        $this->_agents['firefox_android'] = 'Mozilla/5.0 (Android 8.1; Mobile; rv:64.0) Gecko/64.0 Firefox/64.0';

        $this->_agents['safari_mac']      = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.2 Safari/605.1.15';
        $this->_agents['safari_iphone']   = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1';
        $this->_agents['safari_ipad']     = 'Mozilla/5.0 (iPad; CPU OS 12_1_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1';

        $this->_agents['msie11']          = 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko';
        $this->_agents['msie10']          = 'Mozilla/5.0 (compatible; WOW64; MSIE 10.0; Windows NT 6.3; Trident/6.0) like Gecko';
        $this->_agents['msie9']           = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0) like Gecko';
        $this->_agents['msie8']           = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0) like Gecko';

        $this->_agents['edge13']          = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586';
        $this->_agents['edge12']          = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246';

        $this->_agents['googlebot']       = 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)';
        $this->_agents['yandexbot']       = 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)';
    }

    public function get ($vendor = 'safari', $platform = NULL)
    {
        if ($vendor) {

            if (empty($platform) AND in_array ($vendor, ['safari', 'chrome', 'firefox'])) {
                $platform = 'mac';
            }

            if ($vendor == 'random') {
                shuffle ($this->_agents);
                return reset ($this->_agents);
            }

            if ( ! empty ($platform)) {
                $vendor .= '_' . $platform;
            }

            return Arr::get ($this->_agents, $vendor, Agent::UA_DEFAULT);
        }

        return Agent::UA_DEFAULT;
    }
}
