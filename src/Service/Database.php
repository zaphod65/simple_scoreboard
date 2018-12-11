<?php

namespace Service\Database;
use \PDO;

class DatabaseService {
    private $db;

    /**
     * @var configService The config service, which should provide the static method get_db_config.
     */
    public function __construct($configService) {
        $conf = $configService::get_db_config();

        $host = $conf['HOST'];
        $database = $conf['DATABASE'];
        $username = $conf['USER'];
        $password = $conf['PASS'];

        $connection_string = sprintf("mysql:host=%s;dbname=%s;", $host, $database);
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->db = new \PDO($connection_string, $username, $password, $opt);
    }

    /**
     * Returns the PDO connection object in use.
     */
    public function connection() {
        return $this->db;
    }

    /**
     * Naively performs a query against the database connected.
     *
     * @param query The query string.
     * @param params An array of the parameters to be used with the query given.
     *
     * @throws \PDOException
     */
    public function query($query, $params = null) {
        $s = $this->db->prepare($query);
        $s->execute($params);
        return $s->fetchAll();
    }
}
