<?php

namespace BackupManager\Database;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class Connection
{
    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function getConnection()
    {
        return DriverManager::getConnection(
            [
                'path' => realpath(__DIR__ . '/../../') . '/backuper.db3',
                'driver' => 'pdo_sqlite',
            ],
            new Configuration()
        );
    }
}
