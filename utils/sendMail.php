<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

class sendMail
{
    private $mail;
    private $to;
    private $from;
    private $fromname;
    private $replyto;
    private $replytoname;
    private $subject;
    private $variables;
    private $templatename;
    private $twig;

    public function __construct($to, $from, $fromname, $replyto, $replytoname, $subject, $templatename, $twig, array $variables = array())
    {
        $this->templatename = $templatename;
        $this->replytoname = $replytoname;
        $this->variables = $variables;
        $this->fromname = $fromname;
        $this->replyto = $replyto;
        $this->subject = $subject;
        $this->from = $from;
        $this->to = $to;

        require("Config.php");

        $this->mail = new PHPMailer();

        //Server settings
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->mail->isSMTP();                                            //Send using SMTP
        $this->mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
        $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->mail->Username   = 'user@example.com';                     //SMTP username
        $this->mail->Password   = 'secret';                               //SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $this->mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $this->mail->setFrom($this->from, $this->fromname);
        $this->mail->addAddress($this->to);     //Add a recipient
        $this->mail->addReplyTo($this->replyto, $this->replytoname);

        //Content
        $this->mail->isHTML(true);                                  //Set email format to HTML
        $this->mail->Subject = $this->subject;
        $this->mail->Body    = $this->twig->render('mail/'.$this->templatename, $this->variables);
        //$this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    }

    public function send() {
        $this->mail->send();
    }
}
