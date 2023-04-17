<?php

function generateCode($length=6) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
    $code = "";
    $clen = strlen($chars) - 1;
    while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0,$clen)];
    }
    return $code;
} 
function getUserByLogin($login){
    include "db_conf.php";

    $login = $db->quote($login);
    $sql = "SELECT id, password, user_hash FROM users WHERE login = $login";
    $result = $db->query($sql)->FETCH(PDO::FETCH_ASSOC);

    return $result;
}
function getUserById($id){
    include "db_conf.php";

    $id = $db->quote($id);
    $sql = "SELECT id, password, user_hash FROM users WHERE id = $id";
    $result = $db->query($sql)->FETCH(PDO::FETCH_ASSOC);

    return $result;
}
function getUserPassword($login){

    $result = getUserByLogin($login);
    return $result['password'];
}

function getUserId($login){

    $result = getUserByLogin($login);

    return $result['id'];
}
function getUserHash($login){


    return $result['user_hash'];
}
function getFileDataByName($filename){
    include "db_conf.php";

    

}
?>
