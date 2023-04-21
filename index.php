<?php
include "db_conf.php"; 
include "functions.php";

session_start();
$auth = $_SESSION['auth'] ?? null;  //переменная для отметки авторизованного пользователя
define('URL', './'); // URL текущей страницы
define('UPLOAD_MAX_SIZE', 1000000); // 1mb
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', 'upload');

//проверяем пришедшие со страницы аутентификации куки
if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) {
    //получаем информацию о юзере через id
    $userData = getUserById($_COOKIE['id']);
    //если хэш или id не совпадают, стираем все куки и сессии
    if (($userData['user_hash'] !== $_COOKIE['hash']) or ($userData['id'] !== $_COOKIE['id'])) {

        unsetAll();
        
        echo "<script>alert(\"Что-то пошло не так с авторизацией.. Попробуйте повторить вход\");</script>";
        header('Location: ./index.php');  
    }
    else {
        //ставим отметку авторизованного пользователя
         $auth = $_SESSION['auth'] ?? null; 
    }
}

$errors = [];
//проверка на переданные файлы 
if (!empty($_FILES) && $auth) {
    //перебираем файлы в массиве
    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
 
        $fileName = $_FILES['files']['name'][$i];
        //записываем ошибки в $errors
        if ($_FILES['files']['size'][$i] > UPLOAD_MAX_SIZE) {
            $errors[] = 'Недопустимый размер файла ' . $fileName;
            continue;
        }
 
        if (!in_array($_FILES['files']['type'][$i], ALLOWED_TYPES)) {
            $errors[] = 'Недопустимый формат файла ' . $fileName;
            continue;
        }

        //Получаем id пользователя
        $userID = $_COOKIE['id'];
        //получаем доступный id из бд
        $stmt = $db->query("SELECT MAX(id) FROM files");
        $last_id = $stmt->fetchColumn() + 1;
        //расширение загружаемого файла
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        //формируем новое название файла в формате dmy_`id`
        if ($last_id === null) {
            $newName = date("dmy").'_1.'.$fileExt;
        } else {
            $newName = date("dmy").'_'.$last_id.'.'.$fileExt;
        }
        
        $filePath = UPLOAD_DIR . '/' . $newName;
 
        if (!move_uploaded_file($_FILES['files']['tmp_name'][$i], $filePath)) {
            $errors[] = 'Ошибка загрузки файла ' . $fileName;
            continue;
        }
        //добавялем в бд данные о загруженном файле
        $sql = "INSERT INTO files (user_id, filename) VALUES ('$userID', '$newName')";
        $db->query($sql);

        
    }


}
//обработчик удаления изображения
if(isset($_POST['delete_image'])) {
    //удаляем файл из директории
    $fileName = $_POST['delete_image'];
    $filePath = UPLOAD_DIR . '/' . $fileName;
    @unlink($filePath);
    //удаляем комментарии к файлу из бд
    $sql= "DELETE FROM comments WHERE file_id = (SELECT id FROM files WHERE filename = '$fileName' )";
    $db->query($sql);
    //удаляем записи по файлу из бд
    $sql= "DELETE FROM files WHERE filename = '$fileName'";
    $db->query($sql);
    
    // перенаправление на страницу с галереей
    header('Location: index.php');
    exit();
}
//обработчик разлогинивания (если такое слово есть:)
if(isset($_POST['sign_out'])) {
    unsetAll();
    header("Location: ./"); 
    exit();
}
//обработчик добавления комментария к изображению
if (isset($_POST['add_comment'])){
    
    $user_id = $_COOKIE['id'];
    //экранируем ввод
    $comment = $db->quote($_POST['add_comment']);
    $file_id = $_POST['image_id'];
    //добавляем в бд данные о комментарии (id пользователя; текст; id файла)
    $sql = "INSERT INTO comments (user_id, comment, file_id) VALUES ('$user_id', $comment, '$file_id')";
    $db->query($sql);
    header("Location: ./"); exit();
}
//обработчик удаления комментария
if(isset($_POST['delete-comment'])) {
    
    $commentId = $_POST['delete-comment'];
    //ищем запись по переданному id и удаляем
    $sql = "DELETE FROM comments WHERE id='$commentId'";
    $db->query($sql);
    
    // перенаправление на страницу с галереей
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Моя галерея изображений</title>
  <link rel="stylesheet" href="style/index.css" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
    
</head>
<body>
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <!-- логотип -->
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Gallery</a>
            </div>
            <!-- вывод кнопок в зависимости от проверки на авторизацию -->
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                <?php if (!$auth) {?>
                <li><a href="./login.php" class="btn btn-secondary">Войти</a></li>
                <?php } else {?>
                <form action="./" method="post">
                <button type="submit" class="btn btn-primary" name="sign_out" id="logo" formaction="index.php">ВЫЙТИ</button>
                </form>
                <?php } ?>
                </ul>
            </div>
        </div>
    </nav>
  <div class="container">
    <h2>Моя галерея изображений</h2>

    <?php
      // Обработчик вывода изображений
      // Путь к папке с изображениями
      $dir = UPLOAD_DIR."/";

      // Получаем список файлов в папке исключая файлы с недопустимыми расширениями (если вручную загрузили например)
      
      $files = array_filter(scandir($dir), 
        function($file) {
        $dir = UPLOAD_DIR."/";
        $type = mime_content_type($dir . $file);
        return in_array($type, ALLOWED_TYPES);
        }
     );
    
      // Разбиваем массив файлов на массивы по 3 элемента (хочу, чтобы изображения выводились в ряд по 3 элемента)
      $chunks = array_chunk(array_reverse($files), 3);

      // Выводим каждый ряд изображений и комментарии к ним
      foreach ($chunks as $chunk) {
        echo '<div class="row">';
        foreach ($chunk as $file) {
            $fileData = getFileDataByName($file);    // получаем все записи по изображению из бд по названию файла
            $userUploadId = $fileData['user_id'];
            $fileId = $fileData['id'];

            $comments = getCommentsByFile($fileId);  // получаем все комментарии к изображению
            
        ?>
        <div class="col-md-4">
            <div class="thumbnail">
                <a data-fancybox="gallery" href="<?php echo $dir.$file?>">
                <img src="<?php echo $dir.$file?>" class="img-fluid" alt="<?php echo $file ?>">
                </a>
                <?php
                    // показываем кнопку удалить если изображение добавил авторизированный пользователь 
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
                        // выводим комментарии по очереди 
                        if ($comments){
                            foreach ($comments as $comment){
                            $commentUserId = $comment['user_id'];                   //id пользователя
                            $commentUser = getUserById($commentUserId);             //логин
                            $commentText = $comment['comment'];                     //текст комментария
                            $commentDate = date_create($comment['create_date']);    //дата создания комментария
                            $commentId = $comment['id'];                            //id комментария
                    ?>
                    <div id="comments">
                        <div class="row">
                            <div class="col col-md-7"><?php echo $commentUser['login'].':'; ?></div>
                            <div class="col col-md-5" id="comment-date"><?php echo date_format($commentDate, "d.m.y H:i"); ?></div>
                        </div>
                        <div class="row">
                            <div class="col col-md-10 comment-text" ><?php echo $commentText;?></div>
                            <?php
                                // показываем кнопку удалить если комментарий добавил авторизированный пользователь  
                                if ($auth && $commentUserId == $_COOKIE["id"])
                                {   
                            ?>
                            <div class="col col-md-1">
                                <form method="post">
                                    <input type="hidden" name="delete-comment" value="<?php echo $commentId ?>">
                                    <button type="submit"><span class="glyphicon glyphicon-trash"></span></button>
                                </form>
                            </div>
                            
                            <?php 
                                }
                            ?>
                        </div> 
                        
                    </div>
                    
                    <?php
                        }
                           }?>
                </div>
                <form action="<?php echo URL; ?>" method="post">
                    <div class="form-group">
                        <?php
                            //добавлем возможность оставить комментарий авторизованному пользователю 
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
 
    <?php
        //выводим ошибки, если они есть 
        if (!empty($errors)): ?>
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
        //показываем поле для загрузки изображений авторизованным пользователям 
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