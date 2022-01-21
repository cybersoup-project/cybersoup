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
        $sql = "INSERT INTO user (username, password, full_name, email, active, role, creation_date) VALUES (?,?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([$usuario, $pw, $full_name, $email, $active, $role, $creation_date]);
    }

    public function setValidateUserById($id) {
        $sql = "UPDATE user SET active=1 WHERE iduser=?";
        $this->db->prepare($sql)->execute([$id]);
    }

}
