<?php

namespace App\Controllers\V2;

/*
 * @change Isak - 2/14/2024 - Return Error Code 600 for Communication problem - try again later
 * @change Isak - 2/14/2024 - Return Status Code 000 for Not able to reach final destination (pharmacy)
 */

use App\Controllers\BaseController;
use App\Models\OutboundMessageService;

class SureScriptsOutController extends BaseController
{
    public function __construct()
    {
        $this->outboundMessageService = new OutboundMessageService();
    }

    public function sendMessage($messageId)
    {
        $xml = $this->request->getBody();
        if (trim($xml) === '') {
            return $this->response->setStatusCode(400)->setBody('XML body is required.');
        }
        $options = [
            'certificate' => env('SURESCRIPT_CERT_PATH'),
            'certificate_type' => "PEM",
        ];

        $url = env('SURESCRIPT_URL') . "?id=" . $messageId;

        $result =  $this->outboundMessageService->send($xml, $url, $options);
        return $result;
    }

}
