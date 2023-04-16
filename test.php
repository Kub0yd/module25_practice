<?php
   include "db_conf.php"; 
   $login = "Test1";
   $login = $db->quote($login);
   $sql = "SELECT id FROM users WHERE login = $login";
   $userIDTemp = $db->query($sql);
   $userID = $userIDTemp->fetchColumn(0);
   echo $userID;
   $fileName ="dfsf.dsfsf.png";
   $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    echo $fileExt;
   $stmt = $db->query("SELECT MAX(id) FROM files");
    $last_id = $stmt->fetchColumn();

    if ($last_id === null) {
        $newName = date("mdy").'_1';
    } else {
        $newName = date("mdy").'_'.$userID;
    }
    
    //$filePath = UPLOAD_DIR . '/' . $newName;
    if ($last_id === null) {
    echo "Запрос вернул пустое значение";
    } else {
    echo "Последний id в таблице: " . $last_id;
    }
    
    // echo '<form class="delete-form method="post">';

    // echo '<button type="submit" name="delete_image">Удалить</button>';
    // echo '</form>';
    $test = $_POST['image_id'];
    echo "sdsdsdsd";
    echo $test;
    echo var_dump($_POST);
   echo '<form method="post">';
   echo' <input type="hidden" name="image_id" value="12312312313123">';
   echo' <button type="submit" name="delete_image">Удалить</button>';
   echo '</form>';
?>
     <!-- <form method="post">
        <input type="hidden" name="image_id" value="12312312313123">
        <button type="submit" name="delete_image">Удалить</button>
    </form> -->