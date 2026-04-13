<?php

namespace App\Controller;

use App\AI\ProductChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductChatController extends AbstractController
{
    #[Route('/chat-demo', methods: ['GET'], name: 'chat_demo')]
    public function demo(): Response
    {
        return $this->render('chat/demo.html.twig');
    }

    #[Route('/api/chat', methods: ['POST'])]
    public function chat(Request $request, ProductChatService $chatService): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $question = trim(strip_tags($body['question'] ?? ''));

        if (empty($question)) {
            return $this->json(['error' => 'Question is required'], 400);
        }

        try {
            $answer = $chatService->ask($question);
            return $this->json(['answer' => $answer]);
        } catch (\Throwable $e) {
            error_log("AI ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $errorMsg = "¡Vaya! He tenido un problema interno intentando pensar la respuesta o procesar tu petición. ";
            if (str_contains(strtolower($e->getMessage()), '401') || str_contains(strtolower($e->getMessage()), 'unauthorized')) {
                $errorMsg .= "Verifica tu GROQ_API_KEY en el archivo .env.";
            } else {
                $errorMsg .= "¿Podrías reformular la pregunta con otras palabras?";
            }
            return $this->json(['error' => $errorMsg], 500);
        }
    }
}