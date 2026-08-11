<?php

namespace App\Models;

use Exception;

class SureScriptAdapter
{

    function __construct($url = SureScriptURL)
    {
    }

    public function connectivy()
    {
        return $this->postMessage(SureScriptURL . '/' . 'Connectivy', '');
    }

    public function postMessage($url, $xml)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CAINFO, SureScriptCA);
        curl_setopt($ch, CURLOPT_SSLCERT, SureScriptPem);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
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