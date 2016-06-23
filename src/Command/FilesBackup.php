<?php

namespace BackupManager\Command;

use BackupManager\Database\Repository;
use Dropbox;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class FilesBackup extends BaseCommand
{
    /**
     * @var string|null
     */
    protected $encryptionKey = null;

    /**
     * @var string|null
     */
    protected $encryptionCipher = null;

    /**
     * @var Dropbox\Client
     */
    protected $client;


    protected function configure()
    {
        $this
            ->setName('backup:files')
            ->setDescription('Backup Files')
            ->addArgument('sitename', InputArgument::REQUIRED, 'site name in config');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
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

        $this->client = new Dropbox\Client($token, 'Backup-Manager/0.0.1');

        $config = $this->config['sites'][$site];
        if (!empty($config['encryption']['key'])) {
            $this->encryptionKey = $config['encryption']['key'];
            $this->encryptionCipher = $config['encryption']['cipher'];
        }

        $dirs = [];
        foreach ($config['directories'] as $key => $value) {
            $dirs[$key] = $config['base_path'] . DIRECTORY_SEPARATOR . $value;
        }

        foreach ($dirs as $key => $value) {
            $files = $this->getFileList($value);
            $dirLength = strlen($value);
            foreach ($files as $fileInfo) {
                $file = $fileInfo->getPathname();

                $hash = sha1($file);
                $relativePath = substr($file, $dirLength);

                $sendTime = $repository->getFileUpdatedTime($hash);
                if ($sendTime) {
                    $lastModified = \DateTime::createFromFormat('U', $fileInfo->getCTime());
                    if ($lastModified > $sendTime) {
                        $this->sendFile($file, $relativePath, $key);
                        $repository->updateFileUpdatedTime($hash);
                        $output->writeln(sprintf('[%s] %s', (new \DateTime())->format('Y-m-d H:i:s'), $file));
                    }
                } else {
                    $this->sendFile($file, $relativePath, $key);
                    $repository->saveFileUpdatedTime($hash);
                    $output->writeln(sprintf('[%s] %s', (new \DateTime())->format('Y-m-d H:i:s'), $file));
                }
            }
        }
    }

    /**
     * @param string $file
     * @param string $relativePath
     * @param string $dirPrefix
     */
    protected function sendFile($file, $relativePath, $dirPrefix)
    {
        if ($this->encryptionKey) {
            $newFile = realpath(__DIR__ . '/../../tmp') . '/' . pathinfo($file, PATHINFO_BASENAME) . '.enc';
            $process = new Process(
                sprintf(
                    'openssl enc -e -%s -k %s -in %s -out %s',
                    $this->encryptionCipher,
                    $this->encryptionKey,
                    $file,
                    $newFile
                )
            );
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            $f = fopen($newFile, 'rb');
            $this->client->uploadFile('/' . $dirPrefix . $relativePath . '.enc', Dropbox\WriteMode::force(), $f);
            fclose($f);
            unlink($newFile);
        } else {
            $f = fopen($file, 'rb');
            $this->client->uploadFile('/' . $dirPrefix . $relativePath, Dropbox\WriteMode::force(), $f);
            fclose($f);
        }
    }

    /**
     * @param string $directory
     * @return \SplFileInfo[]
     */
    protected function getFileList($directory)
    {
        if (!is_dir($directory)) {
            return [];
        }

        $result = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $result[] = $file;
            }
        }

        return $result;
    }
}
