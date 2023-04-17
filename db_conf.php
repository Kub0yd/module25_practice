<?php
    $host = 'localhost';
    $db = 'Gallery';
    $user = "root";
    $password = "";
    $db = new PDO("mysql:host=$host;dbname=$db", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    
?>