<?php
/**
 * Simple Curl Client
 * @package Http
 * @version	3.0.0
 */
namespace AppZz\Http;
use \AppZz\Helpers\Arr;

class CurlClient {

	/**
	 * default curl options
	 * @var array
	 */
	private $_default_options = [
		'CURLOPT_HEADER'         => TRUE,
		'CURLOPT_USERAGENT'      => CurlClient\Agent::UA_DEFAULT,
		'CURLOPT_TIMEOUT'        => 30,
		'CURLOPT_FOLLOWLOCATION' => TRUE,
		'CURLOPT_RETURNTRANSFER' => TRUE,
		'CURLOPT_MAXREDIRS'      => 3,
		'CURLOPT_ENCODING'		 => '',
		'CURLOPT_AUTOREFERER'    => TRUE,
		'CURLOPT_COOKIEFILE'     => '',
		'CURLOPT_HTTP_VERSION'   => CURL_HTTP_VERSION_1_1,
		'CURLOPT_IPRESOLVE'      => CURL_IPRESOLVE_V4
	];

	/**
	 * @var string
	 */
	public static $charset = 'utf-8';

	/**
	 * class params
	 */
	private $_url;
	private $_method;
	private $_content_type;
	private $_payload;
	private $_query;
	private $_headers;
	private $_request;
	private $_response;
	private $_http_version = 2;
	private $_verbose;
	private $_file;
	private $_file_handle;

	const GET    = 'GET';
	const HEAD   = 'HEAD';
	const POST   = 'POST';
	const PUT    = 'PUT';
	const PATCH  = 'PATCH';
	const DELETE = 'DELETE';

	public function __construct (array $options = [])
	{
		$options = array_merge ($options, $this->_default_options);
		$this->set_options ($options);
		$this->_headers = new CurlClient\Headers;
	}

	public static function factory (array $options = [])
	{
		return new CurlClient ($options);
	}

	public static function curl_version ()
	{
		return Arr::get(curl_version(), 'version');
	}

	/**
	 * Enable http2 requests
	 * @return CurlClient
	 */
	public function http2 ()
	{
		$this->set_option ('CURLOPT_HTTP_VERSION', CURL_HTTP_VERSION_2_0);
		$this->_http_version = 2;
		return $this;
	}

	/**
	 * Enable http 1.1 requests
	 * @return CurlClient
	 */
	public function http11 ()
	{
		$this->set_option ('CURLOPT_HTTP_VERSION', CURL_HTTP_VERSION_1_1);
		$this->_http_version = 1.1;
		return $this;
	}

	/**
	 * Force ipv4
	 * @return CurlClient
	 */
	public function ipv4 ()
	{
		$this->set_option ('CURLOPT_IPRESOLVE', 'CURL_IPRESOLVE_V4');
		return $this;
	}

	/**
	 * Force ipv6
	 * @return CurlClient
	 */
	public function ipv6 ()
	{
		$this->set_option ('CURLOPT_IPRESOLVE', 'CURL_IPRESOLVE_V6');
		return $this;
	}

	/**
	 * Set curl options
	 * @param array $options curl options by array
	 */
	public function set_options (array $options = [])
	{
		foreach ($options as $key => $value) {
			$this->set_option($key, $value);
		}

		return $this;
	}

	/**
	 * Set curl option
	 * @param string $key
	 * @param string $value
	 */
	public function set_option ($key = '', $value = '')
	{
		if (is_string($key) AND !is_numeric($key)) {
			$const = strtoupper($key);

			if (defined($const)) {
				$key = constant(strtoupper($key));
				$this->_options[$key] = $value;
			}
		}

		return $this;
	}

	/**
	 * Set url
	 * @param  string $url
	 * @return CurlClient
	 */
	public function url ($url)
	{
		$this->_url = $url;
		return $this;
	}

	/**
	 * Set http-method
	 * @param  string $method
	 * @return CurlClient
	 */
	public function method ($method = 'get')
	{
		$this->_method = $method;
		return $this;
	}

	public function file ($file = NULL)
	{
		$this->_file = $file;
		return $this;
	}

	public function verbose ($verbose = TRUE)
	{
		if ($verbose === TRUE) {
        	$this->set_option('CURLOPT_VERBOSE', true);
			$handle = fopen('php://temp', 'w+');
			$this->set_option('CURLOPT_STDERR', $handle);
		} else {
			$this->_verbose = FALSE;
		}

		return $this;
	}

	/**
	 * Disable response headers
	 * @return CurlClient
	 */
	public function no_headers ()
	{
		$this->set_option ('CURLOPT_HEADER', FALSE);
		return $this;
	}

	/**
	 * Add header
	 * @param string $key
	 * @param string $value
	 * @return CurlClient
	 */
	public function header ($key, $value)
	{
		if ( ! empty ($key) AND ! empty ($value)) {

			if ($this->_http_version === 2) {
				$key = mb_strtolower ($key);
			}

			$this->_headers->offsetSet ($key, $value);
		}

		return $this;
	}

	/**
	 * Add headers by array
	 * @param array $headers
	 * @return CurlClient
	 */
	public function headers (array $headers = [])
	{
		foreach ($headers as $key => $value) {
			$this->header ($key, $value);
		}

		return $this;
	}

	/**
	 * Add cookies
	 * @param  mixed $cookies
	 * @return CurlClient
	 */
	public function cookies ($cookies = '')
	{
		if (is_array ($cookies)) {
			$cookies = new CurlClient\Cookies ($cookies);
		}

		$this->set_option('CURLOPT_COOKIE', $cookies);

		return $this;
	}

	public function content_type ($type = 'text')
	{
		switch ($type) {
			case 'text':
				$this->header('Content-Type', 'text/plain; charset=' . CurlClient::$charset);
			break;

			case 'form':
				$this->header('Content-Type', 'application/x-www-form-urlencoded; charset=' . CurlClient::$charset);
			break;

			case 'upload':
			case 'multipart':
				$type = 'multipart';
				$this->header('Content-Type', 'multipart/form-data; charset=' . CurlClient::$charset);
			break;

			case 'json':
				$this->header('Content-Type', 'application/json; charset=' . CurlClient::$charset);
			break;

			case 'xml':
				$this->header('Content-Type', 'text/xml; charset=' . CurlClient::$charset);
			break;

			case 'html':
				$this->header('Content-Type', 'text/html; charset=' . CurlClient::$charset);
			break;
		}

		$this->_content_type = $type;
		return $this;
	}

	/**
	 * Verify SSL-cert
	 * @param  boolean $strict
	 * @return CurlClient
	 */
	public function strict_ssl ($strict = TRUE)
	{
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
	public function auth ($username = '', $password = '', $type = 'basic')
	{
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
	public function proxy ($host = '', array $params = [])
	{
		$proxy = new CurlClient\Proxy ($host, $params);

		if ($proxy->is_valid()) {

			if ( ! empty ($proxy->tunnel)) {
				$this->set_option ('CURLOPT_HTTPPROXYTUNNEL', TRUE);
			}

			if ( ! empty ($proxy->userpwd)) {
				$this->set_option ('CURLOPT_PROXYUSERPWD', $proxy->userpwd);
			}

			if ( ! empty ($proxy->authtype)) {
				$this->set_option ('CURLOPT_PROXYAUTH', $proxy->authtype);
			}

			if ( ! empty ($proxy->type)) {
				$this->set_option ('CURLOPT_PROXYTYPE', $proxy->type);
			}

			$this->set_option ('CURLOPT_PROXY', $proxy->hostname . ':' . $proxy->port);
			//$this->set_option ('CURLOPT_PROXYPORT', $proxy->port);
		}

		return $this;
	}

	/**
	 * Set user-agent by shortname eg chrome_win, chrome_mac, msie11 etc
	 * @param string $agent
	 * @param string $platform
	 * @return CurlClient
	 */
	public function browser ($agent = '', $platform = '')
	{
		$agents = new CurlClient\Agent;
		return $this->user_agent ($agents->get ($agent, $platform));
	}

	/**
	 * Set raw user agent
	 * @param  string $user_agent
	 * @return CurlClient
	 */
	public function user_agent ($user_agent = '')
	{
		$this->set_option ('CURLOPT_USERAGENT', $user_agent);
		return $this;
	}

	/**
	 * Set referer
	 * @param string $referer
	 * @return CurlClient
	 */
	public function referer ($referer = '')
	{
		$this->set_option ('CURLOPT_REFERER', $referer);
		return $this;
	}

	/**
	 * Set timeout
	 * @param integer $timeout
	 * @return CurlClient
	 */
	public function timeout ($timeout = 30)
	{
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
	public function accept ($accept = '*/*', $encoding = NULL, $language = 'ru-RU')
	{
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

		if ($accept) {
			$this->header('Accept', $accept);
		}

		if ($encoding) {
			$this->set_option('CURLOPT_ENCODING', $encoding);
			$this->header('Accept-Encoding', $encoding);
		}

		if ($language) {
			$this->header('Accept-Language', $language);
		}

		return $this;
	}

	/**
	 * Set ajax request headers
	 * @param  string $request json|xml
	 * @return CurlClient
	 */
	public function ajax ($request = 'json')
	{
		$headers = [
			'X-Request'        => strtoupper($request),
			'X-Requested-With' => 'XMLHttpRequest'
		];

		$this->headers ($headers);
		return $this;
	}

	public function json ()
	{
		$this->content_type ('json');
		return $this;
	}

	public function form ()
	{
		$this->content_type ('form');
		return $this;
	}

	/**
	 * Set payload params
	 * @param  array  $params
	 * @return CurlClient
	 */
	public function payload ($params = [])
	{
		$this->_payload = $params;
		return $this;
	}

	public function query (array $params = [])
	{
		$this->_query = $params;
		return $this;
	}

	/**
	 * Compile curl request
	 * @return CURL resource
	 */
	public function get_request ()
	{
		$this->_prepare ();
		$this->_set_headers ();

		switch ($this->_method) {
			case CurlClient::GET :
			case CurlClient::HEAD :
				if ( ! empty ($this->_query)) :
					$this->_url .= '?' . http_build_query($this->_query);
				endif;

				if ($this->_method === CurlClient::HEAD) :
					$this->set_option('CURLOPT_HEADER', TRUE);
					$this->set_option('CURLOPT_NOBODY', TRUE);
					$this->set_option ('CURLOPT_CUSTOMREQUEST', $this->_method);
				endif;
			break;

			case CurlClient::PUT :
				if ($this->_file AND file_exists($this->_file)) {
					$this->_file_handle = fopen ($this->_file, 'rb');
					$filesize = filesize ($this->_file);
					$this->_url .= DIRECTORY_SEPARATOR . basename ($this->_file);
					$this->set_option ('CURLOPT_PUT', TRUE);
					$this->set_option ('CURLOPT_BINARYTRANSFER', TRUE);
					$this->set_option ('CURLOPT_INFILE', $this->_file_handle);
					$this->set_option ('CURLOPT_INFILESIZE', $filesize);
					$this->set_option ('CURLOPT_BINARYTRANSFER', TRUE);
				}
			break;

			default:
				if ( ! empty ($this->_file)) :
					$this->_url .= DIRECTORY_SEPARATOR . basename($this->_file);
				endif;

				if ($this->_method === CurlClient::POST):
					$this->set_option ('CURLOPT_POST', TRUE);
				else :
					$this->set_option ('CURLOPT_CUSTOMREQUEST', $this->_method);
				endif;

				if ( ! empty ($this->_payload)) :
					$this->set_option ('CURLOPT_POSTFIELDS', $this->_payload);
				endif;
			break;
		}

        $request = curl_init ($this->_url);
        curl_setopt_array($request, $this->_options);
		return $request;
	}

	/**
	 * Send Curl request
	 * @return Response object
	 */
	public function send ()
	{
		$this->_request = $this->get_request();
		$this->_response = CurlClient\Response::factory ($this->_request, FALSE)
											->verbose ($this->_verbose)
											->execute();
		$this->_close_handlers();
		return $this->_response;
	}

	/**
	 * Get request headers
	 * @return mixed
	 */
	public function get_headers ()
	{
		return $this->_headers;
	}

	/**
	 * Setup curl option header
	 */
	private function _set_headers ()
	{
		if ($this->_headers) {
			$headers = [];
			$this->_headers = $this->_headers->asArray();

			foreach ($this->_headers as $k=>$v) {
				$headers[] = "{$k}: {$v}";
			}

			$this->set_option ('CURLOPT_HTTPHEADER', (array) $headers);
		}

		return $this;
	}

	private function _close_handlers ()
	{
		if ($this->_verbose AND is_resource($this->_verbose)) {
			fclose ($this->_verbose);
		}

		if ($this->_file_handle AND is_resource($this->_file_handle)) {
			fclose ($this->_file_handle);
		}

		curl_close ($this->_request);

		return $this;
	}

	/**
	 * Prepare payload params
	 * @return CurlClient
	 */
	private function _prepare ()
	{
		if ( ! empty ($this->_payload) AND $this->_content_type == 'form') {
			$this->_payload = http_build_query ($this->_payload);
		}
		elseif ($this->_content_type == 'multipart') {
			$mimes = new CurlClient\Mimes;

			foreach ($this->params as $key=>&$value) {
				if (preg_match('#\.\w{2,5}$#iu', $value)) {
					if (class_exists('\CURLFile')) {
						$ext = pathinfo ($value, PATHINFO_EXTENSION);
						$name = pathinfo ($value, PATHINFO_BASENAME);
						$mime = $mimes->get ($ext);

						if ( ! empty ($mime)) {
							$value = new \CURLFile($value, $mime, $name);
						}
					} else {
						$value = '@' . $value;
					}
				}
			}
		}
		else {
			if ($this->_content_type == 'json') {
				if (is_array($this->_payload) OR is_object($this->_payload)) {
					$this->_payload = json_encode ($this->_payload);
				}
			}
			if (empty($this->_file) AND is_scalar($this->_payload)) {
				$this->header ('Content-Length', mb_strlen ($this->_payload));
			}
		}

		return $this;
	}

	/**
	 * Make request
	 * @param  string $url
	 * @param  string $method  http-method
	 * @param  array  $params
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function request ($url, $method = CurlClient::GET, $params = [], array $headers = [], array $options = [])
	{
		$curl = CurlClient::factory ($options)
				->url($url)
				->method($method)
				->accept()
				->headers($headers)
				->set_options($options);

		if ($method === CurlClient::GET OR $method === CurlClient::HEAD) {
			$curl->query ($params);
		} else {
			$curl->payload ($params);
		}

		return $curl;
	}

	/**
	 * Make head request
	 * @param  string $url
	 * @param  array  $params
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function head ($url, $params = [], $headers = [], $options = [])
	{
		return CurlClient::request($url, CurlClient::HEAD, $params, $headers, $options);
	}

	/**
	 * Make get request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function get ($url, $params = [], $headers = [], $options = [])
	{
		return CurlClient::request($url, CurlClient::GET, $params, $headers, $options);
	}

	/**
	 * Make post request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function post ($url, $params = [], $headers = [], $options = [])
	{
		return CurlClient::request($url, CurlClient::POST, $params, $headers, $options);
	}

	/**
	 * Make put request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function put ($url, $params = [], $headers = [], $options = [])
	{
		return CurlClient::request ($url, CurlClient::PUT, $params, $headers, $options);
	}

	/**
	 * Make patch request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function patch ($url, $params = [], $headers = [], $options = [])
	{
		return CurlClient::request ($url, CurlClient::PATCH, $params, $headers, $options);
	}

	/**
	 * Make delete request
	 * @param  string $url
	 * @param  array  $headers
	 * @param  array  $options curl options
	 * @return CurlClient
	 */
	public static function delete ($url, $params = [], $headers = [], $options = [])
	{
		return CurlClient::request ($url, CurlClient::DELETE, $params, $headers, $options);
	}
}
