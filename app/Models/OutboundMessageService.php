<?php

namespace App\Models;

use Exception;

class OutboundMessageService
{
    function __construct()
    {

    }

    public function send($xml, $url, $options)
    {
        $certificateDirectory = ROOTPATH;
        $headers = ['Content-Type: application/xml'];
        if (array_key_exists("apiKey", $options) && $options['apiKey'] != "") {
            $headers[] = 'x-api-key: ' . $options['apiKey'];
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_PORT => 443,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSLCERT => $certificateDirectory . $options['certificate'],
            CURLOPT_SSLCERTTYPE => $options['certificate_type'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if (array_key_exists('privateKey', $options)) {
            curl_setopt($ch, CURLOPT_SSLKEY, $certificateDirectory . $options['privateKey']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, "PEM");
        }
        if (array_key_exists('privateKeyPassword', $options)) {
            curl_setopt($ch, CURLOPT_KEYPASSWD, $certificateDirectory . $options['privateKeyPassword']);
        }

        $responseBody = curl_exec($ch);
        $curlErrorNumber = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $responseBody;
    }
}