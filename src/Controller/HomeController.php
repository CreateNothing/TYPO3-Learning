<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/battle', name: 'app_battle', methods: ['GET'])]
    public function battle(): Response
    {
        return $this->render('home/battle.html.twig');
    }

    #[Route('/learning', name: 'app_learning', methods: ['GET'])]
    public function learning(): Response
    {
        return $this->render('home/learning.html.twig');
    }
}
