<?php
// TODO:
// Al iniciar o registrase, hace falta refrescar para ver el cambio de las opciones del menú de arriba.
// Login y registro usando la clase de validación.

require 'vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Action {
    public $loader;
    public $twig;

    public function __construct() {
        $this->loader = new FilesystemLoader('view/');
        $this->twig = new Environment($this->loader);
        
    }

    public function index() {
        /* Si la acción es index, lo incluyo */
        echo $this->twig->render('index.html', array('the' => 'variables', 'go' => 'here'));
        //include("view/index.php");
    }

    public function login() {
        $errores = array();
        /* Si la petición es POST, significa que es un intento de login. */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require("model/Usuario.php");
            $usuario = new Usuario();
            $valores = array(
                "user" => $_POST['user'],
                "pw" => $_POST['pw']
            );

            /* Busco en la base de datos a ver si hay un usuario existente */
            $r = $usuario->getUserByUsername($valores['user']);

            if ($r) {
                /* Si existe el usuario, compruebo la contraseña */
                if (password_verify($valores['pw'], $r['pass'])) {
                    /* require("utils/UserSession.php"); */
                    $usersession = UserSession::getUserSession();
                    $usersession->addSessionValue("userid", $r['id']);
                    $usersession->addSessionValue("username", $r['usuario']);
                    $usersession->addSessionValue("rol", 1);
                    $mensaje = array("Has iniciado sesión.");
                    require("model/Articulo.php");
                    include("view/articulos.php");
                    /* Si la contraseña es correcta, se inicia sesión y se muestran artículos. */
                } else {
                    /* Contraseña errónea */
                    $errores[] = "El usuario y/o la contraseña no son válidos.";
                    include("view/login.php");
                }
            } else {
                // Usuario no válido (no existe en la base de datos). Le pongo el error e incluyo la view del login.
                $errores[] = "El usuario y/o la contraseña no son válidos.";
                include("view/login.php");
            }
        } else {
            /* Muestro el formulario de registro */
            include("view/login.php");
        }
        /* $usersession = UserSession::getUserSession();
        $usersession->addSessionValue("username", $valores['usuario']);
        $usersession->addSessionValue("userid", $user->getUserId($valores['usuario'])); */
    }

    public function register() {
        /* Mas o menos lo mismo que el login, pero registrando al usuario. */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require("utils/validation.php");
            $errores = array();
            $valores = array(
                "usuario" => $_POST['user'],
                "nombre" => $_POST['nombre'],
                "pw" => $_POST['pw'],
                "pw2" => $_POST['pw2'],
                "email" => $_POST['email'],
                "genero" => $_POST['g'],
                "nacimiento" => $_POST['nacimiento'],
            );

            /* Validaciones */
            if (!isValidUsername($valores['usuario'])) {
                $errores[] = "El usuario solo puede tener carácteres alfanumericos y tener un mínimo de 4 y un máximo de 24 carácteres.";
            }

            if (!isValidUsername($valores['nombre'])) {
                $errores[] = "El nombre solo puede tener carácteres alfanumericos y tener un mínimo de 4 y un máximo de 24 carácteres.";
            }

            if (!isSameString($valores['pw'], $valores['pw2'])) {
                $errores[] = "Las contraseñas no coinciden.";
            } else if ($valores['pw'] === "") {
                $errores[] = "Las contraseñas no pueden estar vacías.";
            }

            if (!isValidEmail($valores['email'])) {
                $errores[] = "El email no es válido.";
            }

            if (!esGeneroValido($valores['genero'])) {
                $errores[] = "El género no es válido.";
            }

            if (!isValidDate($valores['nacimiento'])) {
                $errores[] = "La fecha no es válida";
            }

            if (count($errores) === 0) {
                require("model/Usuario.php");
                $user = new Usuario();
                /* Comprobar si email o usuario está en uso. */
                /* Estaría mejor haberlo comprobado arriba, ya que esta parte del código queda un poco rara. */
                $res = $user->getUserByUsername($valores['usuario']);
                if ($res) {
                    $errores[] = "El nombre de usuario ya está en uso, escoje otro.";
                }
                $res = $user->getUserByEmail($valores['email']);
                if ($res) {
                    $errores[] = "El email ya está en uso, escoje otro.";
                }
                if (count($errores) !== 0) {
                    /* Abortar registro y mostrar errores en el formulario si lo anterior se cumple. */
                    include("view/register.php");
                    exit;
                }
                /* Registrar usuario */
                $usersession = UserSession::getUserSession();
                $usersession->addSessionValue("username", $valores['usuario']);
                $user->setUsuario($valores['usuario'], getHash($valores['pw']), $valores['email'], $valores['nacimiento'], $valores['genero'], $valores['nombre']);
                $usersession->addSessionValue("userid", $user->getUserId($valores['usuario']));
                $usersession->addSessionValue("rol", 1);
                $mensaje = array("Tu usuario ha sido registrado.");
                require("model/Articulo.php");
                include("view/articulos.php");
                //header("Location: index.php");
            } else {
                include("view/register.php");
            }
        } else {
            include("view/register.php");
        }
    }

    function logout() {
        /* Salir de la sesión. Borro $_SESSION y la destruyo. */
        $_SESSION = array();
        session_destroy();
        header("Location: index.php");
    }
}
