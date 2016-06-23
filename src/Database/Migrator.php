<?php

namespace BackupManager\Database;

use Doctrine\DBAL\Schema\Schema;

class Migrator
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;


    function __construct()
    {
        $this->db = Connection::getConnection();
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function migrate()
    {
        $sm = $this->db->getSchemaManager();

        $fromSchema = $sm->createSchema();
        $toSchema = $this->buildSchema();

        $queries = $fromSchema->getMigrateToSql($toSchema, $this->db->getDatabasePlatform());
        foreach ($queries as $query) {
            $this->db->exec($query);
        }

        return $queries;
    }

    /**
     * @return Schema
     */
    protected function buildSchema()
    {
        $schema = new Schema();

        $tokenTable = $schema->createTable('backuper_token');
        $tokenTable->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true]);
        $tokenTable->addColumn('site', 'string', ['length' => 64]);
        $tokenTable->addColumn('token', 'string', ['length' => 255]);
        $tokenTable->setPrimaryKey(['id']);
        $tokenTable->addUniqueIndex(['site']);

        $fileTable = $schema->createTable('backuper_file');
        $fileTable->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true]);
        $fileTable->addColumn('file_hash', 'string', ['length' => 40]);
        $fileTable->addColumn('updated_at', 'datetime');
        $fileTable->addUniqueIndex(['file_hash']);
        $fileTable->setPrimaryKey(['id']);

        return $schema;
    }
}
