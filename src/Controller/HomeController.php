<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomeController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'message' => 'TYPO3 Learning Platform API root',
        ]);
    }
}
