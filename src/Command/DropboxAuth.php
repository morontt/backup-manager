<?php

namespace BackupManager\Command;

use BackupManager\Database\Repository;
use Dropbox;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DropboxAuth extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('dropbox:auth')
            ->setDescription('Dropbox command-line authorization')
            ->addArgument('sitename', InputArgument::REQUIRED, 'site name in config')
        ;
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

        $config = $this->config['sites'][$site];

        try {
            $appInfo = Dropbox\AppInfo::loadFromJson([
                'key' => $config['dropbox']['key'],
                'secret' => $config['dropbox']['secret'],
            ]);
        } catch (Dropbox\AppInfoLoadException $ex) {
            $output->writeln('<error> Error: %s </error>', $ex->getMessage());
            exit(1);
        }

        $webAuth = new Dropbox\WebAuthNoRedirect($appInfo, "examples-authorize", "en");
        $authorizeUrl = $webAuth->start();

        $output->writeln(sprintf("\n1. Go to: %s", $authorizeUrl));
        $output->writeln("2. Click <comment>\"Allow\"</comment> (you might have to log in first).");
        $output->writeln("3. Copy the authorization code.\n");

        $dialog = $this->getHelper('question');
        $question = new Question('Enter the authorization code here: ');
        $question->setValidator(function ($answer) {
            if (!trim($answer)) {
                throw new \RuntimeException(
                    'Empty code :('
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);

        $authCode = trim($dialog->ask($input, $output, $question));

        list($accessToken, $userId) = $webAuth->finish($authCode);

        $output->writeln("\nAuthorization complete.");
        $output->writeln(sprintf("User ID: <comment>%s</comment>", $userId));
        $output->writeln(sprintf("Access Token: <comment>%s</comment>", $accessToken));

        $repository = new Repository();
        $repository->saveAccessToken($site, $accessToken);
    }
}
