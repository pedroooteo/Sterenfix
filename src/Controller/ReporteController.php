<?php

namespace App\Controller;

use App\Repository\EmpleadoRepository;
use App\Repository\RegistroEmpleadoRepository;
use App\Repository\TiendaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ReporteController extends AbstractController
{
    // ==========================================
    // REPORTE 1: EMPLEADOS Y TOTAL DE SALARIOS
    // ==========================================

    #[Route('/api/reportes/empleados', name: 'api_reporte_empleados', methods: ['GET'])]
    public function empleados(
        EmpleadoRepository $empleadoRepository
    ): JsonResponse {
        $empleados = $empleadoRepository->findAll();

        $resultado = [];
        $totalSalarios = 0;

        foreach ($empleados as $empleado) {
            $salario = (float) $empleado->getSalario();
            $totalSalarios += $salario;

            $resultado[] = [
                'id' => $empleado->getId(),
                'nombres' => $empleado->getNombres(),
                'apellidos' => $empleado->getApellidos(),
                'fechaNacimiento' => $empleado->getFechaNacimiento()?->format('Y-m-d'),
                'fotografia' => $empleado->getFotografia(),
                'puesto' => $empleado->getPuesto()?->getNombre(),
                'tienda' => $empleado->getTienda()?->getNombre(),
                'salario' => $empleado->getSalario()
            ];
        }

        return $this->json([
            'totalEmpleados' => count($resultado),
            'totalSalarios' => number_format($totalSalarios, 2, '.', ''),
            'empleados' => $resultado
        ]);
    }

    // ==========================================
    // REPORTE 2: SALARIOS POR TIENDA
    // ==========================================


#[Route('/api/reportes/salarios-tiendas', name: 'api_reporte_salarios_tiendas', methods: ['GET'])]
public function salariosPorTienda(
    TiendaRepository $tiendaRepository
): JsonResponse {
    $tiendas = $tiendaRepository->findAll();
    $resultado = [];

    foreach ($tiendas as $tienda) {
        $empleados = [];
        $total = 0;

        foreach ($tienda->getEmpleados() as $empleado) {
            $salario = (float) $empleado->getSalario();
            $total += $salario;

            $empleados[] = [
                'id' => $empleado->getId(),
                'nombres' => $empleado->getNombres(),
                'apellidos' => $empleado->getApellidos(),
                'fechaNacimiento' => $empleado->getFechaNacimiento()?->format('Y-m-d'),
                'fotografia' => $empleado->getFotografia(),
                'puesto' => $empleado->getPuesto()?->getNombre(),
                'salario' => $salario
            ];
        }

        // Ordenar empleados por salario de mayor a menor.
        usort($empleados, function ($a, $b) {
            return $b['salario'] <=> $a['salario'];
        });

        $resultado[] = [
            'tiendaId' => $tienda->getId(),
            'tienda' => $tienda->getNombre(),
            'cantidadEmpleados' => count($empleados),
            'totalSalarios' => number_format($total, 2, '.', ''),
            'empleados' => $empleados
        ];
    }

    // Conservamos el orden anterior de las tiendas.
    usort($resultado, function ($a, $b) {
        return (float) $b['totalSalarios']
            <=> (float) $a['totalSalarios'];
    });

    return $this->json($resultado);
}


    // ==========================================
    // REPORTE 3: RECONOCIMIENTOS Y SANCIONES
    // ==========================================

    #[Route('/api/reportes/registros', name: 'api_reporte_registros', methods: ['GET'])]
    public function registros(
        RegistroEmpleadoRepository $registroRepository
    ): JsonResponse {
        $registros = $registroRepository->findBy([], ['fecha' => 'DESC']);

        $resultado = [];
        $positivos = 0;
        $negativos = 0;

        foreach ($registros as $registro) {
            $empleado = $registro->getEmpleado();

            if ($registro->getTipo() === 'positivo') {
                $positivos++;
            }

            if ($registro->getTipo() === 'negativo') {
                $negativos++;
            }

            $resultado[] = [
                'id' => $registro->getId(),
                'empleadoId' => $empleado?->getId(),
                'empleado' => $empleado
                    ? $empleado->getNombres() . ' ' . $empleado->getApellidos()
                    : null,
                'tipo' => $registro->getTipo(),
                'descripcion' => $registro->getDescripcion(),
                'fecha' => $registro->getFecha()?->format('Y-m-d')
            ];
        }

        return $this->json([
            'totalRegistros' => count($resultado),
            'totalPositivos' => $positivos,
            'totalNegativos' => $negativos,
            'registros' => $resultado
        ]);
    }

    // ==========================================
    // REPORTE 4: DETALLE DE UN EMPLEADO
    // ==========================================

    #[Route('/api/reportes/empleado/{id}', name: 'api_reporte_empleado_detalle', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detalleEmpleado(
        int $id,
        EmpleadoRepository $empleadoRepository
    ): JsonResponse {
        $empleado = $empleadoRepository->find($id);

        if (!$empleado) {
            return $this->json([
                'error' => 'El empleado no existe.'
            ], 404);
        }

        $registros = [];

        foreach ($empleado->getRegistroEmpleados() as $registro) {
            $registros[] = [
                'id' => $registro->getId(),
                'tipo' => $registro->getTipo(),
                'descripcion' => $registro->getDescripcion(),
                'fecha' => $registro->getFecha()?->format('Y-m-d')
            ];
        }

        usort($registros, function ($a, $b) {
            return strcmp($b['fecha'], $a['fecha']);
        });

        return $this->json([
            'id' => $empleado->getId(),
            'nombres' => $empleado->getNombres(),
            'apellidos' => $empleado->getApellidos(),
            'fechaNacimiento' => $empleado->getFechaNacimiento()?->format('Y-m-d'),
            'fotografia' => $empleado->getFotografia(),
            'puesto' => $empleado->getPuesto()?->getNombre(),
            'tienda' => $empleado->getTienda()?->getNombre(),
            'salario' => $empleado->getSalario(),
            'registros' => $registros
        ]);
    }
}
