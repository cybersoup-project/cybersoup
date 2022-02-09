<?php
/* Creo la conexión a la base de datos. */
class Connection {
    public $db;

    public function __construct()
    {
        require_once("Config.php");
        $this->db = new PDO('mysql:host=' . Config::$db_host . ';dbname=' . Config::$db_name . '', Config::$db_username, Config::$db_password);
        $this->db->exec("set names utf8");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
    }
}