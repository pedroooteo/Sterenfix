<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class UsuarioController extends AbstractController
{
    #[Route('/usuarios', name: 'app_usuarios', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $usuarios = $userRepository->findBy([], ['id' => 'ASC']);

        return $this->render('usuario/index.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }

    #[Route('/usuarios/nuevo', name: 'app_usuarios_nuevo', methods: ['GET', 'POST'])]
    public function nuevo(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {

            $token = $request->request->get('_token', '');

            if (
                !is_string($token) ||
                !$this->isCsrfTokenValid('crear_usuario', $token)
            ) {
                throw $this->createAccessDeniedException(
                    'Token de seguridad inválido.'
                );
            }

            $email = mb_strtolower(trim((string) $request->request->get('email', '')));
            $password = (string) $request->request->get('password', '');
            $confirmar = (string) $request->request->get('confirmar_password', '');
            $rol = (string) $request->request->get('rol', 'ROLE_USER');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('danger', 'Ingresá un correo electrónico válido.');

                return $this->redirectToRoute('app_usuarios_nuevo');
            }

            if ($userRepository->findOneBy(['email' => $email])) {
                $this->addFlash('danger', 'Ya existe un usuario con ese correo.');

                return $this->redirectToRoute('app_usuarios_nuevo');
            }

            if (strlen($password) < 12) {
                $this->addFlash('danger', 'La contraseña debe tener al menos 12 caracteres.');

                return $this->redirectToRoute('app_usuarios_nuevo');
            }

            if ($password !== $confirmar) {
                $this->addFlash('danger', 'Las contraseñas no coinciden.');

                return $this->redirectToRoute('app_usuarios_nuevo');
            }

            if (!in_array($rol, ['ROLE_USER', 'ROLE_ADMIN'], true)) {
                $this->addFlash('danger', 'Seleccioná un rol válido.');

                return $this->redirectToRoute('app_usuarios_nuevo');
            }

            $usuario = new User();

            $usuario->setEmail($email);
            $usuario->setRoles([$rol]);

            $hash = $passwordHasher->hashPassword($usuario, $password);

            $usuario->setPassword($hash);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'El usuario fue creado correctamente.'
            );

            return $this->redirectToRoute('app_usuarios');
        }

        return $this->render('usuario/nuevo.html.twig');
    }
}
