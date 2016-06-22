<?php

namespace BackupManager\Database;

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
        $stmt->bindValue('site', $site, \PDO::PARAM_STR);
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
        $stmt->bindValue('site', $site, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result ? $result['token'] : null;
    }
}
