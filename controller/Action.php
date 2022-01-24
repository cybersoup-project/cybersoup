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
        $this->twig->addGlobal('usersession', UserSession::getUserSession());
    }

    public function index() {
        /* Si la acción es index, lo incluyo */
        echo $this->twig->render('index.html', array('nombre' => 'george'));
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
            echo $this->twig->render('Form_LogIn.html');
        }
        /* $usersession = UserSession::getUserSession();
        $usersession->addSessionValue("username", $valores['usuario']);
        $usersession->addSessionValue("userid", $user->getUserId($valores['usuario'])); */
    }

    public function register() {
        /* Mas o menos lo mismo que el login, pero registrando al usuario. */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require("utils/validation.php");
            require("utils/classValidar.php");
            require("model/Usuario.php");
            $user = new Usuario();
            $validation = new Validacion();

            $errores = array();
            $valores = array(
                "Full Name" => $_POST['full_name'] ?? '',
                "Username" => $_POST['username'] ?? '',
                "Password" => $_POST['password'] ?? '',
                "Repeat Password" => $_POST['repeat_password'] ?? '',
                "Email" => $_POST['email'] ?? '',
                "Terms and Conditions" => $_POST['tos'] ?? '',
            );

            $regla = array(
                array(
                    'name' => 'Full Name',
                    'regla' => 'name'
                ),
                array(
                    'name' => 'Username',
                    'regla' => 'minmax'
                ),
                array(
                    'name' => 'Password',
                    'regla' => 'password'
                ),
                array(
                    'name' => 'Email',
                    'regla' => 'email'
                ),
                array(
                    'name' => 'Terms and Conditions',
                    'regla' => 'checked'
                )
            );

            $validaciones = $validation->rules($regla, $valores)->mensaje ?? array();

            // Comprobar que las dos contraseñas sean iguales
            if ($valores['Password'] !== $valores['Repeat Password']) {
                $validaciones['Password'][] = "Passwords must match";
            }

            // Comprobar que el nombre de usuario no exista ya.
            $res = $user->getUserByUsername($valores['Username']);
            if ($res) {
                $validaciones['Username'][] = "El nombre de usuario ya está en uso, escoje otro.";
            }

            // Comprobar que el email no exista ya.
            $res = $user->getUserByEmail($valores['Email']);
            if ($res) {
                $errores['Email'][] = "El email ya está en uso, escoje otro.";
            }

            /* foreach ($validaciones as $key => $value) {
                echo $key . " >> ";
                foreach ($value as $k => $val) {
                    echo $val . "<br>";
                }
            } */

            /* print_r($validaciones); */

            if (count($validaciones) === 0) {
                /* Registrar usuario */
                $usersession = UserSession::getUserSession();

                $rol = 1; // Rol a 1 (Usuario registrado)
                $activo = 0; // Hace falta validar la cuenta por email;

                $user->setUsuario($valores['Username'], getHash($valores['Password']), $valores['Full Name'], $valores['Email'], $activo, $rol);

                $usersession->addSessionValue("username", $valores['Username']);
                $usersession->addSessionValue("userid", $user->getUserId($valores['Username']));
                $usersession->addSessionValue("rol", $rol);

                $mensaje = array("Tu usuario ha sido registrado.");
                echo $this->twig->render('profile.html', array('mensajes' => $mensaje));
                //header("Location: index.php");
            } else {
                echo $this->twig->render('Form_Registro.html', array('errores' => $validaciones));
            }
        } else {
            echo $this->twig->render('Form_Registro.html');
        }
    }

    function logout() {
        /* Salir de la sesión. Borro $_SESSION y la destruyo. */
        $_SESSION = array();
        session_destroy();
        header("Location: index.php");
    }

    function profile() {
        echo $this->twig->render('profile.html');
    }

    function listChallengers() {
        require("model/Challenge.php");
        $challenge = new Challenge();
        $challenges = $challenge->getAllChallenges();
        echo $this->twig->render('ChallengesList.html', array("objectlist" => $challenges));
    }

    function createEdit() {
        echo $this->twig->render('Form_crear-editarChallenge.html');
    }

    function validateChallenge() {
        echo $this->twig->render('Form_validarChallenge.html');
    }
}
