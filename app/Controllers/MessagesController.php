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

class MessagesController extends BaseController
{
    public function __construct()
    {
        $this->mMessages = new MMessages();
        $this->mapper = new MessageXmlMapper();
        $this->outboundMessageService = new OutboundMessageService();

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

    public function getHealthStatus()
    {
        $return["status"] = "UP";
        echo json_encode($return);
    }

    public function saveScriptExchangeMessage()
    {
        return $this->saveMessage("ScriptExchange");
    }

    public function saveSurescriptMessage()
    {
        return $this->saveMessage("SureScript");
    }

    private function saveMessage($platform = "SureScript")
    {
        $rawXml = (string)$this->request->getBody();
        $incomingMessage = $this->mapper->fromXml($rawXml, $platform);
        $logResult = $this->mMessages->logSwitchMessage($incomingMessage);
        $data = [
            'SenderPlatform' => $platform,
            'MessageID' => uniqid('response_', true),
            'MessageToID' => $incomingMessage['messageFromId'],
            'MessageToQ' => $incomingMessage['messageFromQualifier'],
            'RelatesToMessageID' => $incomingMessage['messageId'],
            'MessageFromID' => $incomingMessage['messageToId'],
            'MessageFromQ' => $incomingMessage['messageToQualifier'],
            'SentTime' => date(DATE_ATOM),
            'MessageType' => 'Status',
            'Code' => '010',
            'dev' => 'success',
            'DatatypesVersion' => $incomingMessage['versions']['datatypes'],
            'TransportVersion' => $incomingMessage['versions']['transport'],
            'TransactionDomain' => $incomingMessage['versions']['transactionDomain'],
            'TransactionVersion' => $incomingMessage['versions']['transaction'],
            'StructuresVersion' => $incomingMessage['versions']['structures'],
            'ECLVersion' => $incomingMessage['versions']['ecl'],
            'debug' => false,
        ];
        if ($logResult == 0) {
            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/xml')
                ->setBody(view('status_message', $data, ['debug' => false]));
        }
        return $this->response
            ->setStatusCode(400)
            ->setBody('Message could not be saved.');
    }

    private function xmlResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/xml', 'UTF-8')
            ->setBody(view('status_message', $data));
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
