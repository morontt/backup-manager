<?php

namespace BackupManager\Command;

use BackupManager\Database\Migrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Application initializing')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configPath = realpath(__DIR__ . '/../../config');

        if (!file_exists($configPath . '/config.yml')) {
            copy($configPath . '/config.sample.yml', $configPath . '/config.yml');
        }

        $migrator = new Migrator();
        $queries = $migrator->migrate();
        foreach ($queries as $query) {
            $output->writeln($query);
        }
    }
}
