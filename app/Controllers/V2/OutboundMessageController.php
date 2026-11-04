<?php

namespace App\Controllers\V2;

/*
 * @change Isak - 2/14/2024 - Return Error Code 600 for Communication problem - try again later
 * @change Isak - 2/14/2024 - Return Status Code 000 for Not able to reach final destination (pharmacy)
 */

use App\Controllers\BaseController;

class OutboundMessageController extends BaseController
{
    public function __construct()
    {
        $this->platforms = [
            'scriptExchange' => [
                'ca' => ROOTPATH . 'certificates/exchange/certificate.crt',
                'client' => ROOTPATH . 'certificates/exchange/certificate.crt',
                'key' => ROOTPATH . 'certificates/exchange/private_key.pem',
            ],

            'otherPlatform' => [
                'ca' => ROOTPATH . 'certificates/exchange/other/ca.pem',
                'client' => ROOTPATH . 'certificates/exchange/other/client.pem',
                'key' => ROOTPATH . 'certificates/exchange/other/private-key.pem',
            ],
        ];


    }

    private function sendStatusMessage(string $url, string $xml): string
    {
        $options = [
            'certificate' => env('SCRIPT_EXCHANGE_CERT_PATH'),
            'privateKey' => env('SCRIPT_EXCHANGE_KEY_PATH'),
            'privateKeyPassword' => env('SCRIPT_EXCHANGE_API_KEY'),
            'apiKey' => env('SCRIPT_EXCHANGE_API_KEY'),
        ];

        return $this->outboundMessageService->send($url, $xml, $options);
    }

}
