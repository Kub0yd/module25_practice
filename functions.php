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
    $sql = "SELECT id, login, password, user_hash FROM users WHERE id = $id";
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

    $result = getUserByLogin($login);

    return $result['user_hash'];
}
function getFileDataByName($filename){
    include "db_conf.php";

    $sql = "SELECT id, user_id, filename, upload_date FROM files WHERE filename = '$filename'";
    $result = $db->query($sql)->FETCH(PDO::FETCH_ASSOC);

    return $result;
}
function unsetAll(){
    setcookie("id", "", time() - 3600*24*30*12, "/");
    setcookie("hash", "", time() - 3600*24*30*12, "/", null, null, true); // httponly !!!
    // if (isset($_SESSION['auth']))
    // {
    //     unset($_SESSION['auth']);  
    // }
    unset($_SESSION['auth']); 
}
function getCommentsByFile($id){
    include "db_conf.php";

    $sql = "SELECT user_id, comment, create_date FROM comments WHERE file_id = $id";
    $result = $db->query($sql)->FETCHALL(PDO::FETCH_ASSOC);

    return $result;
}
    
?>
