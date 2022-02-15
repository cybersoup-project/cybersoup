<?php

// TODO meter los intentos en la base de datos

class Api
{
    public function error_handler($e)
    {
        echo "Excepción no capturada: " , $e->getMessage(), "\n";
    }

    public function __construct()
    {
        set_exception_handler(function($e) {
            $this->error_handler($e);
        });
    }

    public function checkWord()
    {
        header("Content-Type: application/json; charset=UTF-8");
        require_once("model/Challenge.php");
        require_once("model/Attempts.php");
        require_once("model/Usuario.php");

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
                            for ($i = 0; $i < mb_strlen($valores['solution']); $i += 1) {
                                $respuesta['word'][] = "ok";
                            }

                            // * Winner winner chicken dinner

                            // $attempt->setAttempt($userid, $valores['id'], $valores['palabra']);
                            // We don't store the win as an attempt but we add one to the attempt count
                            // in the winner table.
                            $usr= new Usuario();
                            $chl->updateChallengesPlay($chlrow['times_played'] + 1, $valores['id']);
                            $chl->updateChallengesWins($chlrow['times_success'] + 1, $valores['id']);
                            $attempt->setWinner(count($countattempts) + 1, $userid, $valores['id']);
                            $oldScore=$usr->getScore($userid);
                            $winScore=$chl->getwinAttempts($userid,$valores['id']);
                            /* die(print_r($oldScore)); */
                            /* $newScore=$oldScore['score']+($winScore['difficulty']/($winScore['max_attempts']*200))/$winScore['attempt']; */
                            $newScore = $winScore['difficulty'] / $winScore['max_attempts'] * 200;

                            /* var_dump($winScore);
                            die(); */

                            $newScore = $newScore / $winScore['attempt'];
                            
                            $newScore = $newScore + $oldScore['score'];
                            /* die(var_dump($newScore)); */
                            $usr->setScore($userid,$newScore);
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
                    $chl->updateChallengesPlay($chlrow['times_played'] + 1, $valores['id']);
                } else {
                    if ($winner) {
                        $respuesta['status'] = "success";
                    }
                    // * user is already a winner or a loser
                }
            } else {
                // * length between solution and attempt is not equal. Disregard the request
            }
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($respuesta);
    }

    public function userExists()
    {
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

    public function getHealth()
    {
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

    public function getStats()
    {
        require_once("model/Attempts.php");
        require_once("model/Usuario.php");

        $usersession = UserSession::getUserSession();

        $valores = array(
            "iduser" => $usersession->getSessionValue("iduser")
        );

        if ($valores['iduser'] == 0) {
            die("{\"status\":\"not logged in\"}");
        }

        $attempts = new Attempts();
        $wins = $attempts->getUserWins($valores['iduser']);
        $fails = $attempts->getUserFails($valores['iduser']);

        header("Content-Type: application/json; charset=UTF-8");
        $respuesta = array("wins" => $wins['count(*)'], "fails" => $fails['count(*)']);
        echo json_encode($respuesta);
    }

    public function showAttempts()
    {
        require("model/Attempts.php");
        require("model/Challenge.php");
        require_once("model/Usuario.php");

        $usersession = UserSession::getUserSession();
        $userid = $usersession->getSessionValue("iduser");

        $valores = array(
            "idchallenge" => $_GET['id'] ?? 0, //aqui puede dar problemas lo del dailyword
            "iduser" => $userid
        );

        if ($userid == 0 || $valores['idchallenge'] == 0) {
            die("{\"status\":\"not logged in\"}");
        }

        $respuesta = array(
            "status" => "ok",
            "word" => array(),
            "past" => array()
        );

        $att = new Attempts();
        $chl = new Challenge();

        $challenge = $chl->getChallengeById($valores['idchallenge']);
        $attempts = $att->getUserAttemptsAtChallenge($valores['iduser'], $valores['idchallenge']);
        $catt = count($attempts);
        //valores para la funcion coloreame
        //die(print_r($challenge));
        $sol = mb_str_split(mb_strtoupper($challenge['solution']));

        foreach ($attempts as $key => $value) {
            $arr = array();
            $intento = mb_str_split(mb_strtoupper($value['solution']));
            foreach ($intento as $i => $letra) {
                if(in_array($letra, $sol)) {
                    if($letra == $sol[$i]) {
                        $arr[] = "ok";
                    } else {
                        $arr[] = "exists";
                    }
                } else {
                    $arr[] = "null";
                }
            };

            $respuesta['word'][] = $arr;
            $respuesta['past'][] = $intento;
        }
        
        //die(print_r($valores));

        header("Content-Type: application/json; charset=UTF-8");
        //$respuesta = array("attempts" => $attempts, "contatt" => $catt, 'respuesta' => $respuesta);
        echo json_encode($respuesta);
    }
}
