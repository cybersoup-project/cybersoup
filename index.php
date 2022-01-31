<?php
require("utils/UserSession.php");
$usersession = UserSession::getUserSession();

require_once("controller/Action.php");
require_once("controller/Api.php");
$map = array(
    'index' => array('controller' => 'Action', 'action' => 'index', 'rol' => 0),
    'login' => array('controller' => 'Action', 'action' => 'login', 'rol' => 0),
    'register' => array('controller' => 'Action', 'action' => 'register', 'rol' => 0),
    'logout' => array('controller' => 'Action', 'action' => 'logout', 'rol' => 0),
    'challengerList' => array('controller' => 'Action', 'action' => 'challengerList','rol' => 0),
    'newChallenger' => array('controller' => 'Action', 'action' => 'newChallenger','rol' => 0),
    'play' => array('controller' => 'Action', 'action' => 'play','rol' => 0),
    'adminView' => array('controller' => 'Action', 'action' => 'adminView','rol' => 0),
    'editChallenger' => array('controller' => 'Action', 'action' => 'editChallenger','rol' => 0),
    'profile' => array('controller' => 'Action', 'action' => 'profile', 'rol' => 0),
    'listChallengers' => array('controller' => 'Action', 'action' => 'listChallengers','rol' => 0),
    'create' => array('controller' => 'Action', 'action' => 'create','rol' => 1),
    'edit' => array('controller' => 'Action', 'action' => 'edit','rol' => 0),
    'validateChallenge' => array('controller' => 'Action', 'action' => 'validateChallenge','rol' => 0),
    'ranking' => array('controller' => 'Action', 'action' => 'ranking','rol' => 0),
    'game' => array('controller' => 'Action', 'action' => 'game','rol' => 0),
    'checkWord' => array('controller' => 'Api', 'action' => 'checkWord','rol' => 0),
    'userExists' => array('controller' => 'Api', 'action' => 'userExists','rol' => 0)
);
// Parseo de la ruta
if (isset($_GET['action'])) {
    if (isset($map[$_GET['action']])) {
        $ruta = $_GET['action'];
    } else {
        // No establece el estado como 404, simplemente lo pone como una cabecera, no tiene efecto. https://i.imgur.com/U4DJMIX.png
        //header('Status: 404 Not Found');

        // https://www.php.net/manual/es/function.http-response-code.php
        http_response_code(404);
        include("view/404.php");
        exit;
    }
} else {
    $ruta = 'index';
}

$controlador = $map[$ruta];

if (method_exists($controlador['controller'], $controlador['action'])) {
    // Control de roles.
    $rol = $usersession->getSessionValue("rol");

    if ((is_numeric($rol)) && ($rol >= $controlador['rol'])) {
        // Si el usuario tiene permiso, sigue.
        // (El rol es igual o mayor al rol requerido por el controlador frontal)
        // Un rol mayor significa que tiene mas permisos, asi que, en un futuro se podría hacer por ejemplo:
        /** 
         ** 'rol' = 0 -> Visitante
         ** 'rol' = 1 -> Usuario logeado
         ** 'rol' = 2 -> Usuario moderador
         ** 'rol' = 3 -> Usuario administrador
         */
        call_user_func(array(
            new $controlador['controller'],
            $controlador['action']
        ));
    } else {
        // Sino, 403 (forbidden)
        http_response_code(403);
        include("view/403.php");
    }
} else {
    http_response_code(404);
    include("view/404.php");
}
