<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Challenge extends Connection
{

    public function setchalenges($text, $title, $solution, $image, $atempts, $category_id, $user_id, $difficulty = 0) {
        $date = date("Y-m-d H:i:s");
        $verified = 0;
        $trusted = 0;
        $times_played = 0;
        $times_success = 0;
        $sql = "INSERT INTO challenge (text,title,image,max_attempts,solution,verified,trusted,times_played,times_success,difficulty,date,category_id,user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([$text, $title, $image, $atempts, $solution, $verified, $trusted, $times_played, $times_success, $difficulty, $date, $category_id, $user_id]);
    }

    public function updateChallenges($title, $image, $max_attempts, $solution, $difficulty, $idChallenge, $text=NULL)
    {
        $verified = true;

        $sql = "UPDATE `challenge` SET `title`=?, `text`=?, `image`=?, `max_attempts`=?, `solution`=?, `verified`=?, `difficulty`=?  WHERE `idchallenge`=?";
        $this->db->prepare($sql)->execute(array( $title,$text, $image, $max_attempts, $solution, $verified, $difficulty, $idChallenge));
    }
    
    public function updateChallengesPlay($times_played, $idchallenge)
    {
        $sql = "UPDATE challenge SET times_played=?  WHERE idchallenge=?";
        $this->db->prepare($sql)->execute(array($times_played, $idchallenge));
    }

    public function updateChallengesWins($times_success, $idchallenge)
    {
        $sql = "UPDATE challenge SET times_success=?  WHERE idchallenge=?";
        $this->db->prepare($sql)->execute(array($times_success, $idchallenge));
    }

    public function getAllChallenges() {
        return $this->db->query("SELECT * FROM `challenge` , `category` WHERE `challenge`.`category_id`=`category`.`idcategory` AND `category`.`idcategory` != 4 ORDER BY `idchallenge` DESC", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function getPageChallenges($start, $end) {
        return $this->db->query("SELECT * FROM `challenge` , `category` WHERE `challenge`.`category_id`=`category`.`idcategory` AND `category`.`idcategory` != 4 ORDER BY `idchallenge` DESC LIMIT $start, $end", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function getTotalChallengesCount() {
        return $this->db->query("SELECT count(*) FROM `challenge` WHERE `category_id` != 4", PDO::FETCH_ASSOC)->fetch();
    }

    public function getLast10Challenges() {
        return $this->db->query("SELECT * FROM `challenge`  ORDER BY `idchallenge` DESC LIMIT 10", PDO::FETCH_ASSOC)->fetch();
    }

    public function getChallengeById($id) {
        return $this->db->query("SELECT * FROM `challenge` WHERE `idchallenge` = $id", PDO::FETCH_ASSOC)->fetch();
    }
    public function getValidChallenge() {
        return $this->db->query("SELECT * FROM `challenge` WHERE `verified` = 1", PDO::FETCH_ASSOC)->fetchAll();
    }
    public function getMyChallenges($user_id) {
        return $this->db->query("SELECT idchallenge, name, difficulty, title FROM challenge JOIN winners ON winners.challenge_id = challenge.idchallenge JOIN category ON category.idcategory = challenge.category_id WHERE winners.user_id = $user_id", PDO::FETCH_ASSOC)->fetchAll();
    }
    public function getMyChallengesLose($user_id) {
        return $this->db->query("SELECT idchallenge, name, difficulty, title FROM challenge JOIN losers ON losers.challenge_id = challenge.idchallenge JOIN category ON category.idcategory = challenge.category_id WHERE losers.user_id = $user_id; ", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function getNotValidChallenges() {
        return $this->db->query("SELECT `name`, `difficulty`, `title`,`idChallenge` FROM `challenge` JOIN category ON category.idcategory = challenge.category_id WHERE `verified` = 0 ;", PDO::FETCH_ASSOC)->fetchAll();
    }
    
    public function getLast10ChallengesVerified() {
        return $this->db->query("SELECT `name`, `difficulty`, `title` FROM `challenge` JOIN category ON category.idcategory = challenge.category_id WHERE `verified` = 1 ORDER BY challenge.idchallenge DESC LIMIT 10;", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function getPoints() {
        return $this->db->query("SELECT `max_attempts`, `difficulty`, `attempt` FROM `winners`, `challenge` WHERE `winners`.`user_id`=`challenge`.`user_id` and `winners`.`user_id`=6", PDO::FETCH_ASSOC)->fetch();
    }

    public function getChallengeBycategorydate($id,$date) {
        return $this->db->query("SELECT `idchallenge` FROM `challenge` WHERE date(date)='$date' AND `category_id`=$id", PDO::FETCH_ASSOC)->fetch();
    }

    public function existsolution($solution){
        return $this->db->query("SELECT `idchallenge` FROM `challenge` WHERE `solution`='$solution'", PDO::FETCH_ASSOC)->fetch();
    }

    public function getlastChallengeId($date) {
        return $this->db->query("SELECT `idchallenge` FROM `challenge` WHERE date(date) = '$date' AND `category_id`=4", PDO::FETCH_ASSOC)->fetch();
    }
    
    public function getNumChallengeValidate(){
        return $this->db-> query("SELECT count(*) FROM `challenge` where `verified`=1 ", PDO::FETCH_ASSOC)->fetch();
    }
    public function getNumChallengeNotValidate(){
        return $this->db-> query("SELECT count(*) FROM `challenge` where `verified`=0 ", PDO::FETCH_ASSOC)->fetch();

    }
    public function getImg($id){
        return $this->db-> query("SELECT `image` FROM `challenge` where `idchallenge`=$id ", PDO::FETCH_ASSOC)->fetch();
    }
    public function getwinAttempts($id,$idchallenge){
        return $this->db-> query("SELECT `max_attempts`, `difficulty`, `attempt` FROM `winners`, `challenge` 
        WHERE `winners`.`user_id`=`challenge`.`user_id` and `winners`.`user_id`=$id AND `idchallenge`=$idchallenge 
        ORDER BY `attempt` DESC LIMIT 1; ", PDO::FETCH_ASSOC)->fetch();
    }
    
}
