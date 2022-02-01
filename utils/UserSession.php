<?php
/* Maneja la sesión del usuario mediante singleton. */
class UserSession
{
    private static $instance = null;

    private function __construct()
    {
        // TODO: Session config
        session_start();
        $_SESSION['lastvisited']  = time();
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        if (!isset($_SESSION['rol'])) $_SESSION['rol'] = 0;
        if (!isset($_SESSION['iduser'])) $_SESSION['iduser'] = 0;
    }

    public static function getUserSession()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isLoggedIn()
    {
        return !($_SESSION['iduser'] === 0);
    }

    public function getUsername()
    {
        return $_SESSION['username'];
    }

    public function addSessionValue($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function getSessionValue($val){
        return isset($_SESSION[$val]) ? $_SESSION[$val] : null;
    }
}
