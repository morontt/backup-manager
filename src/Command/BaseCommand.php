<?php

namespace BackupManager\Command;

use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     */
    public function setApplicationConfig(array $config)
    {
        $this->config = $config;
    }
}
