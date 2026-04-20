<?php

namespace App\AI;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

final readonly class ProductChatService
{
    public function __construct(
        private AgentInterface $defaultAgent,
        private \Symfony\Component\HttpFoundation\RequestStack $requestStack,
    ) {}

    public function streamAsk(string $userQuestion, \Closure $onChunk): void
    {
        $session = $this->requestStack->getSession();
        
        // Initialize history or get existing from session
        $history = $session->get('chat_history', []);
        
        // Append new user message
        $history[] = ['role' => 'user', 'content' => $userQuestion];

        $messages = $this->buildMessageBag($history);

        // Start stream
        $result = $this->defaultAgent->call($messages, ['stream' => true]);
        $stream = $result->getContent();
        $fullAnswer = '';

        foreach ($stream as $delta) {
            if ($delta instanceof \Symfony\AI\Platform\Result\Stream\Delta\TextDelta) {
                $content = $delta->getText();
                if ($content !== '') {
                    $fullAnswer .= $content;
                    $onChunk($content);
                }
            }
        }

        // Guard: if the answer contains a raw tool call leak, reject it
        $isHallucination = preg_match('/^[a-z_]+\{/i', trim($fullAnswer)) || preg_match('/function=/', trim($fullAnswer));
        
        if ($isHallucination) {
            // In streaming we already sent the bad text, but we avoid polluting history
            $fullAnswer = 'Lo siento, no pude obtener la información correctamente.';
        } elseif (empty(trim($fullAnswer))) {
            // If the model returned absolutely nothing, we can stream the fallback now!
            $fullAnswer = 'Lo siento, hubo un problema procesando tu solicitud.';
            $onChunk($fullAnswer);
        }

        // Append assistant response to history
        $history[] = ['role' => 'assistant', 'content' => $fullAnswer];
        
        // Keep only last 10 messages (context window limit)
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }
        
        $session->set('chat_history', $history);
    }

    private function buildMessageBag(array $history): MessageBag
    {
        $messageObjects = [
            Message::forSystem(
                "You are a helpful online store assistant. Reply ONLY in Spanish.\n\n" .
                "AVAILABLE TOOLS:\n" .
                "- 'categories': list available types.\n" .
                "- 'search': lists products by category. Pass exact slug (telefono, portatil, auriculares, reloj). Page defaults to '1' ('2' for more).\n" .
                "- 'price': gets the price of an SKU.\n\n" .
                "RULES:\n" .
                "1. Map user synonyms to exact slugs (e.g., 'laptops'->'portatil', 'relojes'->'reloj' or 'celulares'->'telefono').\n" .
                "2. NO HALLUCINATION: DO NOT invoke unlisted tools (like order/checkout). Never invent products or SKUs.\n" .
                "3. NO SALES: You cannot process orders or take payments; decline gracefully."
            )
        ];

        foreach ($history as $msg) {
            if ($msg['role'] === 'user') {
                $messageObjects[] = Message::ofUser($msg['content']);
            } elseif ($msg['role'] === 'assistant') {
                $messageObjects[] = Message::ofAssistant($msg['content']);
            }
        }

        return new MessageBag(...$messageObjects);
    }
}