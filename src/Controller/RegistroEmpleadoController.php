<?php

namespace App\Controller;

use App\Entity\RegistroEmpleado;
use App\Repository\RegistroEmpleadoRepository;
use App\Repository\EmpleadoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegistroEmpleadoController extends AbstractController
{
    // ==========================================
    // GET - LISTAR REGISTROS
    // ==========================================

    #[Route('/api/registros', name: 'api_registros_listar', methods: ['GET'])]
    public function listar(
        RegistroEmpleadoRepository $registroRepository
    ): JsonResponse {
        $registros = $registroRepository->findBy([], ['fecha' => 'DESC']);
        $resultado = [];

        foreach ($registros as $registro) {
            $empleado = $registro->getEmpleado();

            $resultado[] = [
                'id' => $registro->getId(),
                'descripcion' => $registro->getDescripcion(),
                'tipo' => $registro->getTipo(),
                'fecha' => $registro->getFecha()?->format('Y-m-d'),
                'empleadoId' => $empleado?->getId(),
                'empleado' => $empleado
                    ? $empleado->getNombres() . ' ' . $empleado->getApellidos()
                    : null,
            ];
        }

        return $this->json($resultado);
    }

    // ==========================================
    // POST - CREAR REGISTRO
    // ==========================================

    #[Route('/api/registros', name: 'api_registros_crear', methods: ['POST'])]
    public function crear(
        Request $request,
        EmpleadoRepository $empleadoRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos) || array_is_list($datos)) {
            return $this->json([
                'error' => 'Debe enviar un objeto JSON válido.',
            ], 400);
        }

        $error = $this->validarDatos($datos);

        if ($error !== null) {
            return $this->json(['error' => $error], 400);
        }

        $empleadoId = filter_var(
            $datos['empleadoId'],
            FILTER_VALIDATE_INT
        );

        if ($empleadoId === false || $empleadoId < 1) {
            return $this->json([
                'error' => 'El ID del empleado debe ser un entero positivo.',
            ], 400);
        }

        $empleado = $empleadoRepository->find($empleadoId);

        if (!$empleado) {
            return $this->json([
                'error' => 'El empleado seleccionado no existe.',
            ], 404);
        }

        $fecha = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $datos['fecha']
        );

        $registro = new RegistroEmpleado();
        $registro->setDescripcion(trim($datos['descripcion']));
        $registro->setTipo($datos['tipo']);
        $registro->setFecha($fecha);
        $registro->setEmpleado($empleado);

        $entityManager->persist($registro);
        $entityManager->flush();

        return $this->json([
            'mensaje' => 'Registro creado correctamente.',
            'registro' => $this->formatearRegistro($registro),
        ], 201);
    }

    // ==========================================
    // PUT - ACTUALIZAR REGISTRO
    // ==========================================

    #[Route('/api/registros/{id}', name: 'api_registros_actualizar', methods: ['PUT'])]
    public function actualizar(
        int $id,
        Request $request,
        RegistroEmpleadoRepository $registroRepository,
        EmpleadoRepository $empleadoRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $registro = $registroRepository->find($id);

        if (!$registro) {
            return $this->json([
                'error' => 'Registro no encontrado.',
            ], 404);
        }

        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos) || array_is_list($datos)) {
            return $this->json([
                'error' => 'Debe enviar un objeto JSON válido.',
            ], 400);
        }

        $error = $this->validarDatos($datos);

        if ($error !== null) {
            return $this->json(['error' => $error], 400);
        }

        $empleadoId = filter_var(
            $datos['empleadoId'],
            FILTER_VALIDATE_INT
        );

        if ($empleadoId === false || $empleadoId < 1) {
            return $this->json([
                'error' => 'El ID del empleado debe ser un entero positivo.',
            ], 400);
        }

        $empleado = $empleadoRepository->find($empleadoId);

        if (!$empleado) {
            return $this->json([
                'error' => 'El empleado seleccionado no existe.',
            ], 404);
        }

        $fecha = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $datos['fecha']
        );

        $registro->setDescripcion(trim($datos['descripcion']));
        $registro->setTipo($datos['tipo']);
        $registro->setFecha($fecha);
        $registro->setEmpleado($empleado);

        $entityManager->flush();

        return $this->json([
            'mensaje' => 'Registro actualizado correctamente.',
            'registro' => $this->formatearRegistro($registro),
        ]);
    }

    // ==========================================
    // DELETE - ELIMINAR REGISTRO
    // ==========================================

    #[Route('/api/registros/{id}', name: 'api_registros_eliminar', methods: ['DELETE'])]
    public function eliminar(
        int $id,
        RegistroEmpleadoRepository $registroRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $registro = $registroRepository->find($id);

        if (!$registro) {
            return $this->json([
                'error' => 'Registro no encontrado.',
            ], 404);
        }

        $entityManager->remove($registro);
        $entityManager->flush();

        return $this->json([
            'mensaje' => 'Registro eliminado correctamente.',
        ]);
    }

    // ==========================================
    // VALIDACIONES
    // ==========================================

    private function validarDatos(array $datos): ?string
    {
        foreach (['empleadoId', 'descripcion', 'tipo', 'fecha'] as $campo) {
            if (
                !array_key_exists($campo, $datos) ||
                $datos[$campo] === null ||
                $datos[$campo] === ''
            ) {
                return "El campo $campo es obligatorio.";
            }
        }

        if (
            !is_string($datos['descripcion']) ||
            trim($datos['descripcion']) === ''
        ) {
            return 'La descripción no es válida.';
        }

        if (mb_strlen($datos['descripcion']) > 5000) {
            return 'La descripción no puede superar 5000 caracteres.';
        }

        if (
            !is_string($datos['tipo']) ||
            !in_array($datos['tipo'], ['positivo', 'negativo'], true)
        ) {
            return 'El tipo debe ser positivo o negativo.';
        }

        if (!is_string($datos['fecha'])) {
            return 'La fecha debe tener formato YYYY-MM-DD.';
        }

        $fecha = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $datos['fecha']
        );

        if (
            !$fecha ||
            $fecha->format('Y-m-d') !== $datos['fecha']
        ) {
            return 'La fecha ingresada no es válida.';
        }

        return null;
    }

    // ==========================================
    // FORMATEAR RESPUESTA JSON
    // ==========================================

    private function formatearRegistro(RegistroEmpleado $registro): array
    {
        $empleado = $registro->getEmpleado();

        return [
            'id' => $registro->getId(),
            'descripcion' => $registro->getDescripcion(),
            'tipo' => $registro->getTipo(),
            'fecha' => $registro->getFecha()?->format('Y-m-d'),
            'empleadoId' => $empleado?->getId(),
            'empleado' => $empleado
                ? $empleado->getNombres() . ' ' . $empleado->getApellidos()
                : null,
        ];
    }
}
