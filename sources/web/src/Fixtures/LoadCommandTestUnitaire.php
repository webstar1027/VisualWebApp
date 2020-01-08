<?php

declare(strict_types=1);

namespace App\Fixtures;

use Safe\Exceptions\InfoException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function Safe\ini_set;

/**
 * @throws InfoException
 */
try {
    ini_set('memory_limit', '-1');
} catch (InfoException $exception) {
    // @ignoreException
}

abstract class LoadCommandTestUnitaire extends Command
{
    /** @var Fixture[] */
    protected $fixtures;

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $output->writeln('Dropping database <info>visialweb</info>...');
        $process = new Process(['php', 'bin/console', 'doctrine:database:drop', '-n', '--force']);
        $process->setTimeout(null);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output->writeln('Creating database <info>visialweb</info>...');
        $process = new Process(['php', 'bin/console', 'doctrine:database:create', '-n']);
        $process->setTimeout(null);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $process = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
        $process->setTimeout(null);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        foreach ($this->fixtures as $fixture) {
            $fixture->up();
        }

        return 0;
    }
}
