<?php

namespace App\Controller;

use App\Entity\Empleado;
use App\Entity\Puesto;
use App\Entity\Tienda;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/empleados')]
class EmpleadoController extends AbstractController
{
    #[Route('', name: 'api_empleados_listar', methods: ['GET'])]
    public function listar(EntityManagerInterface $em): JsonResponse
    {
        $empleados = $em->getRepository(Empleado::class)->findAll();

        $datos = [];

        foreach ($empleados as $empleado) {
            $datos[] = $this->formatearEmpleado($empleado);
        }

        return $this->json($datos);
    }

    #[Route('', name: 'api_empleados_crear', methods: ['POST'])]
    public function crear(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos)) {
            return $this->json([
                'error' => 'JSON inválido.'
            ], 400);
        }

        $empleado = new Empleado();

        $error = $this->actualizarDatos($empleado, $datos, $em);

        if ($error !== null) {
            return $this->json(['error' => $error], 400);
        }

        $em->persist($empleado);
        $em->flush();

        return $this->json([
            'mensaje' => 'Empleado creado correctamente.',
            'empleado' => $this->formatearEmpleado($empleado)
        ], 201);
    }

    #[Route('/{id}', name: 'api_empleados_actualizar', methods: ['PUT'])]
    public function actualizar(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $empleado = $em->getRepository(Empleado::class)->find($id);

        if (!$empleado) {
            return $this->json([
                'error' => 'Empleado no encontrado.'
            ], 404);
        }

        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos)) {
            return $this->json([
                'error' => 'JSON inválido.'
            ], 400);
        }

        $error = $this->actualizarDatos($empleado, $datos, $em);

        if ($error !== null) {
            return $this->json(['error' => $error], 400);
        }

        $em->flush();

        return $this->json([
            'mensaje' => 'Empleado actualizado correctamente.',
            'empleado' => $this->formatearEmpleado($empleado)
        ]);
    }

    #[Route('/{id}', name: 'api_empleados_eliminar', methods: ['DELETE'])]
    public function eliminar(
        int $id,
        EntityManagerInterface $em
    ): JsonResponse {
        $empleado = $em->getRepository(Empleado::class)->find($id);

        if (!$empleado) {
            return $this->json([
                'error' => 'Empleado no encontrado.'
            ], 404);
        }

        if ($empleado->getRegistroEmpleados()->count() > 0) {
            return $this->json([
                'error' => 'No se puede eliminar un empleado que tiene registros asociados.'
            ], 409);
        }

        $em->remove($empleado);
        $em->flush();

        return $this->json([
            'mensaje' => 'Empleado eliminado correctamente.'
        ]);
    }

    private function actualizarDatos(
        Empleado $empleado,
        array $datos,
        EntityManagerInterface $em
    ): ?string {
        $nombres = $datos['nombres'] ?? $empleado->getNombres();
        $apellidos = $datos['apellidos'] ?? $empleado->getApellidos();
        $fecha = $datos['fechaNacimiento']
            ?? $empleado->getFechaNacimiento()?->format('Y-m-d');
        $salario = $datos['salario'] ?? $empleado->getSalario();

        $puestoId = $datos['puestoId']
            ?? $empleado->getPuesto()?->getId();

        $tiendaId = $datos['tiendaId']
            ?? $empleado->getTienda()?->getId();

        if (
            !is_string($nombres) ||
            trim($nombres) === '' ||
            !is_string($apellidos) ||
            trim($apellidos) === ''
        ) {
            return 'Los nombres y apellidos son obligatorios.';
        }

        if (
            strlen($nombres) > 100 ||
            strlen($apellidos) > 100
        ) {
            return 'Los nombres y apellidos no pueden superar 100 caracteres.';
        }

        if (!is_string($fecha)) {
            return 'La fecha de nacimiento es inválida.';
        }

        $fechaNacimiento = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $fecha
        );

        if (
            !$fechaNacimiento ||
            $fechaNacimiento->format('Y-m-d') !== $fecha
        ) {
            return 'La fecha debe tener el formato YYYY-MM-DD.';
        }

        if (
            !is_scalar($salario) ||
            !is_numeric($salario) ||
            (float) $salario < 0 ||
            (float) $salario > 9999999999.99
        ) {
            return 'El salario debe ser un número válido no negativo.';
        }

        if (
            !is_numeric($puestoId) ||
            filter_var($puestoId, FILTER_VALIDATE_INT) === false ||
            !is_numeric($tiendaId) ||
            filter_var($tiendaId, FILTER_VALIDATE_INT) === false
        ) {
            return 'El puesto y la tienda son obligatorios.';
        }

        $puesto = $em->getRepository(Puesto::class)->find((int) $puestoId);
        $tienda = $em->getRepository(Tienda::class)->find((int) $tiendaId);

        if (!$puesto || !$tienda) {
            return 'El puesto o la tienda seleccionados no existen.';
        }

        $empleado->setNombres(trim($nombres));
        $empleado->setApellidos(trim($apellidos));
        $empleado->setFechaNacimiento($fechaNacimiento);
        $empleado->setSalario(number_format((float) $salario, 2, '.', ''));
        $empleado->setPuesto($puesto);
        $empleado->setTienda($tienda);

        return null;
    }

    private function formatearEmpleado(Empleado $empleado): array
    {
        return [
            'id' => $empleado->getId(),
            'nombres' => $empleado->getNombres(),
            'apellidos' => $empleado->getApellidos(),
            'fechaNacimiento' => $empleado
                ->getFechaNacimiento()?->format('Y-m-d'),
            'fotografia' => $empleado->getFotografia(),
            'salario' => $empleado->getSalario(),
            'puestoId' => $empleado->getPuesto()?->getId(),
            'tiendaId' => $empleado->getTienda()?->getId(),
        ];
    }
}
