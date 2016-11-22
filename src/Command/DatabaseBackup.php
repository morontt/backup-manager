<?php

namespace BackupManager\Command;

use BackupManager\Database\Repository;
use Dropbox;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DatabaseBackup extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('backup:database')
            ->setDescription('Backup Database')
            ->addArgument('sitename', InputArgument::REQUIRED, 'site name in config')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $site = $input->getArgument('sitename');
        if (empty($this->config['sites'][$site])) {
            $output->writeln(sprintf('<error> Undefined site: %s </error>', $site));
            exit(1);
        }

        $repository = new Repository();
        $token = $repository->getAccessToken($site);
        if (!$token) {
            $output->writeln(sprintf('<error> Dropbox token not found </error>', $site));
            exit(1);
        }

        $config = $this->config['sites'][$site];
        $path = $this->getDumpPath($site);

        $process = new Process(
            sprintf(
                'mysqldump -h %s -u %s --password=%s %s | bzip2 > %s',
                $config['database']['host'],
                $config['database']['user'],
                $config['database']['password'],
                $config['database']['dbname'],
                $path
            )
        );
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        if (!empty($config['encryption']['key'])) {
            $path = $this->encrypt($path, $config);
        }

        $dbxClient = new Dropbox\Client($token, 'Backup-Manager/0.0.1');

        $f = fopen($path, 'rb');
        $dbxClient->uploadFile('/dumps/' . pathinfo($path, PATHINFO_BASENAME), Dropbox\WriteMode::add(), $f);
        fclose($f);
        unlink($path);

        $output->writeln(
            sprintf('[%s] %s', (new \DateTime())->format('Y-m-d H:i:s'), pathinfo($path, PATHINFO_BASENAME))
        );
    }

    /**
     * @param string $site
     *
     * @return string
     */
    protected function getDumpPath($site)
    {
        return sprintf(
            '%s/%s_%s.sql.bz2',
            realpath(__DIR__ . '/../../tmp'),
            $site,
            (new \DateTime())->format('YmdHis')
        );
    }

    /**
     * @param string $path
     * @param array $config
     *
     * @return string
     */
    protected function encrypt($path, array $config)
    {
        $newPath = $path . '.enc';
        $process = new Process(
            sprintf(
                'openssl enc -e -%s -k %s -in %s -out %s',
                $config['encryption']['cipher'],
                $config['encryption']['key'],
                $path,
                $newPath
            )
        );
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        unlink($path);

        return $newPath;
    }
}
