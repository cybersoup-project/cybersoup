<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Verification extends Connection
{

    public function setVerification($token, $userid)
    {
        $sql = "INSERT INTO verification (token, user_id) VALUES (?,?)";
        $this->db->prepare($sql)->execute([$token, $userid]);
    }
}
