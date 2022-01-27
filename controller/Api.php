<?php

// TODO meter los intentos en la base de datos

class Api {

    public function checkWord() {
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
        if($chlrow && (mb_strlen($valores['palabra']) == mb_strlen($chlrow['solution']))) {
            $sol = mb_strtoupper($chlrow['solution']);
            
            // Si la palabra es igual
            if($valores['palabra'] === $sol) {

                // Construimos la respuesta ok
                $respuesta['status'] = "success";
                for($i = 0; $i < mb_strlen($sol); $i += 1) {
                    $respuesta['word'][] = "ok";
                }
            } else { // Si la palabra no es igual
                $intento = mb_str_split($valores['palabra']);
                $sol = mb_str_split($sol);
                for($i = 0; $i < count($intento); $i += 1) {
                    // Si la letra está en la solución
                    if(in_array($intento[$i], $sol)) {
                        // se comprueba si está en la misma posición
                        if($intento[$i] == $sol[$i]) {
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
            }
        } else {
            // ! ID no existe o palabra no igual en longitud
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($respuesta);

    }

}