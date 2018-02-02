<?php
/**
 * Simple Curl Client
 * @package Http
 * @version	2.0.2
 */
namespace AppZz\Http;
use \AppZz\Helpers\Arr;

class CurlClient {

	/**
	 * default curl options
	 * @var array
	 */
	private $default_options = array (
		'CURLOPT_HEADER'         => TRUE,
		'CURLOPT_USERAGENT'      => 'AppZz Curl Client',
		'CURLOPT_TIMEOUT'        => 30,
		'CURLOPT_FOLLOWLOCATION' => TRUE,
		'CURLOPT_RETURNTRANSFER' => TRUE,
		'CURLOPT_MAXREDIRS'      => 3,
		'CURLOPT_ENCODING'		 => '',
	);

	/**
	 * Curl version
	 * @var string
	 */
	private $version;

	/**
	 * proxy ?
	 * @var boolean
	 */
	private $proxy = FALSE;

	/**
	 * popular clients
	 */
	const CURL_CLIENT_CHROME  = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36';
	const CURL_CLIENT_FIREFOX = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
	const CURL_CLIENT_SAFARI  = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/601.7.7 (KHTML, like Gecko) Version/9.1.2 Safari/601.7.7';
	const CURL_CLIENT_MSIE11  = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';
	const CURL_CLIENT_MSIE10  = 'Mozilla/5.0 (compatible; WOW64; MSIE 10.0; Windows NT 6.2)';
	const CURL_CLIENT_MSIE9   = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)';
	const CURL_CLIENT_MSIE8   = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)';

	/**
	 * class params
	 */
	private $url,
			$method,
			$mime,
			$mimes,
			$params,
			$file,
			$cookie_file,
			$headers,
			$options,
			$response;

	/**
	 * default charset
	 * @var string
	 */
	public static $charset = 'utf-8';

	/**
	 * __constructor
	 * @param array $options
	 */
	public function __construct (array $options = array ()) {
		$options = array_merge ($options, $this->default_options);
		$this->set_options ($options);
		$this->version = Arr::get(curl_version(), 'version');
	}

	/**
	 * factory
	 * @param array $options
	 * @return class object
	 */
	public static function factory (array $options = array ()) {
		return new CurlClient ($options);
	}

	/**
	 * Set curl options
	 * @param array $options curl options by array
	 */
	public function set_options ($options = array()) {
		foreach ( (array) $options as $key => $value) {
			$this->set_option($key, $value);
		}
		return $this;
	}

	/**
	 * Set curl option
	 * @param string $key
	 * @param string $value
	 */
	public function set_option ($key = '', $value = '') {
		if (is_string($key) AND !is_numeric($key)) {
			$const = strtoupper($key);
			if (defined($const)) {
				$key = constant(strtoupper($key));
				$this->options[$key] = $value;
			}
		}
		return $this;
	}

	/**
	 * Set url
	 * @param  string $url
	 * @return CurlClient
	 */
	public function uri ($url) {
		if ($url)
			$this->url = $url;
		return $this;
	}

	/**
	 * Set http-method
	 * @param  string $method
	 * @return CurlClient
	 */
	public function method ($method = 'get') {
		$this->method = strtoupper($method);
		return $this;
	}

	public function file ($file = NULL) {
		$this->file = $file;
		return $this;
	}

	/**
	 * Disable response headers
	 * @return CurlClient
	 */
	public function no_headers() {
		$this->set_option ('CURLOPT_HEADER', FALSE);
		return $this;
	}

	/**
	 * Add header
	 * @param string $key
	 * @param string $value
	 * @return CurlClient
	 */
	public function add_header ($key, $value) {
		if ( !empty ($key) AND !empty($value))
			$this->headers[$key] = $value;
		return $this;
	}

	/**
	 * Add headers by array
	 * @param array $headers
	 * @return CurlClient
	 */
	public function add_headers (array $headers) {
		foreach ($headers as $key => $value) {
			$this->add_header($key, $value);
		}
		return $this;
	}

	/**
	 * Set mime-type
	 * @param  string $mime mime-type
	 * @return CurlClient
	 */
	public function mime ($mime = NULL) {
		switch ($mime) {
			case 'text':
				$this->add_header('Content-Type' ,'text/plain; charset=' . CurlClient::$charset);
			break;
			case 'form':
				$this->add_header('Content-Type', 'application/x-www-form-urlencoded; charset=' . CurlClient::$charset);
			break;
			case 'upload':
				$this->_get_mime_types();
				$this->add_header('Content-Type', 'multipart/form-data; charset=' . CurlClient::$charset);
			break;
			case 'json':
				$this->add_header('Content-Type', 'application/json; charset=' . CurlClient::$charset);
			break;
			case 'xml':
				$this->add_header('Content-Type', 'text/xml; charset=' . CurlClient::$charset);
			break;
			case 'html':
				$this->add_header('Content-Type', 'text/html; charset=' . CurlClient::$charset);
			break;
		}
		$this->mime = $mime;
		return $this;
	}

	/**
	 * Set cookie-file location
	 * @param  string $file
	 * @return CurlClient
	 */
	public function cookie_file ($file = NULL) {
		if ( !empty($this->cookie_file))
			return $this;
		$this->cookie_file = $file ? $file : tempnam(sys_get_temp_dir(), 'cclient_');
		$this->set_option ('CURLOPT_COOKIEJAR', $this->cookie_file);
		$this->set_option ('CURLOPT_COOKIEFILE', $this->cookie_file);
		return $this;
	}

	/**
	 * Verify SSL-cert
	 * @param  boolean $strict
	 * @return CurlClient
	 */
	public function strict_ssl ($strict = TRUE) {
		if ($strict === FALSE) {
			$this->set_option ('CURLOPT_SSL_VERIFYPEER', FALSE);
			$this->set_option ('CURLOPT_SSL_VERIFYHOST', FALSE);
		}
		return $this;
	}

	/**
	 * Auth params
	 * @param  string $username
	 * @param  string $password
	 * @param  string $type
	 * @return CurlClient
	 */
	public function auth ($username = '', $password = '', $type = 'basic') {
		$this->set_option ('CURLOPT_HTTPAUTH', constant ('CURLAUTH_'.strtoupper($type)));
		$this->set_option ('CURLOPT_USERPWD', "{$username}:{$password}");
		return $this;
	}

	/**
	 * Proxy params
	 * @param  string  $host
	 * @param  array  $params
	 * @return CurlClient
	 */
	public function proxy ($host = '', array $params = array ()) {

		$defaults = array (
			'port'     => 8080,
			'type'     => 'http',
			'tunnel'   => FALSE,
			'auth'     => NULL,
			'username' => NULL,
			'password' => NULL,
		);

		$url_parts = parse_url ($host);
		$host = Arr::get ($url_parts, 'host');
		$port = Arr::get ($url_parts, 'port');

		if ($port) {
			$defaults['port'] = $port;
		}

		if ( ! $host) {
			return $this;
		}

		$params = array_merge ($defaults, $params);

		if ( ! empty ($params)) {
			extract ($params);
			if (isset($tunnel))
				$this->set_option ('CURLOPT_HTTPPROXYTUNNEL', TRUE);
			if (isset($port))
				$host . ':' . $port;
			if (isset($username) AND isset($password))
				$this->set_option ('CURLOPT_PROXYUSERPWD', "{$username}:{$password}");
			if (isset($auth)) {
				$auth = strtoupper ($auth);
				if ( in_array ($auth, array ('BASIC', 'NTLM')))
					$this->set_option ('CURLOPT_PROXYAUTH', constant('CURLAUTH_'.$auth));
			}
			if (isset($type)) {
				$type = strtoupper ($type);
				if ( in_array ($type, array ('HTTP', 'SOCKS4', 'SOCKS5', 'SOCKS4A', 'SOCKS5_HOSTNAME')))
					$this->set_option ('CURLOPT_PROXYTYPE', constant('CURLPROXY_'.$type));
			}
		}

		$this->set_option ('CURLOPT_PROXY', $host);
		$this->proxy = TRUE;
		return $this;
	}

	/**
	 * Set user-agent
	 * @param string $agent
	 * @return CurlClient
	 */
	public function agent ($agent = '') {
		if ($agent=='random') {
			$agents = $this->_get_user_agents();
			shuffle ($agents);
			$agent = array_shift($agents);
		}
		else {
			$rf = new \ReflectionClass("\AppZz\Http\CurlClient");
			if ($rf->hasConstant('CURL_CLIENT_'.strtoupper($agent))) {
				$agent = constant('\AppZz\Http\CurlClient::CURL_CLIENT_'.strtoupper($agent));
			}
		}
		$this->set_option ('CURLOPT_USERAGENT', $agent);
		return $this;
	}

	/**
	 * Set referer
	 * @param string $referer
	 * @return CurlClient
	 */
	public function referer ($referer = '') {
		$this->set_option ('CURLOPT_REFERER', $referer);
		return $this;
	}

	/**
	 * Set timeout
	 * @param integer $timeout
	 * @return CurlClient
	 */
	public function timeout ($timeout = 30) {
		$this->set_option ('CURLOPT_TIMEOUT', $timeout);
		return $this;
	}

	/**
	 * Set accept headers
	 * @param  string $accept
	 * @param  string $encoding
	 * @param  string $language
	 * @return CurlClient
	 */
	public function accept ($accept = '*/*', $encoding = NULL, $language = 'ru-RU') {
		switch ($accept) {
			case 'html':
				$accept = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
			break;
			case 'json':
				$accept = 'application/json, text/plain, */*';
			break;
			default:
				$accept = '*/*';
			break;
		}
		if ($accept)
			$this->add_header('Accept', $accept);
		if ($encoding) {
			$this->set_option('CURLOPT_ENCODING', $encoding);
			$this->add_header('Accept-Encoding', $encoding);
		}
		if ($language)
			$this->add_header('Accept-Language', $language);
		return $this;
	}

	/**
	 * Set ajax request headers
	 * @param  string $request json|xml
	 * @return CurlClient
	 */
	public function ajax ($request = 'json') {
		$headers = array (
			'X-Request'=>strtoupper($request),
			'X-Requested-With'=>'XMLHttpRequest'
		);
		$this->add_headers($headers);
		return $this;
	}

	/**
	 * Set payload params
	 * @param  array  $params
	 * @return CurlClient
	 */
	public function params ($params = array()) {
		$this->params = $params;
		return $this;
	}

	/**
	 * Send Curl request
	 * @return http-code response
	 */
	public function send () {
		$this->cookie_file();
		$this->_prepare_params();
		$this->_set_headers();

		switch ($this->method) {
			case 'GET' :
			break;

			case 'POST' :
				$this->set_option ('CURLOPT_POST', TRUE);
				$this->set_option ('CURLOPT_POSTFIELDS', $this->params);
			break;

			case 'PUT' :
				if ($this->file AND file_exists($this->file)) {
					$fp = fopen ($this->file, 'r');
					$filesize = filesize($this->file);
					$this->url .= DIRECTORY_SEPARATOR . basename($this->file);
					$this->set_option ('CURLOPT_PUT', TRUE);
					$this->set_option ('CURLOPT_BINARYTRANSFER', TRUE);
					$this->set_option ('CURLOPT_INFILE', $fp);
					$this->set_option ('CURLOPT_INFILESIZE', $filesize);
					$this->set_option ('CURLOPT_BINARYTRANSFER', TRUE);
				}
			break;

			case 'HEAD' :
				$this->set_option('CURLOPT_HEADER', TRUE);
				$this->set_option('CURLOPT_NOBODY', TRUE);
				$this->set_option ('CURLOPT_CUSTOMREQUEST', $this->method);
			break;

			case 'DELETE' :
				if ($this->file) {
					$this->url .= DIRECTORY_SEPARATOR . basename($this->file);
				}

			default:
				$this->set_option ('CURLOPT_CUSTOMREQUEST', $this->method);
				$this->set_option ('CURLOPT_POSTFIELDS', $this->params);
			break;
		}

		$request = curl_init ($this->url);
		curl_setopt_array($request, $this->options);

		$this->response       = new \stdClass();
		$this->response->body = $this->response->rawbody = curl_exec($request);
		$this->response->info = curl_getinfo($request);
		$this->response->info['version'] = $this->version;
		$this->response->info['proxied'] = $this->proxy;
	    $this->response->cookies = CurlClient::parse_cookies($this->cookie_file);
	    $this->_parse_headers();
	    $this->_populate_body();
		curl_close($request);
		if (isset($fp)) {
			fclose($fp);
		}
		return Arr::path($this->response, 'info.http_code');
	}

	/**
	 * Download file
	 * @param  string $path
	 * @return http-code or false on error
	 */
	public function download ($path = '/tmp') {
		if ( file_exists($path) AND is_dir($path) AND is_writeable($path))
			$path .= DIRECTORY_SEPARATOR . basename($this->url);
		elseif ( file_exists($path) AND !is_writeable($path))
			return FALSE;
		elseif ( !is_writeable(dirname($path)))
			return FALSE;
		$this->no_headers();
		$ret = $this->send();
		if ($ret === 200) {
			file_put_contents($path, $this->get_body());
			return $ret;
		}
		return FALSE;
	}

	/**
	 * Get response body text
	 * @return mixed
	 */
	public function get_body () {
		return isset ($this->response->body) ? $this->response->body : FALSE;
	}

	/**
	 * Get raw body
	 * @return mixed
	 */
	public function get_rawbody () {
		return isset ($this->response->rawbody) ? $this->response->rawbody : FALSE;
	}

	/**
	 * Get info about request
	 * @return mixed
	 */
	public function get_info () {
		return isset ($this->response->info) ? $this->response->info : FALSE;
	}

	/**
	 * Get response headers
	 * @return mixed
	 */
	public function get_headers () {
		return isset ($this->response->headers) ? $this->response->headers : FALSE;
	}

	/**
	 * Get cookies
	 * @return array
	 */
	public function get_cookies ($as_string = FALSE) {
		if ( $as_string AND !empty ($this->response->cookies)) {
			$cookies = '';
			$cnt = 0;
			foreach ($this->response->cookies as $k=>$v) {
				$cnt++;
				$cookies .= "{$k}={$v}";
				if ( sizeof($this->response->cookies) != $cnt)
					$cookies .= '; ';
			}
			return $cookies;
		}
		return isset ($this->response->cookies) ? $this->response->cookies : FALSE;
	}

	/**
	 * Get full response object
	 * @return object
	 */
	public function get_response () {
		return $this->response;
	}

	/**
	 * Post-process on body
	 * @return CurlClient
	 */
	private function _populate_body () {
		$encoding     = Arr::path ($this->response, 'headers.Content-Encoding');
		$content_type = Arr::path ($this->response, 'headers.Content-Type');

	    if (strpos($content_type, 'json'))
	    	$this->response->body = json_decode($this->response->body, TRUE);
	    elseif (strpos($content_type, 'xml')) {
			$object = simplexml_load_string ($this->response->body, "SimpleXMLElement", LIBXML_NOCDATA);
			$this->response->body = json_decode(json_encode($object), TRUE);
			unset ($object);
	    }
	    return $this;
	}

	/**
	 * Headers parser
	 * @return boolean
	 */
	private function _parse_headers () {
	    $headers_size = isset($this->response->info['header_size']) ? $this->response->info['header_size'] : 0;
	    $headers = array ();

	    if ($headers_size > 0) {

	        $lines = array_slice(explode("\r\n", trim(substr($this->response->body, 0, $headers_size))), 1);

	        foreach ( $lines as $line ) {
	            if ( strpos(trim($line), ': ') !== FALSE ) {
	                list($key, $value) = explode(': ', $line);
	                if ( $key == 'Content-Disposition' AND preg_match ('#filename\="(.*)"#iu', $value, $pr ) ) {
	                	$headers['Content-Disposition-Filename'] = $pr[1];
	                }
	                $headers[$key] = $value;
	            }
	        }

	        if ( sizeof ($headers) > 0) {
	        	$this->response->body = mb_substr ($this->response->body, $headers_size);
	        	$this->response->headers = $headers;
	        }
	    }
	    return (bool) $headers_size;
	}

	/**
	 * Setup curl option header
	 */
	private function _set_headers () {
		if ($this->headers) {
			$headers = array ();
			foreach ($this->headers as $k=>$v)
				$headers[] = "{$k}: {$v}";
			$this->set_option ('CURLOPT_HTTPHEADER', (array) $headers);
		}
		return $this;
	}

	/**
	 * Prepare url and payload params
	 * @return CurlClient
	 */
	private function _prepare_params () {
		if ( in_array($this->method, array('GET', 'HEAD'))) {
			$this->params = (array) $this->params;
			if ( !empty ($this->params))
				$this->url = $this->url . '?' . http_build_query($this->params);
		}
		elseif ($this->mime == 'form') {
			$this->params = http_build_query($this->params);
		}
		elseif ($this->mime == 'upload') {
			foreach ($this->params as $key=>&$value) {
				if ( preg_match('#\.\w{2,5}$#iu', $value)) {
					if ( class_exists('\CURLFile')) {
						$ext = pathinfo ($value, PATHINFO_EXTENSION);
						$postname = pathinfo ($value, PATHINFO_BASENAME);
						if ( isset($this->mimes[$ext])) {
							$value = new \CURLFile($value, $this->mimes[$ext], $postname);
						}
					} else {
						$value = '@' . $value;
					}
				}
			}
		}
		else {
			if ($this->mime == 'json') {
				$this->params = json_encode ((array) $this->params);
			}
			if (empty($this->file) AND !is_array($this->params))
				$this->add_header('Content-Length', mb_strlen($this->params));
		}
	}

	/**
	 * get allowed mime types
	 * @return CurlClient
	 */
	private function _get_mime_types () {
		$mimes = file (dirname(__DIR__) . '/assets/Mime.txt');
		$mimes = array_map('rtrim', $mimes);
		$this->mimes = array();
		foreach ($mimes as $m) {
			list ($ext, $type) = explode (' ', $m);
			$exts = explode ('|', $ext);
			if ($exts) {
				foreach ($exts as $ex) {
					$this->mimes[$ex] = $type;
				}
			} else {
				$this->mimes[$ext] = $type;
			}
		}
		return $this;
	}

	/**
	 * get user agents
	 * @return array
	 */
	private function _get_user_agents () {
		$agents = file (dirname(__DIR__) . '/assets/Agents.txt');
		$agents = array_map('rtrim', $agents);
		return $agents;
	}

	public static function parse_cookies ($cookie_file = '') {
		if ( !empty ($cookie_file) AND file_exists($cookie_file)) {
			$cookies_raw = file_get_contents ($cookie_file);
		    $lines = explode("\n", $cookies_raw);
		    $cookies = array();
		    foreach ((array) $lines as $line) {
		        if (isset($line[0]) && substr_count($line, "\t") == 6) {
		            $tokens = explode("\t", $line);
		            $tokens = array_map('trim', $tokens);
		            $cookies[$tokens[5]] = $tokens[6];
		        }
		    }
		    return $cookies;
		}
		return FALSE;
	}

	/**
	 * Make request
	 * @param  string $url
	 * @param  string $method  http-method
	 * @param  array  $params
	 * @param  string  $file
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function request ($url, $method = 'get', $params = array(), $file = NULL, $headers = array(), $options = array()) {
		return CurlClient::factory ($options)
				->uri($url)
				->method($method)
				->params($params)
				->file($file)
				->accept()
				->add_headers($headers)
				->set_options($options);
	}

	/**
	 * Make head request
	 * @param  string $url
	 * @param  array  $params
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function head ($url, $params = array(), $headers = array (), $options = array()) {
		return CurlClient::request($url, 'head', $params, NULL, $headers, $options);
	}

	/**
	 * Make get request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function get ($url, $params = array(), $headers = array (), $options = array ()) {
		return CurlClient::request($url, 'get', $params, NULL, $headers, $options);
	}

	/**
	 * Make post request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function post ($url, $params = array(), $headers = array (), $options = array ()) {
		return CurlClient::request($url, 'post', $params, NULL, $headers, $options);
	}

	/**
	 * Make put request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function put ($url, $params = array(), $file = NULL, $headers = array (), $options = array ()) {
		return CurlClient::request($url, 'put', $params, $file, $headers, $options);
	}

	/**
	 * Make patch request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function patch ($url, $params = array(), $headers = array (), $options = array ()) {
		return CurlClient::request($url, 'patch', $params, $headers, $options);
	}

	/**
	 * Make delete request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function delete ($url, $params = array(), $headers = array (), $options = array ()) {
		return CurlClient::request($url, 'delete', $params, $headers, $options);
	}
}
