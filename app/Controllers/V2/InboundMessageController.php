<?php

namespace App\Controllers\V2;

use App\Controllers\BaseController;
use App\Mapper\Messages\MessageXmlMapper;
use App\Repositories\IncomingMessageService;
use App\Repositories\MessageRepository;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;
use Throwable;

class InboundMessageController extends BaseController
{
    public function __construct()
    {
        $this->repository = new MessageRepository();
        $this->repository->initFM([
            'host' => env('Scripts_Server'),
            'database' => env('Scripts_File'),
            'username' => env('Scripts_Server_Username'),
            'password' => env('Scripts_Server_Pass')
        ]);
        $this->messageService = new IncomingMessageService(new MessageXmlMapper(), $this->repository);
    }

    public function saveMessage($platform): ResponseInterface
    {
        $rawXml = (string)$this->request->getBody();
        try {
            $result = $this->messageService->process($rawXml, $platform);
            $xmlResponse = $result['xmlResponse'];
            return $this->xmlResponse($xmlResponse);
        } catch (InvalidArgumentException $exception) {
            return $this->xmlResponse([
                'Code' => 'Invalid XML',
                'dev' => $exception->getMessage(),
            ], 400);
        } catch (Throwable $exception) {
            return $this->xmlResponse([
                'Code' => 'Communication Error',
                'dev' => 'The message could not be processed.',
            ], 500);
        }
    }

    private function xmlResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/xml', 'UTF-8')
            ->setBody(view('status_message', $data, ['debug' => false]));
    }

    public function saveAPIMessage()
    {
        return $this->saveMessage("APIv3");
    }

    public function saveScriptExchangeMessage()
    {
        return $this->saveMessage("ScriptExchange");
    }

    public function saveSurescriptMessage()
    {
        return $this->saveMessage("SureScript");
    }

}
