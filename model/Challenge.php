<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Challenge extends Connection {
    public function getAllChallenges()
    {
        return $this->db->query("SELECT * FROM `challenge` , `category` WHERE `challenge`.`category_id`=`category`.`idcategory`", PDO::FETCH_ASSOC)->fetchAll();
    }

    public function getLast10Challenges()
    {
        return $this->db->query("SELECT * FROM `challenge` ORDER BY `idchallenge` DESC LIMIT 10", PDO::FETCH_ASSOC)->fetch();
    }

    public function getChallengeById($id)
    {
        return $this->db->query("SELECT * FROM `challenge` WHERE `idchallenge` = $id", PDO::FETCH_ASSOC)->fetch();
    }
}
