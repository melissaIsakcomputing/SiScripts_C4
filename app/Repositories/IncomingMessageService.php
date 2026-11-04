<?php

namespace App\Repositories;

use App\Mapper\Messages\MessageXmlMapper;
use RuntimeException;

class IncomingMessageService
{
    public function __construct(private MessageXmlMapper $mapper, private MessageRepository $repository)
    {
    }

    public function process(string $rawXml, $platform): array
    {
        $message = $this->mapper->fromXml($rawXml, $platform);
        $storeResult = $this->repository->storeIncomingMessage($message);
        $xmlResponse = [
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
            'debug' => false,
        ];
        return [
            'message' => $message,
            'xmlResponse' => $xmlResponse,
            'recordId' => $storeResult['data']['recordId'] ?? null,
        ];
    }
}