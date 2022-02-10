<?php

/* 
Objeto FileUpload, es la función hecha de clase pero adaptada a objeto.
Se puede mejorar.
*/
class FileUpload
{
    public $path;
    public $tagname;
    public $validExtensions;
    public $uploadpath;
    public $max_file_size;
    public $errores = array();
    public $tempdir;
    public $filename;
    public $extension;

    public function __construct($tagname, $uploadpath, array $validExtensions = ['jpg','png'], $max_file_size = 5000000) // 5MB máximo
    {
        $this->tagname = $tagname;
        $this->uploadpath = $uploadpath;
        $this->validExtensions = $validExtensions;
        $this->max_file_size = $max_file_size;
    }

    // Función de clase, pero convertido a objeto.
    function check()
    {
        /* print_r($_FILES); */
        if ($_FILES[$this->tagname]['error'] != 0) {

            /* echo 'Error: '; */
            switch ($_FILES[$this->tagname]['error']) {
                    /* case 1:
                    echo "UPLOAD_ERR_INI_SIZE <br>";
                    echo "Fichero demasiado grande<br>";
                    break;
                case 2:
                    echo "UPLOAD_ERR_FORM_SIZE<br>";
                    echo 'El fichero es demasiado grande<br>';
                    break;
                case 3:
                    echo "UPLOAD_ERR_PARTIAL<br>";
                    echo 'El fichero no se ha podido subir entero<br>';
                    break; */
                case 4:
                    /* echo "UPLOAD_ERR_NO_FILE<br>"; */
                    /* echo 'No se ha podido subir el fichero<br>'; */
                    $this->errores[] = "La imagen es obligatoria.";
                    break;
                    /* case 6:
                    echo "UPLOAD_ERR_NO_TMP_DIR<br>";
                    echo "Falta carpeta temporal<br>";
                case 7:
                    echo "UPLOAD_ERR_CANT_WRITE<br>";
                    echo "No se ha podido escribir en el disco<br>";

                default:
                    echo 'Error indeterminado.'; */
            }
            if ($_FILES[$this->tagname]['error'] !== 4) {
                $this->errores[] = "No se ha podido subir el archivo ajunto, código de error: " . $_FILES[$this->tagname]['error'];
            }
        } else {
            // Guardamos el nombre original del fichero
            $nombreArchivo = $_FILES[$this->tagname]['name'];
            // Guardamos tamaño fichero
            $filesize = $_FILES[$this->tagname]['size'];
            // Guardamos nombre del fichero en el servidor
            $directorioTemp = $_FILES[$this->tagname]['tmp_name'];
            $this->tempdir = $directorioTemp;
            // Guardamos la información del archivo en un array
            $arrayArchivo = pathinfo($nombreArchivo);
            /*
             * Extraemos la extensión del fichero, desde el último punto. Si hubiese doble extensión, no lo
             * tendría en cuenta.
             */
            $extension = mb_strtolower($arrayArchivo['extension']);
            $this->extension = $extension;
            // Comprobamos la extensión del archivo dentro de la lista que hemos definido al principio
            if (!in_array($extension, $this->validExtensions)) {
                $this->errores[] = "La extensión del archivo no es válida o no se ha subido ningún archivo";
            }
            // Comprobamos el tamaño del archivo
            if ($filesize > $this->max_file_size) {
                $this->errores[] = "La imagen debe de tener un tamaño inferior a 5 MB";
            }

            // Almacenamos el archivo en ubicación definitiva si no hay errores

        }
    }

    function upload()
    {
        if (empty($this->errores)) {
            // Nombre único. Al ser un nombre proporcionado por la base de datos no hace falta que el nombre sea fácil de recordar.
            $nombreArchivo = bin2hex(random_bytes(20));
            $nombreCompleto = $this->uploadpath . $nombreArchivo . "." . $this->extension;
            $this->path = $nombreCompleto;
            // Movemos el fichero a la ubicación definitiva

            // Crea la carpeta si no e xiste
            if (!file_exists($this->uploadpath)) {
                mkdir($this->uploadpath, 0777, true);
            }

            if (move_uploaded_file($this->tempdir, $nombreCompleto)) {
                $this->filename = $nombreArchivo . "." . $this->extension;
                return true;
                //echo "<br> El fichero \"$nombreCompleto\" ha sido guardado";
            } else {
                $this->errores[] = "Error: No se puede mover el fichero a su destino";
            }
        }
    }
}
