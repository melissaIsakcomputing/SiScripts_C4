<?php

namespace App\Models;

use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;

class MessageXmlMapper
{
    /**
     * Converts the incoming XML into application data.
     */
    public function fromXml(string $rawXml, $platform): array
    {
        $rawXml = trim($rawXml);

        if ($rawXml === '') {
            throw new InvalidArgumentException('The XML body is empty.');
        }

        $document = new DOMDocument();

        $previousSetting = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $loaded = $document->loadXML( $rawXml,LIBXML_NONET | LIBXML_NOBLANKS );

        $errors = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        if (!$loaded) {
            throw new InvalidArgumentException(
                $this->formatXmlErrors($errors)
            );
        }

        $xpath = new DOMXPath($document);

        $root = $document->documentElement;

        if (!$root instanceof DOMElement || $root->localName !== 'Message') {
            throw new InvalidArgumentException(
                'The XML root element must be Message.'
            );
        }

        $toNode = $this->requiredNode(
            $xpath,
            '//*[local-name()="Header"]/*[local-name()="To"]'
        );

        $fromNode = $this->requiredNode(
            $xpath,
            '//*[local-name()="Header"]/*[local-name()="From"]'
        );

        return [
            'senderPlatform' => $platform,

            'messageId' => $this->requiredValue(  $xpath,'//*[local-name()="Header"]/*[local-name()="MessageID"]' ),

            'messageToId' => trim($toNode->textContent),
            'messageToQualifier' => $toNode->getAttribute('Qualifier'),

            'messageFromId' => trim($fromNode->textContent),
            'messageFromQualifier' => $fromNode->getAttribute('Qualifier'),

            'versions' => [
                'datatypes' => $this->versionAttribute(
                    $root,
                    'DatatypesVersion'
                ),
                'transport' => $this->versionAttribute(
                    $root,
                    'TransportVersion'
                ),
                'transactionDomain' => $this->requiredAttribute(
                    $root,
                    'TransactionDomain'
                ),
                'transaction' => $this->versionAttribute(
                    $root,
                    'TransactionVersion'
                ),
                'structures' => $this->versionAttribute(
                    $root,
                    'StructuresVersion'
                ),
                'ecl' => $this->versionAttribute(
                    $root,
                    'ECLVersion'
                ),
            ],

            // Store the complete normalized XML.
            'rawXml' => $document->saveXML(),

            'receivedAt' => date(DATE_ATOM),
        ];
    }

    /**
     * Maps application fields to FileMaker fields.
     *
     * This method is called by Repository::create().
     */
    public function toFM($data, bool $isUpdate = false): array
    {
        $mapped = [
            'MessageID' => $data['messageId'],
            'MessageToID' => $data['messageToId'],
            'MessageToQ' => $data['messageToQualifier'],
            'MessageFromID' => $data['messageFromId'],
            'MessageFromQ' => $data['messageFromQualifier'],
            'XML' => $data['rawXml'],
//            'ReceivedAt' => $data['receivedAt'],
        ];

        return array_filter(
            $mapped,
            static fn($value) => $value !== null
        );
    }

    private function requiredValue(DOMXPath $xpath, string $expression): string
    {
        $node = $this->requiredNode($xpath, $expression);
        $value = trim($node->textContent);

        if ($value === '') {
            throw new InvalidArgumentException(
                'A required XML element is empty: ' . $expression
            );
        }

        return $value;
    }

    private function requiredNode(DOMXPath $xpath, string $expression): DOMElement
    {
        $nodes = $xpath->query($expression);

        if (
            $nodes === false ||
            $nodes->length === 0 ||
            !($nodes->item(0) instanceof DOMElement)
        ) {
            throw new InvalidArgumentException(
                'A required XML element is missing: ' . $expression
            );
        }

        return $nodes->item(0);
    }

    private function formatXmlErrors(array $errors): string
    {
        if ($errors === []) {
            return 'The request body is not valid XML.';
        }

        $messages = [];

        foreach ($errors as $error) {
            $messages[] = sprintf(
                'Line %d, column %d: %s',
                $error->line,
                $error->column,
                trim($error->message)
            );
        }

        return implode(' | ', $messages);
    }


    private const SUPPORTED_VERSIONS = [
        '20170715',
        '2023011',
        '20230115'
    ];

    private function versionAttribute(\DOMElement $element, string $attribute): string
    {
        $value = $this->requiredAttribute($element, $attribute);

        if (!in_array($value, self::SUPPORTED_VERSIONS, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported %s: %s. Supported versions: %s.',
                    $attribute,
                    $value,
                    implode(', ', self::SUPPORTED_VERSIONS)
                )
            );
        }

        return $value;
    }


    private function requiredAttribute(DOMElement $element, string $attribute): string
    {
        $value = trim($element->getAttribute($attribute));

        if ($value === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'The required XML attribute %s is missing.',
                    $attribute
                )
            );
        }

        return $value;
    }
}