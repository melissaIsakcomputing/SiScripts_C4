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
        $certificateDirectory = ROOTPATH ;
        $clientCertificatePath = $certificateDirectory . $options['certificate'];
        $privateKeyPath = $certificateDirectory . $options['privateKey'];
        $privateKeyPassword = $options['privateKeyPassword'];
        $apiKey = $options['apiKey'];
        $headers = ['Content-Type: application/xml'];
        if ($apiKey != "") {
            $headers[] = 'x-api-key: ' . $apiKey;
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
            CURLOPT_SSLCERT => $clientCertificatePath,
            CURLOPT_SSLCERTTYPE => 'CRT',
            CURLOPT_SSLKEY => $privateKeyPath,
            CURLOPT_SSLKEYTYPE => 'PEM',

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($privateKeyPassword != "") {
            curl_setopt($ch, CURLOPT_KEYPASSWD, $privateKeyPassword);
        }

        $responseBody = curl_exec($ch);
        $curlErrorNumber = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $responseBody;
    }
}