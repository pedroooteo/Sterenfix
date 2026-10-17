<?php

namespace App\Controller;

use App\Entity\Puesto;
use App\Entity\Empleado;
use App\Repository\PuestoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PuestoController extends AbstractController
{
    // ==========================================
    // GET - LISTAR PUESTOS
    // ==========================================

    #[Route('/api/puestos', name: 'api_puestos_listar', methods: ['GET'])]
    public function listar(PuestoRepository $puestoRepository): JsonResponse
    {
        $puestos = $puestoRepository->findBy([], ['nombre' => 'ASC']);

        $resultado = [];

        foreach ($puestos as $puesto) {
            $resultado[] = [
                'id' => $puesto->getId(),
                'nombre' => $puesto->getNombre(),
            ];
        }

        return $this->json($resultado);
    }

    // ==========================================
    // POST - CREAR PUESTO
    // ==========================================

    #[Route('/api/puestos', name: 'api_puestos_crear', methods: ['POST'])]
    public function crear(
        Request $request,
        EntityManagerInterface $em,
        PuestoRepository $puestoRepository
    ): JsonResponse {
        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos)) {
            return $this->json(['error' => 'JSON inválido.'], 400);
        }

        $nombre = trim($datos['nombre'] ?? '');

        if ($nombre === '') {
            return $this->json([
                'error' => 'El nombre del puesto es obligatorio.',
            ], 400);
        }

        if (mb_strlen($nombre) > 150) {
            return $this->json([
                'error' => 'El nombre no puede superar 150 caracteres.',
            ], 400);
        }

        foreach ($puestoRepository->findAll() as $existente) {
            if (mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)) {
                return $this->json([
                    'error' => 'Ya existe un puesto con ese nombre.',
                ], 409);
            }
        }

        $puesto = new Puesto();
        $puesto->setNombre($nombre);

        $em->persist($puesto);
        $em->flush();

        return $this->json([
            'mensaje' => 'Puesto creado correctamente.',
            'puesto' => [
                'id' => $puesto->getId(),
                'nombre' => $puesto->getNombre(),
            ],
        ], 201);
    }

    // ==========================================
    // PUT - ACTUALIZAR PUESTO
    // ==========================================

    #[Route('/api/puestos/{id}', name: 'api_puestos_actualizar', methods: ['PUT'])]
    public function actualizar(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        PuestoRepository $puestoRepository
    ): JsonResponse {
        $puesto = $puestoRepository->find($id);

        if (!$puesto) {
            return $this->json(['error' => 'Puesto no encontrado.'], 404);
        }

        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos)) {
            return $this->json(['error' => 'JSON inválido.'], 400);
        }

        $nombre = trim($datos['nombre'] ?? '');

        if ($nombre === '') {
            return $this->json([
                'error' => 'El nombre del puesto es obligatorio.',
            ], 400);
        }

        if (mb_strlen($nombre) > 150) {
            return $this->json([
                'error' => 'El nombre no puede superar 150 caracteres.',
            ], 400);
        }

        foreach ($puestoRepository->findAll() as $existente) {
            if (
                $existente->getId() !== $id &&
                mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)
            ) {
                return $this->json([
                    'error' => 'Ya existe otro puesto con ese nombre.',
                ], 409);
            }
        }

        $puesto->setNombre($nombre);
        $em->flush();

        return $this->json([
            'mensaje' => 'Puesto actualizado correctamente.',
            'puesto' => [
                'id' => $puesto->getId(),
                'nombre' => $puesto->getNombre(),
            ],
        ]);
    }

    // ==========================================
    // DELETE - ELIMINAR PUESTO
    // ==========================================

    #[Route('/api/puestos/{id}', name: 'api_puestos_eliminar', methods: ['DELETE'])]
    public function eliminar(
        int $id,
        EntityManagerInterface $em,
        PuestoRepository $puestoRepository
    ): JsonResponse {
        $puesto = $puestoRepository->find($id);

        if (!$puesto) {
            return $this->json(['error' => 'Puesto no encontrado.'], 404);
        }

        $empleadosAsignados = $em->getRepository(Empleado::class)
            ->count(['puesto' => $puesto]);

        if ($empleadosAsignados > 0) {
            return $this->json([
                'error' => 'No se puede eliminar este puesto porque tiene empleados asignados.',
            ], 409);
        }

        $em->remove($puesto);
        $em->flush();

        return $this->json([
            'mensaje' => 'Puesto eliminado correctamente.',
        ]);
    }
}
