# ПРАКТИКА (модуль 25) - Продвинутый Backend
Разработка в качестве домашнего задания курса **"Веб-разработчик"** на платформе [Skillfactory](https://skillfactory.ru/).

Тема: "Работа с БД"

## Установка
Разработка тестировалась с использованием OpenServer

Версия тестировалась на  MySQL Community Server 8.0.30

1. Скачайте (или воспользуйтесь командой "git clone https://github.com/Kub0yd/module25_practice.git") файлы на сервер
2. Импортируйте файл [Gallery.sql](./Gallery.sql) на свой сервер MySQL
3. Изображения для загрузки можно взять из папки [images](./images/)
4. Для отрабоки внешних скриптов нужен доступ в интернет

## Структура
1. [db_conf.php](./db_conf.php) - настройки подлключения к базе данных
2. [function.php](./functions.php) - функции для обработки данных
3. [Gallery.sql](./Gallery.sql) - файл с настройками таблиц (необходим для импорта таблиц в бд)
4. [index.php](./index.php) - основной файл для работы с сайтом
5. [login.php](./login.php) - настройки страницы авторизации
6. [images](./images/) - папка с изображениями, для примера загрузки
7. [style](./style/) - папка с файлами настройки стилизации
8. [upload](./upload/) - папка, в которой будут храниться загружаемые с сайта файлы



