<?php

namespace App\Controller;

use App\Repository\EmpleadoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

final class FotografiaEmpleadoController extends AbstractController
{
    #[Route(
        '/api/empleados/{id}/fotografia',
        name: 'api_empleados_fotografia',
        methods: ['POST'],
        requirements: ['id' => '\d+']
    )]
    public function subirFotografia(
        int $id,
        Request $request,
        EmpleadoRepository $empleadoRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $empleado = $empleadoRepository->find($id);

        if (!$empleado) {
            return $this->json([
                'error' => 'El empleado no existe.'
            ], 404);
        }

        $archivo = $request->files->get('fotografia');

        if (!$archivo instanceof UploadedFile) {
            return $this->json([
                'error' => 'Debes enviar una fotografía.'
            ], 400);
        }

        if (!$archivo->isValid()) {
            return $this->json([
                'error' => 'No se pudo recibir el archivo.'
            ], 400);
        }

        if ($archivo->getSize() > 5 * 1024 * 1024) {
            return $this->json([
                'error' => 'La fotografía no puede superar los 5 MB.'
            ], 400);
        }

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $mime = $archivo->getMimeType();

        if (!isset($tiposPermitidos[$mime])) {
            return $this->json([
                'error' => 'Solo se permiten fotografías JPG, PNG o WebP.'
            ], 400);
        }

        $extension = $tiposPermitidos[$mime];

        $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extension;

        $directorio = $this->getParameter('kernel.project_dir')
            . '/public/uploads/empleados';

        try {
            $archivo->move($directorio, $nombreArchivo);

            $rutaAnterior = $empleado->getFotografia();

            $rutaNueva = '/uploads/empleados/' . $nombreArchivo;

            $empleado->setFotografia($rutaNueva);

            $entityManager->flush();

            // Eliminar la fotografía anterior únicamente si
            // pertenece al directorio administrado por este módulo.
            if (
                is_string($rutaAnterior)
                && str_starts_with($rutaAnterior, '/uploads/empleados/')
                && basename($rutaAnterior) ===
                    substr($rutaAnterior, strlen('/uploads/empleados/'))
            ) {
                $archivoAnterior = $directorio . '/' . basename($rutaAnterior);

                if (is_file($archivoAnterior)) {
                    @unlink($archivoAnterior);
                }
            }

            return $this->json([
                'mensaje' => 'Fotografía subida correctamente.',
                'empleadoId' => $empleado->getId(),
                'fotografia' => $rutaNueva
            ]);

        } catch (\Throwable $e) {
            // Evitar dejar archivos huérfanos si falla el guardado.
            if (isset($rutaNueva)) {
                $archivoNuevo = $directorio . '/' . $nombreArchivo;

                if (is_file($archivoNuevo)) {
                    @unlink($archivoNuevo);
                }
            }

            return $this->json([
                'error' => 'No fue posible guardar la fotografía.'
            ], 500);
        }
    }
}
