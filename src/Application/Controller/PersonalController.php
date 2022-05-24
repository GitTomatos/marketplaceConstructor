<?php

namespace App\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonalController extends AbstractController
{

    #[Route('/my-marketplace-example', name: 'myMarketplaceExample')]
    public function menu(): Response
    {
        return $this->renderForm(
            '@personal/my_marketplace_example.twig',
            [
            ]
        );
    }
}
