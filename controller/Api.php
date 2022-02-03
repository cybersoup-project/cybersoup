<?php

// TODO meter los intentos en la base de datos

class Api {

    public function checkWord() {
        header("Content-Type: application/json; charset=UTF-8");
        require_once("model/Challenge.php");
        require_once("model/Attempts.php");

        $usersession = UserSession::getUserSession();
        $userid = $usersession->getSessionValue("iduser");

        // Api open for unregistered users for functionality.
        if ($userid == 0) {
            die("{\"status\":\"not logged in\"}");
        }

        // ! ------------------------------------------------------------------
        // ! ------------------------------------------------------------------

        $valores = array(
            "palabra" => mb_strtoupper($_GET['palabra']) ?? "",
            "palabralen" => mb_strlen($_GET['palabra']) ?? 0,
            "id" => $_GET['id'] ?? 0
        );

        $respuesta = array(
            "status" => "",
            "word" => array()
        );

        $chl = new Challenge();

        $chlrow = $chl->getChallengeById($valores['id']);

        $valores['solutionlen'] = mb_strlen($chlrow['solution']);
        $valores['solution'] = mb_strtoupper($chlrow['solution']);

        $attempt = new Attempts();
        $loser = $attempt->isUserLoserAtChallenge($userid, $valores['id']);
        $winner = $attempt->isUserWinnerAtChallenge($userid, $valores['id']);

        // ! ------------------------------------------------------------------
        // ! ------------------------------------------------------------------

        // * challenge exists in database
        if ($chlrow) {
            // * solution word length and attempt word length is the same
            if ($valores['palabralen'] === $valores['solutionlen']) {

                if ((!$winner) && (!$loser)) { // * not winner, not loser
                    
                    $countattempts = $attempt->getUserAttemptsAtChallenge($userid, $valores['id']);
                    if ($chlrow['max_attempts'] > count($countattempts)) { // * still has attempts left

                        if ($valores['palabra'] === $valores['solution']) { // * word is equal = win

                            $respuesta['status'] = "success";
                            for ($i = 0; $i < mb_strlen($sol); $i += 1) {
                                $respuesta['word'][] = "ok";
                            }

                            // * Winner winner chicken dinner
                            $attempt->setAttempt($userid, $valores['id'], $valores['palabra']);
                            $attempt->setWinner(count($countattempts), $userid, $valores['id']);
                        } else { // * word is not equal, set attempt and response.
                            $intento = mb_str_split($valores['palabra']);
                            $sol = mb_str_split($valores['solution']);
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
                    } else { // * no attempts left, set loser
                        $attempt->setLoser($userid, $valores['id']);
                    }
                } else {
                    // * user is already a winner or a loser
                }
            } else {
                // * length between solution and attempt is not equal. Disregard the request
            }
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

        if ($user->getUserByUsername($valores['usuario'])) {
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

        if ($valores['idusuario'] == 0) {
            die("{\"status\":\"not logged in\"}");
        }

        $respuesta = array();

        $attempt = new Attempts();
        $chl = new Challenge();

        $challenge = $chl->getChallengeById($valores['idchallenge']);

        $attempts = $attempt->getUserAttemptsAtChallenge($valores['idusuario'], $valores['idchallenge']);

        if ($challenge) {
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
        $wins = $attempts->getUserWins($valores['iduser']);
        $fails = $attempts->getUserFails($valores['iduser']);

        header("Content-Type: application/json; charset=UTF-8");
        $respuesta = array("wins" => $wins['count(*)'], "fails" => $fails['count(*)']);
        echo json_encode($respuesta);
    }
}
