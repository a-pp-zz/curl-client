<?php
namespace AppZz\Http\CurlClient;

class Headers extends ArrayAccess {

    public function __construct (array $headers = [])
    {
        parent::__construct ($headers);
    }

}
