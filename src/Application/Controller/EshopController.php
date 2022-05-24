<?php

namespace App\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EshopController extends AbstractController
{
    #[Route('/eshop', name: 'app_eshop')]
    public function index(): Response
    {
//        return $this->render('test.html.twig');
        return $this->render('eshop/index.html.twig');
//        return $this->json([
//            'message' => 'Welcome to your new controller!',
//            'path' => 'src/Controller/MainController.php',
//        ]);
    }
}
