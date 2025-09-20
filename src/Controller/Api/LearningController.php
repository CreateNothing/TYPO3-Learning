<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LearningController extends AbstractController
{
    #[Route('/api/learn/generate', name: 'api_learn_generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        return new JsonResponse([
            'status' => 'not_implemented',
            'payload' => $payload,
        ], Response::HTTP_NOT_IMPLEMENTED);
    }

    #[Route('/api/admin/import', name: 'api_admin_import', methods: ['POST'])]
    public function import(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        return new JsonResponse([
            'status' => 'not_implemented',
            'payload' => $payload,
        ], Response::HTTP_NOT_IMPLEMENTED);
    }
}
