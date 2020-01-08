<?php

declare(strict_types=1);

namespace App\Fixtures;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class LoadCommand extends Command
{
    /** @var Fixture[] */
    protected $fixtures;

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $output->writeln('Dropping database <info>visialweb</info>...');
        $process = new Process(['php', 'bin/console', 'doctrine:database:drop', '-n', '--force']);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output->writeln('Creating database <info>visialweb</info>...');
        $process = new Process(['php', 'bin/console', 'doctrine:database:create', '-n']);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output->writeln('Running migrations for <info>visialweb</info>...');
        $process = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '-n']);
        $process->setTimeout(3 * 60);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        foreach ($this->fixtures as $fixture) {
            $output->writeln('Running <info>' . $fixture->getName() . '</info>...');
            $fixture->up();
        }
        $output->writeln('Done!');

        return 0;
    }
}
