<?php

// Evitar usar estas funciones.

function esGeneroValido($valor)
{
    $generosValidos = array("h", "m", "nb");
    return in_array($valor, $generosValidos);
}

function isValidDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function isSameString($str1, $str2)
{
    return ($str1 === $str2);
}

function isValidUsername($str)
{
    return preg_match("/^[a-zA-Z0-9]{4,24}$/", $str);
}

function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function getHash($pw) {
    return password_hash($pw, PASSWORD_DEFAULT);
}

function cleanText($t) {
    return htmlentities($t);
}