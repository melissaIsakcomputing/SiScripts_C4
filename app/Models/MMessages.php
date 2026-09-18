<?php

namespace App\Models;

use CodeIgniter\Model;

class MMessages extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    function error($result)
    {
        if (isset($result["messages"][0]["code"]) && $result["messages"][0]["code"] != '0') {
            return '(' . $result["messages"][0]["code"] . ') ' . $result["messages"][0]["message"];
        }
        return 0;
    }

    function Result($error_code = 0, $error_msg = 0, $result = '')
    {
        $message = $result["messages"][0];
        if (isset($message["code"]) && $message["code"] !== '') {
            $return['error_code'] = $result["messages"][0]["code"];
        }
        if (isset($result["messages"][0]["message"]) && $result["messages"][0]["message"] !== '') {
            $return['error'] = $error_msg;
        }
        $return['data'] = $result;
        return $return;
    }


    function logMessage($dataMessage, $xml = "")
    {
        $layout = "Messages";
        $this->fm = new RestFM(
            DataServer,
            DataFile,
            DataServerUsername,
            DataServerPass,
            $layout,
            "fmtoken1"
        );

        $record = [
            'XML' => urlencode($dataMessage['rawXml']),
            'SenderPlatform' => urlencode($dataMessage['senderPlatform']),
            'MessageToID' => urlencode($dataMessage['messageToId']),
            'MessageToQ' => urlencode($dataMessage['messageToQualifier']),
            'MessageFromID' => urlencode($dataMessage['messageFromId']),
            'MessageFromQ' => urlencode($dataMessage['messageFromQualifier']),
        ];
        $result = $this->fm->createRecord(['fieldData' => $record], $layout);
        return ['error' => $this->error($result)];
    }

    function sendMessage($xml, $data)
    {
        $layout = "Messages";
        $host = "";
        $method = "mailbox";
        $messageTo = $data['MessageToID'];
        $connection = $this->getPharmacyConnection($messageTo);
        if (isset($connection)) {
            $host = $connection['host'];
            $method = $connection['method'];
        }
        if ($method !== "push") {
            return ['error' => 0];
        }
        $this->fm = $this->getRestFM(
            $host,
            HostFile,
            HostUsername,
            HostPass,
            $layout,
            "fmtoken2"
        );
        $record = [
            'XML_Encode' => base64_encode($xml),
            'MessageToID' => urlencode($data['MessageToID']),
            'MessageToQ' => urlencode($data['MessageToQ']),
            'MessageFromID' => urlencode($data['MessageFromID']),
            'MessageFromQ' => urlencode($data['MessageFromQ'])
        ];
        $result = $this->fm->createRecord(['fieldData' => $record], $layout);
        return ['error' => $this->error($result)];
    }

    public function logResponseMessage($message)
    {
        $layout = "Messages";
        $this->fm = $this->getRestFM(
            DataServer .
            DataFile,
            DataServerUsername,
            DataServerPass,
            $layout,
            "fmtoken1"
        );
        $record = [
            'MessageID' => $message['MessageID'],
            'RelatesToMessageID' => urlencode($message['RelatesToMessageID']),
            'MessageToID' => urlencode($message['MessageToID']),
            'MessageToQ' => urlencode($message['MessageToQ']),
            'MessageFromID' => urlencode($message['MessageFromID']),
            'MessageFromQ' => urlencode($message['MessageFromQ']),
            'SentTime' => $message['SentTime'],
            'MessageStatusCode' => $message['Code'],
            'MessageType' => $message['MessageType']
        ];
        $result = $this->fm->createRecord(['fieldData' => $record], $layout);
        return ['error' => $this->error($result)];
    }

    public function sendResponseMessage(array $message): array
    {
        $layout = "Messages";
        $connection = $this->getPharmacyConnection($message['MessageToID']);
        /*
         * Currently all pharmacies are mailbox.
         * If later you add push pharmacies,
         * only the configuration changes.
         */
        if ($connection['method'] !== 'push') {
            return ['error' => 0];
        }
        $this->fm = new RestFM(
            $connection['host'],
            HostFile,
            HostUsername,
            HostPass,
            $layout,
            'fmtoken2'
        );
        $record = [
            'MessageID' => $message['MessageID'],
            'RelatesToMessageID' => $message['RelatesToMessageID'],
            'MessageToID' => $message['MessageToID'],
            'MessageToQ' => $message['MessageToQ'],
            'MessageFromID' => $message['MessageFromID'],
            'MessageFromQ' => $message['MessageFromQ'],
            'SentTime' => $message['SentTime'],
            'MessageStatusCode' => $message['Code'],
            'MessageType' => $message['MessageType'],
        ];
        $result = $this->fm->createRecord(['fieldData' => $record], 'Messages');
        return ['error' => $this->error($result)];
    }

    public function getMessage(): array
    {
        $layout = 'Messages';
        $criteria = [
            'query' => [
                [
                    'id' => '2020'
                ]
            ]
        ];
        $result = $this->fm->findRecords($criteria, $layout);
        $return = ['error' => $this->error($result)];
        if ($return['error'] === 0) {
            $records = [];
            foreach ($result['response']['data'] as $item) {
                $records[] = ['id' => trim($item['fieldData']['id'])];
            }
            $return['data'] = $records;
        }
        return $return;
    }

    function logout()
    {
        $this->fm->logout();
    }

    private function getPharmacyConnection(string $messageTo): array
    {
        return [
            'host' => Pharmacies[$messageTo] ?? '',
            'method' => 'mailbox'
        ];
    }
}