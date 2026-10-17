<?php

namespace App\Controller;

use App\Repository\RegistroEmpleadoRepository;
use App\Repository\EmpleadoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegistroWebController extends AbstractController
{
    #[Route('/registros', name: 'app_registros', methods: ['GET'])]
    public function index(
        RegistroEmpleadoRepository $registroRepository,
        EmpleadoRepository $empleadoRepository
    ): Response {
        $registros = $registroRepository->findBy(
            [],
            ['fecha' => 'DESC']
        );

        $empleados = $empleadoRepository->findBy(
            [],
            ['nombres' => 'ASC']
        );

        return $this->render('registros/index.html.twig', [
            'titulo' => 'Registros - Sterenfix',
            'registros' => $registros,
            'empleados' => $empleados,
        ]);
    }
}
