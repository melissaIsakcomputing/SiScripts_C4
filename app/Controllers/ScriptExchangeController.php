<?php

namespace App\Controllers;

/*
 * @change Isak - 2/14/2024 - Return Error Code 600 for Communication problem - try again later
 * @change Isak - 2/14/2024 - Return Status Code 000 for Not able to reach final destination (pharmacy)
 */

use App\Models\MessageXmlMapper;
use App\Models\MMessages;
use App\Models\OutboundMessageService;
use CodeIgniter\HTTP\ResponseInterface;
use http\Env;
use RuntimeException;

class ScriptExchangeController extends BaseController
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
            'privateKey' => env('SCRIPT_EXCHANGE_KEY_PATH'),
            'privateKeyPassword' => env('SCRIPT_EXCHANGE_API_KEY'),
            'apiKey' => env('SCRIPT_EXCHANGE_API_KEY'),
        ];

        $url = env('SCRIPT_EXCHANGE_API_KEY');

        return $this->outboundMessageService->send($url, $xml, $options);
    }

}
