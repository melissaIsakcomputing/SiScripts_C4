<?php

namespace App\Models;

use Exception;

class ScriptAdapter
{

    public $url = "";
    public $certificatePath = "";
    public $privateKeyPath = "";
    public $headers = "";

    function __construct($url, $certificatePath, $privateKeyPath, $headers = [])
    {
        $this->url = $url;
        $this->certificatePath = $certificatePath;
        $this->privateKeyPath = $privateKeyPath;
        $this->headers = $headers;
    }

    public function postMessage($url, $xml)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 100);
        curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CAINFO, $this->certificatePath);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->privateKeyPath);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        //Do some basic error checking.
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        //Close the cURL handle.
        curl_close($ch);
        //Print out the response output.
        return $result;
    }

}