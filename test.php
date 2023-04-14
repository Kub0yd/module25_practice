<?php
   include "db_conf.php"; 
   $userIDTemp = $db->prepare("SELECT id FROM users WHERE login = :login");
    $userIDTemp->bindParam(':login, $login');
   $login = "Test1";
   //получаем id авторизованного пользователя
   $stmt = $userIDTemp->execute();
   //$result= $stmt->FETCH(PDO::FETCH_ASSOC);
   $userID = $stmt->fetchColumn();
   echo $userId;
?>