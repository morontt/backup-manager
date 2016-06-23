<?php

namespace BackupManager\Database;

use PDO;

class Repository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;


    public function __construct()
    {
        $this->db = Connection::getConnection();
    }

    /**
     * @param string $site
     * @param string $accessToken
     */
    public function saveAccessToken($site, $accessToken)
    {
        $stmt = $this->db->prepare('SELECT id FROM backuper_token WHERE site = :site');
        $stmt->bindValue('site', $site, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $this->db->update(
                'backuper_token',
                [
                    'site' => $site,
                    'token' => $accessToken,
                ],
                [
                    'id' => (int)$result['id'],
                ]
            );
        } else {
            $this->db->insert(
                'backuper_token',
                [
                    'site' => $site,
                    'token' => $accessToken,
                ]
            );
        }
    }

    /**
     * @param string $site
     * @return string|null
     */
    public function getAccessToken($site)
    {
        $stmt = $this->db->prepare('SELECT token FROM backuper_token WHERE site = :site');
        $stmt->bindValue('site', $site, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result ? $result['token'] : null;
    }

    /**
     * @param string $hash
     * @return \DateTime|null
     */
    public function getFileUpdatedTime($hash)
    {
        $stmt = $this->db->prepare('SELECT updated_at FROM backuper_file WHERE file_hash = :hash');
        $stmt->bindValue('hash', $hash, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result ? \DateTime::createFromFormat('Y-m-d H:i:s', $result['updated_at']) : null;
    }

    /**
     * @param string $hash
     */
    public function saveFileUpdatedTime($hash)
    {
        $this->db->insert(
            'backuper_file',
            [
                'file_hash' => $hash,
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * @param string $hash
     */
    public function updateFileUpdatedTime($hash)
    {
        $this->db->update(
            'backuper_file',
            [
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
            [
                'file_hash' => $hash,
            ]
        );
    }
}
