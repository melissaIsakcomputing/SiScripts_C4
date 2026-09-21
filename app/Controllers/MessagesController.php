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
use RuntimeException;
use SimpleXMLElement;

class MessagesController extends BaseController
{
    public function __construct()
    {
        $this->mMessages = new MMessages();
        $this->mapper = new MessageXmlMapper();
        $this->scriptAdapter = new ScriptAdapter(ScriptExchange, SureScriptPem, SureScriptCA);

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

    private function saveMessage($platform)
    {
        $rawXml = (string)$this->request->getBody();
        $incomingMessage = $this->mapper->fromXml($rawXml, $platform);

        $logResult = $this->mMessages->logMessage($incomingMessage);
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

        return view('status_message', $data, ['debug' => false]);

        $statusXml = view('status_message', $data, ['debug' => false]);
        $statusXml = preg_replace('/^\xEF\xBB\xBF/', '', $statusXml);
        $statusXml = ltrim($statusXml);
        $channelId = "VC96HWsEdX";

        $response = $this->sendStatusMessage(
            $channelId,
            $statusXml
        );

        $responseXml = $this->xmlResponse($data);

        return $responseXml;
    }

    private function xmlResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/xml', 'UTF-8')
            ->setBody(view('status_message', $data));
    }

    private function sendStatusMessage(
        string $channelId,
        string $xml
    ): string
    {
        $certificateDirectory = ROOTPATH . 'certificates/exchange/';

        $clientCertificatePath = $certificateDirectory . 'certificate.crt';
        $privateKeyPath = $certificateDirectory . 'private_key.pem';

        $apiKey = "KN9i6CPLIv8AuQPphMbljnC7I6RmkpHE52HHqyH1";

        $privateKeyPassword = "KN9i6CPLIv8AuQPphMbljnC7I6RmkpHE52HHqyH1";

        if ($channelId === '') {
            throw new RuntimeException(
                'The Script Exchange channel ID is missing.'
            );
        }

        if ($apiKey === '') {
            throw new RuntimeException(
                'The Script Exchange channel API key is missing.'
            );
        }

        if (!is_readable($clientCertificatePath)) {
            throw new RuntimeException(
                "Client certificate is not readable: {$clientCertificatePath}"
            );
        }

        if (!is_readable($privateKeyPath)) {
            throw new RuntimeException(
                "Private key is not readable: {$privateKeyPath}"
            );
        }

        $url = 'https://smx.script.exchange/message/'
            . rawurlencode($channelId);

        $headers = [
            'Content-Type: application/xml',
            'x-api-key: ' . $apiKey,
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_PORT => 443,
            CURLOPT_POST => true,

            // Sends the raw XML, equivalent to req.write(BODY).
            CURLOPT_POSTFIELDS => $xml,

            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,

            // Equivalent to cert in the JavaScript code.
            CURLOPT_SSLCERT => $clientCertificatePath,
            CURLOPT_SSLCERTTYPE => 'PEM',

            // Equivalent to key in the JavaScript code.
            CURLOPT_SSLKEY => $privateKeyPath,
            CURLOPT_SSLKEYTYPE => 'PEM',

            // Needed because your private key is encrypted.
            CURLOPT_KEYPASSWD => $privateKeyPassword,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $responseBody = curl_exec($ch);

        $curlErrorNumber = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($responseBody === false) {
            throw new RuntimeException(
                "Script Exchange connection failed "
                . "({$curlErrorNumber}): {$curlErrorMessage}"
            );
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException(
                "Script Exchange returned HTTP {$httpCode}. "
                . "Response: {$responseBody}"
            );
        }

        return $responseBody;
    }

    public function sendScriptExchangeStatus()
    {
        $xml = "";
        $this->scriptAdapter->postMessage(ScriptExchange, $xml);
    }
}
