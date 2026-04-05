<?php

namespace App\AI;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

final readonly class ProductChatService
{
    public function __construct(
        private AgentInterface $defaultAgent,
    ) {}

    public function ask(string $userQuestion): string
    {
        $messages = new MessageBag(
            Message::forSystem(
                'You are a helpful assistant for an online store. ' .
                'Answer questions about products concisely and accurately. ' .
                'If you need to check a price or product details, use the available tools.'
            ),
            Message::ofUser($userQuestion),
        );

        $result = $this->defaultAgent->call($messages);

        return $result->getContent();
    }
}