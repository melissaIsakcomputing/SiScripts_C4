<?php

namespace App\Controllers\V2;

/*
 * @change Isak - 2/14/2024 - Return Error Code 600 for Communication problem - try again later
 * @change Isak - 2/14/2024 - Return Status Code 000 for Not able to reach final destination (pharmacy)
 */

use App\Controllers\BaseController;
use App\Models\OutboundMessageService;

class ScriptExchangeOutController extends BaseController
{
    public function __construct()
    {
        $this->outboundMessageService = new OutboundMessageService();
    }

    private function sendMessage()
    {
        $xml = $this->request->getBody();
        if (trim($xml) === '') {
            return $this->response->setStatusCode(400)->setBody('XML body is required.');
        }
        $options = [
            'certificate' => env('SCRIPT_EXCHANGE_CERT_PATH'),
            'certificate_type' => "CRT",
            'privateKey' => env('SCRIPT_EXCHANGE_KEY_PATH'),
            'privateKeyPassword' => env('SCRIPT_EXCHANGE_API_KEY'),
            'apiKey' => env('SCRIPT_EXCHANGE_API_KEY'),
        ];

        $url = env('SCRIPT_EXCHANGE_URL');

        return $this->outboundMessageService->send($url, $xml, $options);
    }

}
