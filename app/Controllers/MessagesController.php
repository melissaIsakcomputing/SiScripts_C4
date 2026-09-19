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

        $statusXml = view('status_message', $data);
        $this->sendStatusMessage(ScriptExchange, $statusXml);

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

    private function sendStatusMessage(string $url, string $xml): string
    {
        $certificateDirectory = ROOTPATH . 'certificates/exchange/';
        $clientCertificatePath = $certificateDirectory . 'certificate.crt';
        $privateKeyPath        = $certificateDirectory . 'private_key.pem';
        $apiKey            = "KN9i6CPLIv8AuQPphMbljnC7I6RmkpHE52HHqyH1";
        $privateKeyPassword = "KN9i6CPLIv8AuQPphMbljnC7I6RmkpHE52HHqyH1";
        $headers = [
            'content-type: application/xml',
            'x-api-key: ' . $apiKey,
        ];

        $verboseStream = fopen('php://temp', 'w+');
        $responseHeaders = [];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $xml,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_LOW_SPEED_LIMIT => 100,
            CURLOPT_LOW_SPEED_TIME  => 30,

            /*
             * Client certificate and encrypted private key.
             */
            CURLOPT_SSLCERT        => $clientCertificatePath,
            CURLOPT_SSLCERTTYPE    => 'PEM',
            CURLOPT_SSLKEY         => $privateKeyPath,
            CURLOPT_SSLKEYTYPE     => 'PEM',
            CURLOPT_KEYPASSWD      => $privateKeyPassword,

            /*
             * Always verify the remote server.
             */
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,

            /*
             * Capture diagnostics.
             */
            CURLOPT_VERBOSE        => true,
            CURLOPT_STDERR         => $verboseStream,
            CURLINFO_HEADER_OUT    => true,

            /*
             * Capture the response headers separately from the body.
             */
            CURLOPT_HEADERFUNCTION => static function (
                $curl,
                string $headerLine
            ) use (&$responseHeaders): int {
                $length = strlen($headerLine);
                $trimmedHeader = trim($headerLine);

                if ($trimmedHeader !== '') {
                    $responseHeaders[] = $trimmedHeader;
                }

                return $length;
            },
        ]);

        $result = curl_exec($ch);

        $curlErrorNumber  = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        $httpCode         = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType      = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $requestHeaders   = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        rewind($verboseStream);
        $curlTrace = stream_get_contents($verboseStream);

        fclose($verboseStream);
        curl_close($ch);

        $diagnostics = [
            'url'             => $url,
            'httpCode'        => $httpCode,
            'responseType'    => $contentType,
            'requestHeaders'  => $requestHeaders,
            'responseHeaders' => $responseHeaders,
            'responseBody'    => $result,
            'curlErrorNumber' => $curlErrorNumber,
            'curlErrorMessage' => $curlErrorMessage,
            'curlTrace'       => $curlTrace,

            /*
             * Avoid logging the complete XML in production because
             * prescription XML can contain patient information.
             */
            'requestBodyLength' => strlen($xml),
        ];

        log_message(
            'debug',
            "Status message request:\n{diagnostics}",
            [
                'diagnostics' => json_encode(
                    $diagnostics,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                ),
            ]
        );

        if ($result === false) {
            throw new RuntimeException(
                "Status message connection failed ({$curlErrorNumber}): "
                . $curlErrorMessage
            );
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException(
                "Status endpoint returned HTTP {$httpCode}. "
                . "Response: {$result}"
            );
        }

        return $result;
    }

    public function sendScriptExchangeStatus()
    {
        $xml = "";
        $this->scriptAdapter->postMessage(ScriptExchange, $xml);
    }
}
