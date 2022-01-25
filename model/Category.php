<?php

require_once("model/connection.php");
/* Al extender la conexión, se llama al constructor de connection, lo cual da acceso a $db */
/* Los nombres de las funciones son explicativas. */
class Category extends Connection {

    public function getCategoryIdByName($name){

        return $this->db->query("SELECT * FROM `category` WHERE `name` = '$name'", PDO::FETCH_ASSOC)->fetch()['idcategory'];

    }

}

?>