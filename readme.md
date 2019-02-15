# CurlClient v3.0
Liteweight full featured curlclient

## Features
* Support all popular http-methods
* Proxy support
* Logging
* http-version, ipv4, ipv6 triggers
* Cookie parser
* JSON support

```
<?php

$request = CurlClient::get ('https://github.com')
				->http2()
				->ipv6()
				->browser('chrome', 'mac');
				
$response = $request->send();

//Get response status
$status = $response->get_status();

//Get body
$body = $response->get_body();

//Get request headers
$request->get_headers()->asArray();

//Get response headers
$headers = $response->get_headers()->asArray();

//Get cookies
$cookies = $headers->offsetGet ('cookies');
var_dump ($cookies->asArray()); //as array
echo $cookies; // as url-encoded string
?>

```
