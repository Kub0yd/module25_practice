<?php
   include "db_conf.php"; 
   $login = "Test1";
   $login = $db->quote($login);
   $sql = "SELECT id FROM users WHERE login = $login";
   $userIDTemp = $db->query($sql);
   $userID = $userIDTemp->fetchColumn(0);
   echo $userID;
?>