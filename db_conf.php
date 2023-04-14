<?php
    $host = 'localhost';
    $db = 'Gallery';
    $user = "root";
    $password = "";
    $db = new PDO("mysql:host=$host;dbname=$db", $user, $password);

    $userIDTemp = $db->prepare("SELECT id FROM users WHERE login = :login");
    $userIDTemp->bindParam(':login, $login');
    $login ="";

    $insertImageTemp = $db->prepare("INSERT INTO files (user_id, filename) VALUES (:user_id, :filename)");
    $insertImageTemp->bindParam(':user_id, $userID');
    $insertImageTemp->bindParam(':filename, $filename');
    $userID = "";
    $filename = "";


    //$getImageID = $db->prepare("SELECT id FROM users WHERE login = :login");
?>