<?php

namespace BackupManager\Listener;

use BackupManager\Config\Config;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class CommandListener
{
    /**
     * @param ConsoleCommandEvent $event
     */
    public static function beforeRun(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        if (method_exists($command, 'setApplicationConfig')) {
            $config = Config::getConfig();
            $command->setApplicationConfig($config);
        }
    }
}
