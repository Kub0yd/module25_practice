<?php
include "db_conf.php"; 
define('URL', './'); // URL текущей страницы
define('UPLOAD_MAX_SIZE', 1000000); // 1mb
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', 'images');
 
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

        $login = 'Test1';
        //получаем id авторизованного пользователя
        $stmt = $userIDTemp->execute();
        //$result= $stmt->FETCH(PDO::FETCH_ASSOC);
        $userID = $stmt->fetchColumn();

        $newName = date("mdy").'_'.$userID;
        $filePath = UPLOAD_DIR . '/' . basename($fileName);
 
        // if (!move_uploaded_file($_FILES['files']['tmp_name'][$i], $filePath)) {
        //     $errors[] = 'Ошибка загрузки файла ' . $fileName;
        //     continue;
        // }
        
    }
}
 
?>
<!DOCTYPE html>
<html>
<head>
  <title>Моя галерея изображений</title>
  <!-- Подключение Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

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
      $chunks = array_chunk($files, 4);

      // Выводим каждый ряд изображений и комментариев
      foreach ($chunks as $chunk) {
        echo '<div class="row">';
        foreach ($chunk as $file) {
          echo '<div class="col-md-3">';
          echo '<img src="'.$dir.$file.'" class="img-responsive" alt="'.$file.'">';
          echo '<input type="text" class="form-control" placeholder="Введите комментарий">';
          echo '</div>';
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
    <?php endif; ?>
 
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
</div>
 
<div class='message-div message-div_hidden' id='message-div'></div>
</body>
</html>