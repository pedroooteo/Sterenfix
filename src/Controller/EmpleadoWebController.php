<?php

namespace App\Controller;

use App\Repository\EmpleadoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmpleadoWebController extends AbstractController
{
    #[Route('/empleados', name: 'app_empleados', methods: ['GET'])]
    public function index(EmpleadoRepository $empleadoRepository): Response
    {
        $empleados = $empleadoRepository->findAll();

        return $this->render('empleados/index.html.twig', [
            'titulo' => 'Empleados - Sterenfix',
            'empleados' => $empleados,
        ]);
    }
}
