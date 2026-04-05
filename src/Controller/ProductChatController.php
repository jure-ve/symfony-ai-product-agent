<?php

namespace App\Controller;

use App\AI\ProductChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProductChatController extends AbstractController
{
    #[Route('/api/chat', methods: ['POST'])]
    public function chat(Request $request, ProductChatService $chatService): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $question = $body['question'] ?? '';

        if (empty($question)) {
            return $this->json(['error' => 'Question is required'], 400);
        }

        $answer = $chatService->ask($question);

        return $this->json(['answer' => $answer]);
    }
}