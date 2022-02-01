<?php

// TODO meter los intentos en la base de datos

class Api {

    public function checkWord() {
        header("Content-Type: application/json; charset=UTF-8");

        $usersession = UserSession::getUserSession();
        $userid = $usersession->getSessionValue("iduser");

        if($userid == 0) {
            die("{\"status\":\"not logged in\"}");
        }

        $valores = array(
            "palabra" => mb_strtoupper($_GET['palabra']) ?? "",
            "id" => $_GET['id'] ?? ""
        );

        $respuesta = array(
            "status" => "",
            "word" => array()
        );

        require_once("model/Challenge.php");

        $chl = new Challenge();

        $chlrow = $chl->getChallengeById($valores['id']);

        // comprueba si existe el id y que las palabras tengan la misma longitud
        if ($chlrow && (mb_strlen($valores['palabra']) == mb_strlen($chlrow['solution']))) {
            require_once("model/Attempts.php");

            $attempt = new Attempts();
            
            $countattempts = $attempt->getUserAttemptsAtChallenge($userid, $valores['id']);

            $loser = $attempt->isUserLoserAtChallenge($userid, $valores['id']);
            $winner = $attempt->isUserWinnerAtChallenge($userid, $valores['id']);

            //$respuesta['test'] = count($countattempts);

            if (($chlrow['max_attempts'] <= count($countattempts)) && (!$loser)) {
                $attempt->setLoser($userid, $valores['id']);   
            }

            // ! comprobar intentos restantes
            if (($chlrow['max_attempts'] > count($countattempts)) && (!$loser) && (!$winner)) { // Si aún tiene intentos

                $sol = mb_strtoupper($chlrow['solution']);

                // Si la palabra es igual
                if ($valores['palabra'] === $sol) {
                    // Construimos la respuesta ok
                    $respuesta['status'] = "success";
                    for ($i = 0; $i < mb_strlen($sol); $i += 1) {
                        $respuesta['word'][] = "ok";
                    }

                    // Ganador
                    $attempt->setAttempt($userid, $valores['id'], $valores['palabra']);
                    $attempt->setWinner(count($countattempts), $userid, $valores['id']);
                } else { // Si la palabra no es igual
                    $intento = mb_str_split($valores['palabra']);
                    $sol = mb_str_split($sol);
                    for ($i = 0; $i < count($intento); $i += 1) {
                        // Si la letra está en la solución
                        if (in_array($intento[$i], $sol)) {
                            // se comprueba si está en la misma posición
                            if ($intento[$i] == $sol[$i]) {
                                $respuesta['word'][] = "ok";
                            } else {
                                // no está en la misma posición
                                $respuesta['word'][] = "exists";
                            }
                        } else {
                            // no existe la letra en la posición
                            $respuesta['word'][] = "null";
                        }
                    }
                    $respuesta['status'] = "incomplete";
                    $attempt->setAttempt($userid, $valores['id'], $valores['palabra']);
                }
            } else {
                // No tiene intentos o es ganador

                if($winner) {
                    $respuesta['status'] = "success";
                } else {
                    $respuesta['status'] = "failed";
                }
            }
        } else {
            // ! ID no existe o palabra no igual en longitud
            $respuesta['status'] = "err";
            // JS won't do anything
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($respuesta);
    }

    public function userExists() {
        require("model/Usuario.php");
        
        $valores = array(
            "usuario" => $_GET['user'] ?? ""
        );

        $respuesta = array(
            'exists' => false
        );

        $user = new Usuario();

        if($user->getUserByUsername($valores['usuario'])) {
            $respuesta['exists'] = !$respuesta['exists'];
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($respuesta);
    }

    public function getHealth() {
        require("model/Attempts.php");
        require("model/Challenge.php");

        header("Content-Type: application/json; charset=UTF-8");

        $usersession = UserSession::getUserSession();
        
        $valores = array(
            "idchallenge" => $_GET['id'] ?? "",
            "idusuario" => $usersession->getSessionValue("iduser")
        );

        if($valores['idusuario'] == 0) {
            die("{\"status\":\"not logged in\"}");
        }

        $respuesta = array();

        $attempt = new Attempts();
        $chl = new Challenge();

        $challenge = $chl->getChallengeById($valores['idchallenge']);

        $attempts = $attempt->getUserAttemptsAtChallenge($valores['idusuario'], $valores['idchallenge']);

        if($challenge) {
            $respuesta['health'] = $challenge['max_attempts'] - count($attempts);
        } else {
            $respuesta['health'] = 0;
        }
        echo json_encode($respuesta);

    }

    public function getStats() {
        require_once("model/Attempts.php");
        require_once("model/Usuario.php");

        $usersession = UserSession::getUserSession();
        
        $valores = array(
            "iduser" => $usersession->getSessionValue("iduser")
        );

        $attempts = new Attempts();
        $wins= $attempts -> getUserWins($valores['iduser']);
        $fails= $attempts -> getUserFails($valores['iduser']);

        header("Content-Type: application/json; charset=UTF-8");
        $respuesta=array("wins" => $wins['count(*)'], "fails" => $fails['count(*)']);
        echo json_encode($respuesta);

}
}
