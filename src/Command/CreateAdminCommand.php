<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crear un usuario administrador para Sterenfix'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('Crear administrador de Sterenfix');

        $email = $io->ask('Correo electrónico del administrador');

        if (
            !is_string($email) ||
            !filter_var(trim($email), FILTER_VALIDATE_EMAIL)
        ) {
            $io->error('Ingresá un correo electrónico válido.');
            return Command::FAILURE;
        }

        $email = mb_strtolower(trim($email));

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error('Ya existe un usuario con ese correo electrónico.');
            return Command::FAILURE;
        }

        $password = $io->askHidden('Contraseña del administrador');

        if (!is_string($password) || strlen($password) < 12) {
            $io->error('La contraseña debe tener al menos 12 caracteres.');
            return Command::FAILURE;
        }

        $confirmacion = $io->askHidden('Confirmá la contraseña');

        if ($password !== $confirmacion) {
            $io->error('Las contraseñas no coinciden.');
            return Command::FAILURE;
        }

        $usuario = new User();

        $usuario->setEmail($email);
        $usuario->setRoles(['ROLE_ADMIN']);

        $hash = $this->passwordHasher->hashPassword(
            $usuario,
            $password
        );

        $usuario->setPassword($hash);

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        $io->success('Administrador creado correctamente.');

        $io->writeln('Correo: ' . $usuario->getEmail());
        $io->writeln('Rol: ROLE_ADMIN');

        return Command::SUCCESS;
    }
}
