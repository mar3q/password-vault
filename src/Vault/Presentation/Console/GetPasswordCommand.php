<?php

declare(strict_types=1);

namespace App\Vault\Presentation\Console;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Port\UserRepository;
use App\Vault\Application\Query\ListEntries\ListEntriesHandler;
use App\Vault\Application\Query\ListEntries\ListEntriesQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:vault:get-password',
    description: 'Retrieve a decrypted password by entry title',
)]
final class GetPasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ListEntriesHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'User email address')
            ->addOption('title', 't', InputOption::VALUE_REQUIRED, 'Entry title to search for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string|null $email */
        $email = $input->getOption('user');
        /** @var string|null $title */
        $title = $input->getOption('title');

        if ($email === null || $title === null) {
            $io->error('Both --user and --title options are required.');

            return Command::FAILURE;
        }

        $user = $this->userRepository->findByEmail(new Email($email));

        if ($user === null) {
            $io->error(sprintf('User "%s" not found.', $email));

            return Command::FAILURE;
        }

        $entries = ($this->handler)(new ListEntriesQuery($user->id()->value()));

        foreach ($entries as $entry) {
            if (mb_strtolower($entry->title) === mb_strtolower($title)) {
                $output->write($entry->password);

                return Command::SUCCESS;
            }
        }

        $io->error(sprintf('No entry found with title "%s".', $title));

        return Command::FAILURE;
    }
}
