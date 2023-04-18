<?php
include "db_conf.php"; 
include "functions.php";
session_start();
$auth = $_SESSION['auth'] ?? null;  //переменная для отметки авторизованного пользователя
define('URL', './'); // URL текущей страницы
define('UPLOAD_MAX_SIZE', 1000000); // 1mb
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', 'images');

if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) {
    $userData = getUserById($_COOKIE['id']);
    if (($userData['user_hash'] !== $_COOKIE['hash']) or ($userData['id'] !== $_COOKIE['id'])) {

        unsetAll();
        // unset($_SESSION['auth']);
        
        echo "<script>alert(\"Что-то пошло не так с авторизацией.. Попробуйте повторить вход\");</script>";
        header('Location: ./index.php');  
    }
    else {
         $auth = $_SESSION['auth'] ?? null; 
    }
}

$errors = [];
 
if (!empty($_FILES)) {
 
    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
 
        $fileName = $_FILES['files']['name'][$i];
        echo $filename['name'];
        if ($_FILES['files']['size'][$i] > UPLOAD_MAX_SIZE) {
            $errors[] = 'Недопустимый размер файла ' . $fileName;
            continue;
        }
 
        if (!in_array($_FILES['files']['type'][$i], ALLOWED_TYPES)) {
            $errors[] = 'Недопустимый формат файла ' . $fileName;
            continue;
        }
        //Получаем ид пользователя
        $userID = $_COOKIE['id'];
        //получаем ид последнего загруженного файла
        $stmt = $db->query("SELECT MAX(id) FROM files");
        $last_id = $stmt->fetchColumn();
        //расширение загружаемого файла
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($last_id === null) {
            $newName = date("dmy").'_1.'.$fileExt;
        } else {
            $newName = date("dmy").'_'.$last_id .'.'.$fileExt;
        }
        
        $filePath = UPLOAD_DIR . '/' . $newName;
 
        if (!move_uploaded_file($_FILES['files']['tmp_name'][$i], $filePath)) {
            $errors[] = 'Ошибка загрузки файла ' . $fileName;
            continue;
        }
        //добавялем в бд данные о загруженном файле
        $sql = "INSERT INTO files (user_id, filename) VALUES ('$userID', '$newName')";
        $db->query($sql);
        header('Location: index.php');
        exit();
    }
    
}

if(isset($_POST['delete_image'])) {
    $fileName = $_POST['image_id'];
    $filePath = UPLOAD_DIR . '/' . $fileName;
    @unlink($filePath);
    
    $sql= "DELETE FROM files WHERE filename = '$fileName'";
    $db->query($sql);
    
    // перенаправление на страницу с галереей
    header('Location: index.php');
    exit();
}
if(isset($_POST['sign_out'])) {
   unsetAll();
    header("Location: ./"); exit();
}
if (isset($_POST['add_comment'])){
    
    $user_id = $_COOKIE['id'];
    $comment = $_POST['add_comment'];
    $file_id = $_POST['image_id'];
    echo $file_id;
    echo "<br>";
    $sql = "INSERT INTO comments (user_id, comment, file_id) VALUES ('$user_id', '$comment', '$file_id')";
    $db->query($sql);
    header("Location: ./"); exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Моя галерея изображений</title>
  <!-- Подключение Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
  <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> -->
  <link rel="stylesheet" href="./index.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />

</head>
<body>
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <!-- логотип -->
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Logo</a>
            </div>
            <!-- кнопка-гамбургер для мобильной версии -->
            <!-- <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button> -->
            <!-- пункты меню -->
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                <?php if (!$auth) {?>
                <li><a href="./login.php" class="btn btn-secondary">Войти</a></li>
                <?php } else {?>
                <form action="./" method="post">
                <button type="submit" class="btn btn-primary" name="sign_out" formaction="index.php">Sign OUT</button>
                </form>
                <?php } ?>
                </ul>
            </div>
        </div>
    </nav>
  <div class="container">
    <h2>Моя галерея изображений</h2>

    <?php
      // Путь к папке с изображениями
      $dir = "images/";

      // Получаем список файлов в папке
      $files = scandir($dir);

      // Удаляем первые два элемента массива (".", "..")
      $files = array_slice($files, 2);

      // Разбиваем массив файлов на массивы по 4 элемента
      $chunks = array_chunk(array_reverse($files), 3);

      // Выводим каждый ряд изображений и комментариев
      foreach ($chunks as $chunk) {
        echo '<div class="row">';
        foreach ($chunk as $file) {
            $fileData = getFileDataByName($file);
            $userUploadId = $fileData['user_id'];
            $fileId = $fileData['id'];

            $comments = getCommentsByFile($fileId);
            
        ?>
        <div class="col-md-4">
            <div class="thumbnail">
                <a data-fancybox="gallery" href="<?php echo $dir.$file?>">
                <img src="<?php echo $dir.$file?>" class="img-fluid" alt="<?php echo $file ?>">
                </a>
                <?php 
                    if ($auth && $userUploadId == $_COOKIE["id"])
                     {
                        
                ?>
                <form method="post">
                    <input type="hidden" name="delete_image" value="<?php echo $file ?>">
                    <button type="submit" name="delete">Удалить</button>
                </form>
                <?php 
                    }
                ?>
                <div class="thumbnail" id="comments-block">
                    <h3>Комментарии:</h3>
                    <?php 
                        if ($comments){
                            foreach ($comments as $comment){
                            $commentUser = getUserById($comment['user_id']);
                            $commentText = $comment['comment'];
                            $commentDate = date_create($comment['create_date']);
                    ?>
                    <div class="container">
                        <div class="row">
                            <div class="col col-md-2"><?php echo $commentUser['login'].':'; ?></div>
                            <div class="col col-md-4" id="comment-date"><?php echo date_format($commentDate, "d.m.y H:i"); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-12"><?php echo $commentText;?></div>
                        </div>    
                    </div>
                    
                    <?php
                        }
                           }?>
                </div>
                <form action="<?php echo URL; ?>" method="post">
                    <div class="form-group">
                        <?php 
                            if ($auth) {

                        ?>
                        <input type="hidden" name="image_id" value="<?php echo $fileId ?>">
                        <input class="form-control" type="text" rows="1" id="comment" name="add_comment" placeholder="Оставьте комментарий" required>
                        <button type="submit" class="btn btn-primary" name="upload_comment">Отправить</button>
                        <?php } else{ ?>
                        <a href="./login.php" class="btn btn-primary">Войти для возможности комментирования</a>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
        }
        echo '</div>';
      }
    ?>
  </div>
  <div class="container pt-4">
    <h1 class="mb-4">Загрузка файлов</h1>
 
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
 
    <?php if (!empty($_FILES) && empty($errors)): ?>
        <div class="alert alert-success">Файлы успешно загружены</div>
    <?php endif; 
          if ($auth){ 
    ?>
    <form action="<?php echo URL; ?>" method="post" enctype="multipart/form-data">
        <div class="custom-file">
            <input type="file" class="custom-file-input" name="files[]" id="customFile" multiple required>
            <label class="custom-file-label" for="customFile" data-browse="Выбрать">Выберите файлы</label>
            <small class="form-text text-muted">
                Максимальный размер файла: <?php echo UPLOAD_MAX_SIZE / 1000000; ?>Мб.
                Допустимые форматы: <?php echo implode(', ', ALLOWED_TYPES) ?>.
            </small>
        </div>
        <hr>
        <button type="submit" class="btn btn-primary">Загрузить</button>
        <a href="<?php echo URL; ?>" class="btn btn-secondary ml-3">Сброс</a>
    </form>
    <?php }else {?>
    <h3>Авторизуйтесь для возможности загрузки изображений</h3>
    <?php } ?>
</div>

<div class='message-div message-div_hidden' id='message-div'></div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>

</body>
</html>