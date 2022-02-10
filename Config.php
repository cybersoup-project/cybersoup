<?php

class Config {

    /* public static $db_host          = "localhost";
    public static $db_name          = "cybersoup";
    public static $db_username      = "root";
    public static $db_password      = "";

    public static $smtp_host        = "";
    public static $smtp_username    = "";
    public static $smtp_password    = ""; */

    private static $instance = null;

    private function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }

    public static function getConfigObject()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getEnvValue($value) {
        return $_ENV[$value];
    }

}



?>