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
    name: 'app:vault:export',
    description: 'Export all vault entries for a user as JSON',
)]
final class ExportEntriesCommand extends Command
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
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file path (defaults to stdout)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string|null $email */
        $email = $input->getOption('user');

        if ($email === null) {
            $io->error('The --user option is required.');

            return Command::FAILURE;
        }

        $user = $this->userRepository->findByEmail(new Email($email));

        if ($user === null) {
            $io->error(sprintf('User "%s" not found.', $email));

            return Command::FAILURE;
        }

        $entries = ($this->handler)(new ListEntriesQuery($user->id()->value()));

        $data = array_map(static fn($entry) => [
            'id' => $entry->id,
            'title' => $entry->title,
            'login' => $entry->login,
            'password' => $entry->password,
            'url' => $entry->url,
            'notes' => $entry->notes,
            'createdAt' => $entry->createdAt,
            'updatedAt' => $entry->updatedAt,
        ], $entries);

        $json = json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);

        /** @var string|null $outputPath */
        $outputPath = $input->getOption('output');

        if ($outputPath !== null) {
            file_put_contents($outputPath, $json . "\n");
            $io->success(sprintf('Exported %d entries to %s', count($data), $outputPath));
        } else {
            $output->writeln($json);
        }

        return Command::SUCCESS;
    }
}
