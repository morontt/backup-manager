<?php

namespace BackupManager\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /**
     * @return array
     */
    public static function getConfig()
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../../config/config.yml'));

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(
            new AppConfiguration(),
            [$config]
        );

        return $processedConfig;
    }
}
