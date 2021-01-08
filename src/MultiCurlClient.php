<?php
/**
 * Simple MultiCurl Client
 * @package Http
 * @version	3.0.0
 */
namespace AppZz\Http;
use \AppZz\Helpers\Arr;
use \AppZz\Http\CurlClient\Exceptions\MultiCurlClientException;

class MultiCurlClient {

	private $_mc;
	private $_results;

	public function __construct ()
	{
		$this->_mc = curl_multi_init ();
	}

	public static function factory ()
	{
		return new MultiCurlClient ();
	}

	public function add ($request, $params = [])
	{
		if (is_array ($request) AND ! empty ($request)) {
			foreach ($request as $r) {
				$this->add ($r, $params);
			}
			return $this;
		} elseif ($request instanceof CurlClient) {
			$request = $request->get_request();
		} elseif (is_string ($request)) {
			$request = CurlClient::get ($request, $params)->get_request();
		} elseif ( ! is_resource ($request)) {
			throw new MultiCurlClientException ('Wrong type of request');
		}

		curl_multi_add_handle ($this->_mc, $request);
		return $this;
	}

	public function execute ()
	{
		do {
		  	curl_multi_exec ($this->_mc, $running);
		  	curl_multi_select ($this->_mc);
		} while ($running > 0);

	    while ($info = curl_multi_info_read ($this->_mc)) {
	    	$this->_add_result ($info);
	    }

	    return $this;
	}

	public function get_results ()
	{
		if ($this->_mc) {
			curl_multi_close ($this->_mc);
		}

		return $this->_results;
	}

	public function _add_result ($info)
	{
		$handle = Arr::get ($info, 'handle');

		if (is_resource($handle)) {

			$this->_results[] = CurlClient\Response::factory ($handle, TRUE)
											->verbose (FALSE)
											->execute();

			curl_multi_remove_handle ($this->_mc, $handle);
			curl_close ($handle);
		} else {
			throw new MultiCurlClientException ('Wrong curl handle');
		}

		return $this;
	}
}
