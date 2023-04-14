<?php
   include "db_conf.php"; 
   $login = "Test1";
   $login = $db->quote($login);
   $sql = "SELECT id FROM users WHERE login = $login";
   $userIDTemp = $db->query($sql);
   $userID = $userIDTemp->fetchColumn(0);
   echo $userID;

   $stmt = $db->query("SELECT MAX(id) FROM files");
    $last_id = $stmt->fetchColumn();

    if ($last_id === null) {
    echo "Запрос вернул пустое значение";
    } else {
    echo "Последний id в таблице: " . $last_id;
    }
?>