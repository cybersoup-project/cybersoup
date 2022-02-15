<?php
// TODO:
// Al iniciar o registrase, hace falta refrescar para ver el cambio de las opciones del menú de arriba.
// Login y registro usando la clase de validación.

require 'vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Action
{
    public $loader;
    public $twig;

    public function error_handler($e) {
        echo $this->twig->render("500.html");
        /* echo "Excepción no capturada: " , $e->getMessage(), "\n"; */
    }

    public function __construct()
    {
        $this->loader = new FilesystemLoader('view/');
        $this->twig = new Environment($this->loader);
        $this->twig->addGlobal('usersession', UserSession::getUserSession());
        set_exception_handler(function($e) {
            $this->error_handler($e);
        });
    }

    public function index()
    {
        /* Simply show the index page */
        echo $this->twig->render('index.html');
    }

    public function login()
    {

        $errores = array();
        /* Si la petición es POST, significa que es un intento de login. */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require("model/Usuario.php");
            require("utils/classValidar.php");
            $usuario = new Usuario();
            $valid = new Validacion();
            $valores = array(
                "user" => $_POST['user'] ?? '',
                "pw" => $_POST['pw']  ?? ''
            );
            $regla = array(
                array(
                    'name' => 'user',
                    'regla' => 'minmax'
                )
            );
            $validaciones = $valid->rules($regla, $valores)->mensaje ?? array();
            /* Busco en la base de datos a ver si hay un usuario existente */
            if (count($validaciones) === 0) {
                $r = $usuario->getUserByUsername($valores['user']);
                if ($r) {
                    /* Si existe el usuario, compruebo si está verificado y luego la contraseña */
                    if ($r['active']) {
                        if (password_verify($valores['pw'], $r['password'])) {
                            /* require("utils/UserSession.php");  */
                            $usersession = UserSession::getUserSession();
                            $usersession->addSessionValue("iduser", $r['iduser']);
                            $usersession->addSessionValue("username", $r['username']);
                            $usersession->addSessionValue("rol", $r['role']);
                            $mensajes = array("Has iniciado sesión.");

                            /* Si la contraseña es correcta, se inicia sesión y se muestran artículos. */
                            /* echo $this->twig->render('profile.html', array('mensajes' => $mensajes)); */
                            header('location: ?action=profile');
                        } else {
                            /* Contraseña errónea */
                            $errores[] = "Sorry, the password and/or username isn't right. If you forgot your password, click the \"forgot password?\" button down below.";
                            echo $this->twig->render('Form_LogIn.html', array('errores' => $errores));
                        }
                    } else {
                        $errores[] = "This user isn't verified. Please check your inbox and spam folder and follow the verification instructions.";
                        echo $this->twig->render('Form_LogIn.html', array('errores' => $errores));
                    }
                } else {
                    // Usuario no válido (no existe en la base de datos). Le pongo el error e incluyo la view del login.
                    $errores[] = "Sorry, the password and/or username isn't right. If you forgot your password, click the \"forgot password?\" button down below.";
                    echo $this->twig->render('Form_LogIn.html', array('errores' => $errores));
                }
            } else {
                $errores[] = "Sorry, the password and/or username isn't right. If you forgot your password, click the \"forgot password?\" button down below.";
                echo $this->twig->render('Form_LogIn.html', array('errores' => $errores));
            }
        } else {
            /* Muestro el formulario de registro */
            echo $this->twig->render('Form_LogIn.html');
        }
    }


    public function register()
    {
        /* Mas o menos lo mismo que el login, pero registrando al usuario. */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require("utils/validation.php");
            require("utils/classValidar.php");
            require("model/Usuario.php");
            $user = new Usuario();
            $validation = new Validacion();

            $errores = array();
            $valores = array(
                "Full Name" => $_POST['full_name'] ?? '',
                "Username" => $_POST['username'] ?? '',
                "Password" => $_POST['password'] ?? '',
                "Repeat Password" => $_POST['repeat_password'] ?? '',
                "Email" => $_POST['email'] ?? '',
                "Terms and Conditions" => $_POST['tos'] ?? '',
            );

            $regla = array(
                array(
                    'name' => 'Full Name',
                    'regla' => 'name'
                ),
                array(
                    'name' => 'Username',
                    'regla' => 'minmax'
                ),
                array(
                    'name' => 'Password',
                    'regla' => 'password'
                ),
                array(
                    'name' => 'Email',
                    'regla' => 'email'
                ),
                array(
                    'name' => 'Terms and Conditions',
                    'regla' => 'checked'
                )
            );

            $validaciones = $validation->rules($regla, $valores)->mensaje ?? array();

            // Comprobar que las dos contraseñas sean iguales
            if ($valores['Password'] !== $valores['Repeat Password']) {
                $validaciones['Password'][] = "Passwords must match";
            }

            // Comprobar que el nombre de usuario no exista ya.
            $res = $user->getUserByUsername($valores['Username']);
            if ($res) {
                $validaciones['Username'][] = "El nombre de usuario ya está en uso, escoje otro.";
            }

            // Comprobar que el email no exista ya.
            $res = $user->getUserByEmail($valores['Email']);
            if ($res) {
                $errores['Email'][] = "El email ya está en uso, escoje otro.";
            }

            /* foreach ($validaciones as $key => $value) {
                echo $key . " >> ";
                foreach ($value as $k => $val) {
                    echo $val . "<br>";
                }
            } */

            /* print_r($validaciones); */

            if (count($validaciones) === 0) {
                /* Registrar usuario */
                $usersession = UserSession::getUserSession();
                $config = Config::getConfigObject();

                $rol = 1; // Rol a 1 (Usuario registrado)
                $activo = 0; // Hace falta validar la cuenta por email;
                $fromemail = "registration@cybersoup.tk";
                $fromname = "Cybersoup Registration";
                $replyto = "info@" . $config->getEnvValue("DOMAIN");
                $replytoname = "noreply";
                $subject = "New Cybersoup Registration";
                $template = "verification.html";

                $user->setUsuario($valores['Username'], getHash($valores['Password']), $valores['Full Name'], $valores['Email'], $activo, $rol);

                $userid = $user->getUserId($valores['Username']);

                /* $usersession->addSessionValue("username", $valores['Username']);
                $usersession->addSessionValue("iduser", $userid);
                $usersession->addSessionValue("rol", $rol); */

                require("model/Verification.php");
                require("utils/sendMail.php");

                $verification = new Verification();

                $token = bin2hex(random_bytes($config->getEnvValue("TOKEN_LENGTH")));

                $verification->setVerification($token, $userid);

                $html = $this->twig->render("mail/" . $template, array("token" => $token, "base_url" => $config->getEnvValue("BASE_URL")));

                $mail = new sendMail($valores['Email'], $fromemail, $fromname, $replyto, $replytoname, $subject, $html);
                $mail->send();

                //$mensaje = array("Tu usuario ha sido registrado.");
                //echo $this->twig->render('profile.html', array('mensajes' => $mensaje));
                //header("Location: index.php");
                //header('location: ?action=profile');
                echo $this->twig->render('emailawaitingverification.html', array());
            } else {
                echo $this->twig->render('Form_Registro.html', array('errores' => $validaciones));
            }
        } else {
            echo $this->twig->render('Form_Registro.html');
        }
    }

    function logout()
    {
        /* Salir de la sesión. Borro $_SESSION y la destruyo. */
        $_SESSION = array();
        session_destroy();
        header("Location: index.php");
    }
    function profile()
    {
        //datos del usuario
        require_once("model/Usuario.php");
        $usersession = UserSession::getUserSession();
        $us = new Usuario();
        $datos = $us->getUserById($usersession->getSessionValue("iduser"));
        //Valor del Ranking
        $ranking = new Usuario();
        $role = $ranking->role($usersession->getSessionValue("iduser"));
        $rankings = $ranking->getRanking();
        $miRanking = array_search(($usersession->getSessionValue("iduser")), array_column($rankings, 'iduser'));

        if (!$usersession->getSessionValue("iduser")) {
            header("Location: ?action=register");
        }

        require("model/Challenge.php");

        $challenge = new Challenge();
        $challenges = $challenge->getMyChallenges($usersession->getSessionValue("iduser"));
        $challengesl = $challenge->getMyChallengesLose($usersession->getSessionValue("iduser"));
        echo $this->twig->render('profile.html', array("objectlist" => $challenges, "objectlists" => $challengesl, "userdata" => $datos, 'miRanking' => $miRanking + 1, "rol" => $role));
    }

    function adminView()
    {
        require("model/Usuario.php");
        require("model/Challenge.php");

        $user = new Usuario();
        $users = $user->getUserById(UserSession::getUserSession()->getSessionValue("iduser"));
        $name = $user->getUserById(UserSession::getUserSession()->getSessionValue("iduser"));

        $challenge = new Challenge();
        $notValidC = $challenge->getNotValidChallenges();
        $last10ValidC = $challenge->getLast10ChallengesVerified();
        $numValid = $challenge->getNumChallengeValidate();
        $numNotValid = $challenge->getNumChallengeNotValidate();
        $numValid = $numValid ? $numValid["count(*)"] : 0;
        $numNotValid = $numNotValid ? $numNotValid["count(*)"] : 0;
        echo $this->twig->render('admin_view.html', array("objectlist" => $notValidC, "objectlists" => $last10ValidC, "user" => $users, "name" => $name, "valid" => $numValid, "NotValid" => $numNotValid));
    }

    function ranking()
    {
        require("model/Usuario.php");
        $usersession = UserSession::getUserSession();
        $ranking = new Usuario();
        $rankings = $ranking->getRanking();
        $topTen = $ranking->getRankingTopTen();
        //$miRanking = array_search(($usersession->getSessionValue("iduser")), array_column($rankings, 'iduser'));
        $miRanking = $ranking->getUserRanking(($usersession->getSessionValue("iduser")));
         //die(var_dump($miRanking)); 
        $rankingMio = $ranking->getMyRanking($usersession->getSessionValue("iduser"));
        echo $this->twig->render('ranking.html', array("objectlist" => $topTen, "objectlists" => $rankingMio, 'miRanking' => $miRanking));
    }

    function listChallengers()
    {
        require("model/Challenge.php");
        $challenge = new Challenge();

        $limit = 10;

        if(isset($_GET['page']) && is_numeric($_GET['page'])) {
            $page = $_GET['page'];
        } else {
            $page = 1;
        }

        $total = $challenge->getTotalChallengesCount();

        //die(var_dump($total));

        if($total) {
            //die(print_r($total));
            $total = $total['count(*)'] / $limit;
            /* $total = $total['count(*)']; */
            $nbPages = floor($total) + 1;
            //die($nbPages);
        } else {
            //die("total error");
        }

        $start = ($page-1) * $limit;
        $end = $start + $limit;

        /*
        nbPages -> number of pages
        currentPage (int): current pages
        */

        $challenges = $challenge->getPageChallenges($start, $end);
        $wotdid = $challenge->getChallengeBycategorydate(4, date('Y-m-d'));
        $wotd = $wotdid ? $challenge->getChallengeById($wotdid['idchallenge']) : false;
        echo $this->twig->render('ChallengesList.html', array("objectlist" => $challenges, "wotd" => $wotd, "nbPages" => $nbPages, "currentPage" => $page));
    }

    function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            //----Data Collect--
            $errores = array();
            $valores = array(
                "title" => $_POST['title'] ?? '',
                "solution" => $_POST['solution'] ?? '',
                "helptext" => $_POST['helptext'] ?? '',
                "atempts" => $_POST['atempts'] ?? '',
                "radio" => $_POST['categoria'] ?? ''
                //FECHA?
            );

            //**************Validations*******************/
            require("utils/classValidar.php");
            require("model/Challenge.php");
            $validation = new Validacion();
            $challenge = new Challenge();
            $regla = array(
                array(
                    'name' => 'title',
                    'regla' => 'tit'
                ),
                array(
                    'name' => 'solution',
                    'regla' => 'solutionCh'
                ),
                array(
                    'name' => 'helptext',
                    'regla' => 'helpText'
                ),
                array(
                    'name' => 'atempts',
                    'regla' => 'atemptsNum'
                )

            );
            $validaciones = $validation->rules($regla, $valores)->mensaje ?? array();
            /* print_r($validaciones); */

            if (count($validaciones) == 0) {

                switch ($valores['radio']) {
                    case 'riddles':
                        $image = null;
                        $radio = 'riddles';
                        break;
                    case 'images':
                        require("utils/fileUpload.php");
                        $img = new FileUpload("image", "static/img/");
                        $imagen = $img->check();
                        $radio = 'images';
                        break;
                    case 'words':
                        $image = null;
                        $radio = 'words';
                        break;
                    default:
                        $radio = 'words'; //si hay algun cmbio entramos en words
                        $image = null;
                        break;
                }

                $usersession = UserSession::getUserSession();
                require('model/Category.php');
                $cat = new Category();

                if (isset($img)) {
                    if (count($img->errores) == 0) {
                        $img->upload();
                        $challenge->setchalenges($valores['helptext'], $valores['title'], $valores['solution'], $img->filename, $valores['atempts'], $cat->getCategoryIdByName($radio)['idcategory'], $usersession->getSessionValue("iduser"));
                        header('location: ?action=profile');
                    } else {
                        // ! Hacer errores!!
                        echo $this->twig->render('Form_crearChallenge.html', array("errores" => "Hubo errores"));
                    }
                } else {
                    $challenge->setchalenges($valores['helptext'], $valores['title'], $valores['solution'], null, $valores['atempts'], $cat->getCategoryIdByName($radio)['idcategory'], $usersession->getSessionValue("iduser"));
                    header('location: ?action=profile');
                }
            } else {
                // ! Hacer errores!!
                echo $this->twig->render('Form_crearChallenge.html', array("errores" => "Hubo errores"));
            }
        } else echo $this->twig->render('Form_crearChallenge.html');
    }

    function edit()
    {
        require("model/Challenge.php");
        require("model/Category.php");
        require("utils/classValidar.php");

        $challenge = new Challenge();
        $category = new Category();
        $idChallenge = $_GET['idChallenge'];

        //MUESTRA DATOS
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = array();
            $valores = array(
                "title" => $_POST['title'] ?? '',
                "solution" => $_POST['solution'] ?? '',
                "helptext" => $_POST['helptext'] ?? '',
                "image" => $_POST['image'] ?? '',
                "atempts" => $_POST['atempts'] ?? '',
                "radio" => $_POST['dificultad'] ?? ''
                //FECHA?
            );

            $validation = new Validacion();
            $regla = array(
                array(
                    'name' => 'title',
                    'regla' => 'tit'
                ),
                array(
                    'name' => 'solution',
                    'regla' => 'solutionCh'
                ),
                array(
                    'name' => 'helptext',
                    'regla' => 'helpText'
                ),
                array(
                    'name' => 'atempts',
                    'regla' => 'atemptsNum'
                ),
                array(
                    'name' => 'radio',
                    'regla' => 'dificultad'
                )
            );

            $validaciones = $validation->rules($regla, $valores)->mensaje ?? array();

            if (count($validaciones) == 0) {

                if (isset($img) && $category['name'] == "images") {
                    if (count($img->errores) == 0) {
                        $img->upload();
                        $challenge->updateChallenges($valores['helptext'], $valores['title'], $img->filename, $valores['atempts'], $valores['solution'], $valores['radio'], $idChallenge);
                        header("Location: index.php?action=adminView");
                    }
                } else {
                    $challenge->updateChallenges($valores['helptext'], $valores['title'], null, $valores['atempts'], $valores['solution'], $valores['radio'], $idChallenge);
                    header("Location: index.php?action=adminView");
                }
            } else {
                echo $this->twig->render('Form_editChallenge.html', array("errores" => "Hubo errores"));
            }
        } else if ($values = $challenge->getChallengeById($idChallenge)) {
            $category = $category->getCategoryNameById($values['category_id']);

            echo $this->twig->render('Form_editChallenge.html', array('valuesForm' => $values, 'category' => $category));
        }
    }

    function validateChallenge()
    {
        echo $this->twig->render('Form_validarChallenge.html');
    }

    function game()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            require("model/Challenge.php");
            require("model/Attempts.php");

            $usersession = UserSession::getUserSession();
            $challenge = new Challenge();
            $chl = $challenge->getChallengeById($_GET['id']);
            $attempt = new Attempts();

            $winner = $attempt->isUserWinnerAtChallenge($usersession->getSessionValue("iduser"), $_GET['id']);
            $loser = $attempt->isUserLoserAtChallenge($usersession->getSessionValue("iduser"), $_GET['id']);

            if ($chl) {
                echo $this->twig->render('game.html', array("challenge" => $chl, "length" => mb_strlen($chl['solution']), "winner" => $winner, "loser" => $loser, "idchl" => $_GET['id']));
            } else {
                // ! No Existe el reto (404)
               echo $this->twig->render('404.html');
            }
        } else {
            // ! Mostrar 404
            echo $this->twig->render('404.html');
        }
    }

    function dailygame()
    {
        require("model/Challenge.php");
        require("model/Attempts.php");
        $usersession = UserSession::getUserSession();
        $challen = new Challenge();

        $chl = $challen->getChallengeBycategorydate(4, date('Y-m-d'));
        while (!$chl) { //if there is not challenge of the day

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://random-word-api.herokuapp.com/word?number=1",
                CURLOPT_RETURNTRANSFER => true,
            ));
            $response = json_decode(curl_exec($curl));
            //die($response[0]);

            if (!$challen->existsolution($response[0])) { //If doesnt exist the word as a solution
                $challen->setchalenges(null, "Word of the Day - " . date('y-m-d'), $response[0], null, 5, 4, 1, 3);
                $chl = 1; // DO NOT TOUCH
            }
        }
        //send ig_challenge
        $idchallenge = $challen->getlastChallengeId(date('Y-m-d'));


        if (is_numeric($idchallenge['idchallenge'])) {


            $chl = $challen->getChallengeById($idchallenge['idchallenge']);
            $attempt = new Attempts();

            $winner = $attempt->isUserWinnerAtChallenge($usersession->getSessionValue("iduser"), $idchallenge['idchallenge']);
            $loser = $attempt->isUserLoserAtChallenge($usersession->getSessionValue("iduser"), $idchallenge['idchallenge']);

            if ($chl) {
                echo $this->twig->render('game.html', array("challenge" => $chl, "length" => mb_strlen($chl['solution']), "winner" => $winner, "loser" => $loser, "idchl" => $idchallenge['idchallenge']));
            } else {
                // ! No Existe el reto (404)
                echo $this->twig->render('404.html');
            }
        } else {
            // ! Mostrar 404
            echo $this->twig->render('404.html');

        }
    }

    function verifyEmail()
    {
        require("Config.php");
        $config = Config::getConfigObject();
        if ((isset($_GET['token'])) && ((mb_strlen($_GET['token']) / 2) == $config->getEnvValue("TOKEN_LENGTH"))) {
            require("model/Verification.php");
            $verification = new Verification();
            $v = $verification->getVerifiedUser($_GET['token']);
            $userid = $v ? $v['user_id'] : 0;
            //var_dump($userid);
            if ($userid > 0) {
                require("model/Usuario.php");
                $user = new Usuario();
                $user->setValidateUserById($userid);
                $verification->deleteVerification($userid);
                echo $this->twig->render('emailverified.html');
            } else {
                // unknown token
                header("Location: index.php?action=index");
            }
        } else {
            header("Location: index.php?action=index");
        }
    }
}
