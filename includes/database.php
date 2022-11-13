<?php
class Database {
    protected $pdo;


    public function __construct() {
        /* Get settings from settings file */
        $settings = include("settings.php");

        /* Connect to the database */
        $dsn = "mysql:host=$settings->mysql_host;dbname=$settings->mysql_db;charset=$settings->mysql_charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $settings->mysql_user, $settings->mysql_pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
}
