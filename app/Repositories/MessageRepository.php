<?php

namespace App\Repositories;

use App\Mapper\Messages\MessageXmlMapper;

class MessageRepository extends Repository
{
    public function __construct()
    {
        parent::__construct('v3_scripts_messages', new MessageXmlMapper());
    }

    public function storeIncomingMessage(array $message): array
    {
        return $this->create($message);
    }
}