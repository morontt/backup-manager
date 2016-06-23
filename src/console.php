<?php

use BackupManager\Command\DatabaseBackup;
use BackupManager\Command\DropboxAuth;
use BackupManager\Command\FilesBackup;
use BackupManager\Command\Init;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;

$console = new Application('Backup Manager', '0.0.1');

$console->add(new DatabaseBackup());
$console->add(new FilesBackup());
$console->add(new DropboxAuth());
$console->add(new Init());

$dispatcher = new EventDispatcher();
$dispatcher->addListener(ConsoleEvents::COMMAND, 'BackupManager\\Listener\\CommandListener::beforeRun');
$console->setDispatcher($dispatcher);

return $console;
