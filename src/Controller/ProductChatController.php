<?php

namespace App\Controller;

use App\AI\ProductChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProductChatController extends AbstractController
{
    #[Route('/chat-demo', methods: ['GET'], name: 'chat_demo')]
    public function demo(): Response
    {
        return $this->render('chat/demo.html.twig');
    }

    #[Route('/api/chat', methods: ['POST'])]
    public function chat(Request $request, ProductChatService $chatService): Response
    {
        $body = json_decode($request->getContent(), true);
        $question = trim(strip_tags($body['question'] ?? ''));

        if (empty($question)) {
            return $this->json(['error' => 'Question is required'], 400);
        }

        $response = new StreamedResponse(function () use ($chatService, $question) {
            try {
                $chatService->streamAsk($question, function (string $token) {
                    echo "data: " . json_encode(['text' => $token], JSON_THROW_ON_ERROR) . "\n\n";
                    ob_flush();
                    flush();
                });
                echo "data: [DONE]\n\n";
                ob_flush();
                flush();
            } catch (\Throwable $e) {
                error_log("AI ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                $errorMsg = "Error: " . $e->getMessage() . " in " . basename($e->getFile()) . ":" . $e->getLine();
                echo "data: " . json_encode(['error' => $errorMsg]) . "\n\n";
                ob_flush();
                flush();
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}