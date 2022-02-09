<?php

/**
 * Clase para realizar validaciones en el modelo
 * Es utilizada para realizar validaciones en el modelo de nuestras clases.
 *
 */
class Validacion {

    protected $_atributos;

    protected $_error;

    public $mensaje;

    /**
     * Metodo para indicar la regla de validacion
     * El método retorna un valor verdadero si la validación es correcta, de lo contrario retorna el objeto
     * actual, permitiendo acceder al atributo Validacion::$mensaje ya que es publico
     */

    public function rules($rule = array(), $data) {

        if (!is_array($rule)) {
            $this->mensaje = "las reglas deben de estar en formato de arreglo";
            return $this;
        }
        foreach ($rule as $key => $rules) {
            $reglas = explode(',', $rules['regla']);
            if (array_key_exists($rules['name'], $data)) {
                foreach ($data as $indice => $valor) {
                    if ($indice === $rules['name']) {
                        foreach ($reglas as $clave => $valores) {
                            $validator = $this->_getInflectedName($valores);
                            if (!is_callable(array(
                                $this,
                                $validator
                            ))) {
                                throw new BadMethodCallException("No se encontro el metodo $valores");
                            }
                            $respuesta = $this->$validator($rules['name'], $valor);
                        }
                        break;
                    }
                }
            } else {
                $this->mensaje[$rules['name']] = "el campo {$rules['name']} no esta dentro de la regla de validación o en el formulario";
            }
        }
        if (!empty($this->mensaje)) {
            return $this;
        } else {
            return array();
            /* return $this; */
        }
    }

    /*
     * Metodo inflector de la clase
     * por medio de este metodo llamamos a las reglas de validacion que se generen
     */
    private function _getInflectedName($text) {
        $validator = "";
        $_validator = preg_replace('/[^A-Za-z0-9]+/', ' ', $text);
        $arrayValidator = explode(' ', $_validator);
        if (count($arrayValidator) > 1) {
            foreach ($arrayValidator as $key => $value) {
                if ($key == 0) {
                    $validator .= "_" . $value;
                } else {
                    $validator .= ucwords($value);
                }
            }
        } else {
            $validator = "_" . $_validator;
        }

        return $validator;
    }

    /**
     * Metodo de verificacion de que el dato no este vacio o NULL
     * El metodo retorna un valor verdadero si la validacion es correcta de lo contrario retorna un valor falso
     * y llena el atributo validacion::$mensaje con un arreglo indicando el campo que mostrara el mensaje y el
     * mensaje que visualizara el usuario
     */
    protected function _noEmpty($campo, $valor) {
        if (isset($valor) && !empty($valor)) {
            return true;
        } else {
            $this->mensaje[$campo][] = "El campo $campo debe de estar lleno.";
            return false;
        }
    }

    /**
     * Metodo de verificacion de tipo numerico
     * El metodo retorna un valor verdadero si la validacion es correcta de lo contrario retorna un valor falso
     * y llena el atributo validacion::$mensaje con un arreglo indicando el campo que mostrara el mensaje y el
     * mensaje que visualizara el usuario
     */
    protected function _numeric($campo, $valor) {
        if (is_numeric($valor)) {
            return true;
        } else {
            $this->mensaje[$campo][] = "El campo $campo debe de ser numerico.";
            return false;
        }
    }

    /**
     * Metodo de verificacion de tipo email
     * El metodo retorna un valor verdadero si la validacion es correcta de lo contrario retorna un valor falso
     * y llena el atributo validacion::$mensaje con un arreglo indicando el campo que mostrara el mensaje y el
     * mensaje que visualizara el usuario
     */
    protected function _email($campo, $valor) {
        if (filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            $this->mensaje[$campo][] = "Field $campo must have the email format user@server.com.";
            return false;
        }
    }

    protected function _gender($campo, $valor) {
        $generosValidos = array("h", "m", "nb");
        if (in_array($valor, $generosValidos)) {
            return true;
        }
        $this->mensaje[$campo][] = "Field $campo must be a valid gender.";
        return false;
    }

    protected function _name($campo, $valor) {
        if (preg_match("/^[a-zA-Z0-9 ]{4,24}$/", $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "Field $campo must be in between 4 and 24 characters.";
        return false;
    }

    protected function _tit($campo, $valor) {
        if (preg_match("/^[a-zA-Z0-9 ]{4,64}$/", $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "El campo $campo debe tener entre 4 y 64 carácteres.";
        return false;
    }

    protected function _solutionCh($campo, $valor) {
        if (preg_match("/^[a-zA-Z0-9 ]{1,32}$/", $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "El campo $campo debe tener entre 1 y 32 carácteres.";
        return false;
    }

    protected function _helpText($campo, $valor) {
        if (preg_match("/^[a-zA-Z0-9 ]{0,128}$/", $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "El campo $campo debe tener entre menos de 128 carácteres.";
        return false;
    }

    protected function _atemptsNum($campo, $valor) {
        if (preg_match("/^[0-9]{0,11}$/", $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "El campo $campo debe ser un numero entero";
        return false;
    }


    protected function _minmax($campo, $valor) {
        if (preg_match("/^[a-zA-Z0-9]{4,24}$/", $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "Field $campo must be in between 4 and 24 characters, without spaces.";
        return false;
    }

    protected function _password($campo, $valor) {
        if (preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,64}$/", $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "Field $campo must have in between 6 and 64 characters, at least an uppercase, a lowercase, a number and a special character.";
        return false;
    }

    protected function _date($campo, $valor) {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $valor);
        if(($d) && ($d->format($format) === $valor)) {
            return true;
        }
        $this->mensaje[$campo][] = "Field $campo is not a valid date.";
        return false;
    }

    protected function _checked($campo, $valor) {
        if($valor !== "") {
            return true;
        }
        $this->mensaje[$campo][] = "Field $campo must be checked.";
        return false;
    }

    protected function _dificultad($campo, $valor) {
        if ($valor == '1' || $valor == '2' || $valor == '3') {
            return true;
        }
        $this->mensaje[$campo][] = "Field $campo only must be 'easy|middle|hard' .";
        return false;
    }
}

/*
 * Ejemplo de uso de la clase, es muy sencillo.  
 */ 

/* 
$datos['campo1'] = "33";
$datos['campo2'] = "usuario@gmail.com";

$validacion = new Validacion();
$regla = array(
    array(
        'name' => 'campo2',
        'regla' => 'no-empty,email'
    ),
    array(
        'name' => 'campo1',
        'regla' => 'no-empty,numeric'
    )
    
);
$validaciones = $validacion->rules($regla, $datos);
print_r($validaciones);
*/