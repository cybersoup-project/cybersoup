<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Attempts extends Connection {
    public function getUserAttemptsAtChallenge($userid, $challengeid) {
        return $this->db->query("SELECT * FROM `attempts` WHERE `user_id` = $userid AND `challenge_id` = $challengeid", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function isUserLoserAtChallenge($userid, $challengeid) {
        return $this->db->query("SELECT * FROM `losers` WHERE `user_id` = $userid AND `challenge_id` = $challengeid", PDO::FETCH_ASSOC)->fetch();
    }

    public function isUserWinnerAtChallenge($userid, $challengeid) {
        return $this->db->query("SELECT * FROM `winners` WHERE `user_id` = $userid AND `challenge_id` = $challengeid", PDO::FETCH_ASSOC)->fetch();
    }

    public function setAttempt($userid, $challengeid, $solution) {
        $sql = "INSERT INTO attempts (solution, user_id, challenge_id) VALUES (?,?,?)";
        $this->db->prepare($sql)->execute([$solution, $userid, $challengeid]);
    }

    public function setWinner($attempt_nr, $userid, $challengeid) {
        $sql = "INSERT INTO winners (attempt, user_id, challenge_id) VALUES (?,?,?)";
        $this->db->prepare($sql)->execute([$attempt_nr, $userid, $challengeid]);
    }

    public function setLoser($userid, $challengeid) {
        $sql = "INSERT INTO losers (user_id, challenge_id) VALUES (?,?)";
        $this->db->prepare($sql)->execute([$userid, $challengeid]);
    }

}