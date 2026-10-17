<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReporteWebController extends AbstractController
{
    #[Route('/reportes', name: 'app_reportes', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('reportes/index.html.twig', [
            'titulo' => 'Reportes - Sterenfix',
        ]);
    }
}
