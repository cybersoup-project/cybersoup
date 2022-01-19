<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Usuario extends Connection
{
    public function getUserId($u) {
        return $this->db->query("SELECT `id` FROM `usuarios` WHERE `usuario` = '$u'", PDO::FETCH_ASSOC)->fetch()['id'];
    }

    public function getUserById($id)
    {
        return $this->db->query("SELECT * FROM `usuarios` WHERE `ID` = $id", PDO::FETCH_ASSOC)->fetch();
    }

    public function getUserByUsername($u)
    {
        return $this->db->query("SELECT * FROM `usuarios` WHERE `usuario` = '$u'", PDO::FETCH_ASSOC)->fetch();
    }

    public function getUserByEmail($e)
    {
        return $this->db->query("SELECT * FROM `usuarios` WHERE `email` = '$e'", PDO::FETCH_ASSOC)->fetch();
    }

    public function setUsuario($usuario, $pw, $email, $fnacimiento, $genero, $nombre)
    {
        $sql = "INSERT INTO usuarios (usuario, pass, activo, email, fNacimiento, sexo, nombre) VALUES (?,?,1,?,?,?,?)";
        $this->db->prepare($sql)->execute([$usuario, $pw, $email, $fnacimiento, $genero, $nombre]);
    }
}
