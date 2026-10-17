<?php

namespace App\Controller;

use App\Entity\Tienda;
use App\Repository\TiendaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class TiendaController extends AbstractController
{
    // ==========================================
    // GET - LISTAR TODAS LAS TIENDAS
    // ==========================================

    #[Route('/api/tiendas', name: 'api_tiendas_listar', methods: ['GET'])]
    public function listar(TiendaRepository $tiendaRepository): JsonResponse
    {
        $tiendas = $tiendaRepository->findBy([], ['nombre' => 'ASC']);

        $resultado = [];

        foreach ($tiendas as $tienda) {
            $resultado[] = [
                'id' => $tienda->getId(),
                'nombre' => $tienda->getNombre()
            ];
        }

        return $this->json($resultado);
    }

    // ==========================================
    // POST - CREAR UNA NUEVA TIENDA
    // ==========================================

    #[Route('/api/tiendas', name: 'api_tiendas_crear', methods: ['POST'])]
    public function crear(
        Request $request,
        EntityManagerInterface $em,
        TiendaRepository $tiendaRepository
    ): JsonResponse {

        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos)) {
            return $this->json([
                'error' => 'El cuerpo de la solicitud debe contener un JSON válido.'
            ], 400);
        }

        $nombre = trim($datos['nombre'] ?? '');

        if ($nombre === '') {
            return $this->json([
                'error' => 'El nombre de la tienda es obligatorio.'
            ], 400);
        }

        if (mb_strlen($nombre) > 150) {
            return $this->json([
                'error' => 'El nombre no puede superar 150 caracteres.'
            ], 400);
        }

        $existentes = $tiendaRepository->findAll();

        foreach ($existentes as $existente) {
            if (mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)) {
                return $this->json([
                    'error' => 'Ya existe una tienda con ese nombre.'
                ], 409);
            }
        }

        $tienda = new Tienda();
        $tienda->setNombre($nombre);

        $em->persist($tienda);
        $em->flush();

        return $this->json([
            'mensaje' => 'Tienda creada correctamente.',
            'tienda' => [
                'id' => $tienda->getId(),
                'nombre' => $tienda->getNombre()
            ]
        ], 201);
    }

    // ==========================================
    // PUT - ACTUALIZAR UNA TIENDA
    // ==========================================

    #[Route('/api/tiendas/{id}', name: 'api_tiendas_actualizar', methods: ['PUT'])]
    public function actualizar(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        TiendaRepository $tiendaRepository
    ): JsonResponse {

        $tienda = $tiendaRepository->find($id);

        if (!$tienda) {
            return $this->json([
                'error' => 'Tienda no encontrada.'
            ], 404);
        }

        $datos = json_decode($request->getContent(), true);

        if (!is_array($datos)) {
            return $this->json([
                'error' => 'El cuerpo de la solicitud debe contener un JSON válido.'
            ], 400);
        }

        $nombre = trim($datos['nombre'] ?? '');

        if ($nombre === '') {
            return $this->json([
                'error' => 'El nombre de la tienda es obligatorio.'
            ], 400);
        }

        if (mb_strlen($nombre) > 150) {
            return $this->json([
                'error' => 'El nombre no puede superar 150 caracteres.'
            ], 400);
        }

        $existentes = $tiendaRepository->findAll();

        foreach ($existentes as $existente) {
            if (
                $existente->getId() !== $id &&
                mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)
            ) {
                return $this->json([
                    'error' => 'Ya existe otra tienda con ese nombre.'
                ], 409);
            }
        }

        $tienda->setNombre($nombre);

        $em->flush();

        return $this->json([
            'mensaje' => 'Tienda actualizada correctamente.',
            'tienda' => [
                'id' => $tienda->getId(),
                'nombre' => $tienda->getNombre()
            ]
        ]);
    }

    // ==========================================
    // DELETE - ELIMINAR UNA TIENDA
    // ==========================================

    #[Route('/api/tiendas/{id}', name: 'api_tiendas_eliminar', methods: ['DELETE'])]
    public function eliminar(
        int $id,
        EntityManagerInterface $em,
        TiendaRepository $tiendaRepository
    ): JsonResponse {

        $tienda = $tiendaRepository->find($id);

        if (!$tienda) {
            return $this->json([
                'error' => 'Tienda no encontrada.'
            ], 404);
        }

        // Evitar eliminar tiendas que tengan empleados asignados.
        $empleadosAsignados = $em->getRepository(\App\Entity\Empleado::class)
            ->count(['tienda' => $tienda]);

        if ($empleadosAsignados > 0) {
            return $this->json([
                'error' => 'No se puede eliminar esta tienda porque tiene empleados asignados.'
            ], 409);
        }

        $em->remove($tienda);
        $em->flush();

        return $this->json([
            'mensaje' => 'Tienda eliminada correctamente.'
        ]);
    }
}
