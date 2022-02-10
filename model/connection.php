<?php
/* Creo la conexión a la base de datos. */
class Connection {
    public $db;

    public function __construct() {
        require_once("Config.php");
        $config = Config::getConfigObject();
        try {   
            $this->db = new PDO('mysql:host=' . $config->getEnvValue("DB_HOST") . ';dbname=' . $config->getEnvValue("DB_NAME") . '', $config->getEnvValue("DB_USERNAME"), $config->getEnvValue("DB_PASSWORD"));
            $this->db->exec("set names utf8");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(Exception $e) {
            header("Location: 500.php");
        }
    }
}