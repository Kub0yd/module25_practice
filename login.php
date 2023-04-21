<?php
    include "db_conf.php";
    include "functions.php";

    session_start();
    $auth = $_SESSION['auth'] ?? null;
    //если пользователь авторизован его перкидывает на главную страницу
    if ($auth) {
      header("Location: ./index.php");
    }
//обработчик регистрации
if(isset($_POST['registration']))
{   
    $err = [];
    // проверяем логин
    if(!preg_match("/^[a-zA-Z0-9]+$/",$_POST['username']))
    {
        $err[] = "Логин может состоять только из букв английского алфавита и цифр";
    } 
    if(strlen($_POST['username']) < 3 or strlen($_POST['username']) > 30)
    {
        $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";
    } 
    // проверяем, не существует ли пользователя с таким именем
    $username = $db->quote($_POST['username']);
    $query = $db->query("SELECT id FROM users WHERE login=$username");
    if($query->rowCount() > 0)
    {
        $err[] = "Пользователь с таким логином уже существует в базе данных";
    } 
    // Если нет ошибок, то добавляем в БД нового пользователя
    if(count($err) == 0)
    {
        $username = $db->quote($_POST['username']);
        // Убираем лишние пробелы и делаем хэширование
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (login, password) VALUES ($username, '$password' )";
        $db->query($sql);

        echo "<script>alert(\"Вы успешно зарегистрировались!\");</script>";


    }
    else
    {
        print "<b>При регистрации произошли следующие ошибки:</b><br>";
        foreach($err AS $error)
        {
            print $error."<br>";

        }
    }
    
}
//обработчик входа на сайт
if(isset($_POST['login']))
{
    //сравниваем введенный пароль, и пароль в бд
    $login = $_POST['username'];
    $password = $_POST['password'];
    if(password_verify($password, getUserPassword($login)))
    {
        // Генерируем случайное число и шифруем его
        $hash = md5(generateCode(10));
 
        // Записываем в БД новый хеш авторизации и IP
        $db->query("UPDATE users SET user_hash='".$hash."' WHERE id='".getUserId($login)."'"); 
        // Ставим куки
        setcookie("id", getUserId($login), time()+60*60*24*30, "/");
        setcookie("hash", $hash, time()+60*60*24*30, "/", null, null, true); // httponly !!! 
        // Переадресовываем браузер на страницу проверки нашего скрипта
        $_SESSION['auth'] = true; 
        header("Location: index.php"); exit();
    }
    else
    {   
      echo "<script>alert(\"Вы ввели неправильный логин/пароль!\");</script>";

    }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <link rel="stylesheet" href="style/login.css" />
  </head>
  <body>
    <form method="post">
      <h2>Авторизация</h2>
      <label for="username">Имя пользователя:</label>
      <input type="text" id="username" name="username">
      <label for="password">Пароль:</label>
      <input type="password" id="password" name="password" required>
      <input type="submit" value="Войти" name="login">
      <input type="submit" value="Зарегистрироваться" name="registration">
    </form>
  </body>
</html>
