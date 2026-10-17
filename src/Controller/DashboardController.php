<?php

namespace App\Controller;

use App\Entity\Empleado;
use App\Entity\Puesto;
use App\Entity\Tienda;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Consultar la cantidad real de registros en MariaDB
        $totalEmpleados = $entityManager
            ->getRepository(Empleado::class)
            ->count([]);

        $totalTiendas = $entityManager
            ->getRepository(Tienda::class)
            ->count([]);

        $totalPuestos = $entityManager
            ->getRepository(Puesto::class)
            ->count([]);

        // Calcular la nómina mensual
        $empleados = $entityManager
            ->getRepository(Empleado::class)
            ->findAll();

        $nominaMensual = 0;

        foreach ($empleados as $empleado) {
            $nominaMensual += (float) $empleado->getSalario();
        }

        return $this->render('dashboard/index.html.twig', [
            'titulo' => 'Dashboard - Sterenfix',
            'totalEmpleados' => $totalEmpleados,
            'totalTiendas' => $totalTiendas,
            'totalPuestos' => $totalPuestos,
            'nominaMensual' => $nominaMensual,
        ]);
    }
}
