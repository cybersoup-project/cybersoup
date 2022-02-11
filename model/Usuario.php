<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Usuario extends Connection
{
    public function getUserId($u) {
        return $this->db->query("SELECT `iduser` FROM `user` WHERE `username` = '$u'", PDO::FETCH_ASSOC)->fetch()['iduser'];
    }

    public function getUserById($id)
    {
        return $this->db->query("SELECT * FROM `user` WHERE `iduser` = $id", PDO::FETCH_ASSOC)->fetch();
    }

    public function getUserByUsername($u)
    {
        return $this->db->query("SELECT * FROM `user` WHERE `username` = '$u'", PDO::FETCH_ASSOC)->fetch();
    }

    public function getUserByEmail($e)
    {
        return $this->db->query("SELECT * FROM `user` WHERE `email` = '$e'", PDO::FETCH_ASSOC)->fetch();
    }

    public function setUsuario($usuario, $pw, $full_name, $email, $active, $role)
    {
        $creation_date = date("Y-m-d");
        $sql = "INSERT INTO user (username, password, full_name, email, active, role, creation_date, score) VALUES (?,?,?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([$usuario, $pw, $full_name, $email, $active, $role, $creation_date, 0]);
    }

    public function setValidateUserById($id) {
        $sql = "UPDATE user SET active=1 WHERE iduser=?";
        $this->db->prepare($sql)->execute([$id]);
    }

    public function getRanking() { 
        return $this->db->query("SELECT `iduser`, `username`, `score` FROM `user` ORDER BY `score` DESC ", PDO::FETCH_ASSOC)->fetchAll();
    }
    public function getRankingTopTen() { 
        return $this->db->query("SELECT `iduser`, `username`, `score` FROM `user` ORDER BY `score` DESC LiMIT 10 ", PDO::FETCH_ASSOC)->fetchAll();
    }
    public function getMyRanking($user_id) { 
        return $this->db->query("SELECT `username`, `score` FROM `user` WHERE `iduser` = $user_id", PDO::FETCH_ASSOC)->fetchAll();
    }
    public function role($user_id){
        return $this->db->query("SELECT `role` FROM `user` WHERE `iduser`=$user_id",PDO::FETCH_ASSOC)->fetch();
   }
   public function getUserRanking($user_id){
     $this->db->query("SET @row_num=0;");
    return $this->db->query(" SELECT * FROM(SELECT(@row_num:=@row_num + 1) AS num,  `iduser`, `username`, `score` FROM `user`  ORDER BY `user`.`score`  DESC) as tb WHERE tb.`iduser`=$user_id;",PDO::FETCH_ASSOC)->fetch();
   }
}


