<?php

namespace App\Controller;

use App\Repository\PuestoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PuestoWebController extends AbstractController
{
    #[Route('/puestos', name: 'app_puestos', methods: ['GET'])]
    public function index(PuestoRepository $puestoRepository): Response
    {
        $puestos = $puestoRepository->findBy([], ['nombre' => 'ASC']);

        return $this->render('puestos/index.html.twig', [
            'titulo' => 'Puestos - Sterenfix',
            'puestos' => $puestos,
        ]);
    }
}
