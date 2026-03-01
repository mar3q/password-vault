<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Console;

use App\Identity\Application\Command\RegisterUser\RegisterUserCommand;
use App\Identity\Application\Command\RegisterUser\RegisterUserHandler;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create a new user account',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly RegisterUserHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email address')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var string $username */
        $username = $input->getArgument('username');
        /** @var string $password */
        $password = $input->getArgument('password');

        try {
            $user = ($this->handler)(new RegisterUserCommand($email, $username, $password));
        } catch (EmailAlreadyTakenException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('User created with ID: %s', $user->id));

        return Command::SUCCESS;
    }
}
