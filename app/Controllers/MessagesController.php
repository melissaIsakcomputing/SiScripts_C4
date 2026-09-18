<?php

namespace App\Controllers;

/*
 * @change Isak - 2/14/2024 - Return Error Code 600 for Communication problem - try again later
 * @change Isak - 2/14/2024 - Return Status Code 000 for Not able to reach final destination (pharmacy)
 */


use App\Models\MessageXmlMapper;
use App\Models\MMessages;
use App\Models\RestFM;
use App\Models\ScriptAdapter;
use App\Models\SureScriptAdapter;
use CodeIgniter\HTTP\ResponseInterface;
use DOMDocument;
use Exception;
use SimpleXMLElement;

class MessagesController extends BaseController
{
    public function __construct()
    {
        $this->mMessages = new MMessages();
        $this->mapper = new MessageXmlMapper();
        $this->scriptAdapter = new ScriptAdapter(ScriptExchange, SureScriptPem, SureScriptCA);
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

    private function saveMessage($platform)
    {
        $rawXml = (string)$this->request->getBody();
        $message = $this->mapper->fromXml($rawXml, $platform);

        $logResult = $this->mMessages->logMessage($message);

        return $this->xmlResponse([
            'SenderPlatform' => $platform,
            'MessageID' => uniqid('response_', true),
            'MessageToID' => $message['messageFromId'],
            'MessageToQ' => $message['messageFromQualifier'],
            'RelatesToMessageID' => $message['messageId'],
            'MessageFromID' => $message['messageToId'],
            'MessageFromQ' => $message['messageToQualifier'],
            'SentTime' => date(DATE_ATOM),
            'MessageType' => 'Status',
            'Code' => '010',
            'dev' => 'success',

            'DatatypesVersion' => $message['versions']['datatypes'],
            'TransportVersion' => $message['versions']['transport'],
            'TransactionDomain' => $message['versions']['transactionDomain'],
            'TransactionVersion' => $message['versions']['transaction'],
            'StructuresVersion' => $message['versions']['structures'],
            'ECLVersion' => $message['versions']['ecl'],
        ]);
    }

    private function xmlResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/xml', 'UTF-8')
            ->setBody(view('status_message', $data));
    }

    public function sendScriptExchangeStatus()
    {
        $xml = "";
        $this->scriptAdapter->postMessage(ScriptExchange, $xml);
    }
}
