<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Challenge extends Connection
{

    public function setchalenges($text, $title, $solution, $image, $atempts, $user_id, $category_id) {
        $date = date("Y-m-d");
        $verified = 0;
        $trusted = 0;
        $times_played = 0;
        $times_success = 0;
        $dificulty = 0;
        $sql = "INSERT INTO challenge (text,title,image,max_attempts,solution,verified,trusted,times_played,times_success,difficulty,date,category_id,user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([$title, $text, $image, $atempts, $solution, $verified, $trusted, $times_played, $times_success, $dificulty, $date, $category_id, $user_id]);
    }

    public function updateChallenges($text, $title, $image, $max_attempts, $solution, $difficulty, $idChallenge)
    {
        $verified = true;

        $sql = "UPDATE challenge SET text=?, title=?, image=?, max_attempts=?, solution=?, verified=?, difficulty=?  WHERE idchallenge=?";
        $this->db->prepare($sql)->execute(array($text, $title, $image, $max_attempts, $solution, $verified, $difficulty, $idChallenge));
    }


    public function getAllChallenges() {
        return $this->db->query("SELECT * FROM `challenge` , `category` WHERE `challenge`.`category_id`=`category`.`idcategory` ORDER BY `idchallenge` DESC", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function getLast10Challenges() {
        return $this->db->query("SELECT * FROM `challenge`  ORDER BY `idchallenge` DESC LIMIT 10", PDO::FETCH_ASSOC)->fetch();
    }

    public function getChallengeById($id) {
        return $this->db->query("SELECT * FROM `challenge` WHERE `idchallenge` = $id", PDO::FETCH_ASSOC)->fetch();
    }

    public function getMyChallenges($user_id) {
        return $this->db->query("SELECT * FROM `challenge` WHERE `user_id` = $user_id", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function getNotValidChallenges() {
        return $this->db->query("SELECT * FROM `challenge` WHERE `verified` = 0", PDO::FETCH_ASSOC)->fetchAll();
    }
    
    public function getLast10ChallengesVerified() {
        return $this->db->query("SELECT * FROM `challenge` WHERE `verified` = 1  ORDER BY `idchallenge` DESC LIMIT 10", PDO::FETCH_ASSOC)->fetch();
    }

    public function getPoints() {
        return $this->db->query("SELECT `max_attempts`, `difficulty`, `attempt` FROM `winners`, `challenge` WHERE `winners`.`user_id`=`challenge`.`user_id` and `winners`.`user_id`=6", PDO::FETCH_ASSOC)->fetch();
    }
}
