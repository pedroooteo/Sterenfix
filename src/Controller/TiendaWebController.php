<?php

namespace App\Controller;

use App\Repository\TiendaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TiendaWebController extends AbstractController
{
    #[Route('/tiendas', name: 'app_tiendas', methods: ['GET'])]
    public function index(TiendaRepository $tiendaRepository): Response
    {
        $tiendas = $tiendaRepository->findBy([], ['nombre' => 'ASC']);

        return $this->render('tiendas/index.html.twig', [
            'titulo' => 'Tiendas - Sterenfix',
            'tiendas' => $tiendas,
        ]);
    }
}
