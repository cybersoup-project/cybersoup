<?php

// Evitar usar estas funciones.

function esGeneroValido($valor) {
    $generosValidos = array("h", "m", "nb");
    return in_array($valor, $generosValidos);
}

function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function isSameString($str1, $str2) {
    return ($str1 === $str2);
}

function isValidUsername($str) {
    return preg_match("/^[a-zA-Z0-9]{4,24}$/", $str);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function getHash($pw) {
    return password_hash($pw, PASSWORD_DEFAULT);
}

function cleanText($t) {
    return htmlentities($t);
}

function get_time_ago($time) {
    $time_difference = time() - $time;

    if ($time_difference < 1) {
        return 'less than 1 second ago';
    }
    $condition = array(
        12 * 30 * 24 * 60 * 60 =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );

    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;

        if ($d >= 1) {
            $t = round($d);
            return 'about ' . $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
}
