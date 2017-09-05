<?php

// Загрузка клиентской библиотеки PHP для Google API.
require_once __DIR__ . '/vendor/autoload.php';

// Запуск сеанса для сохранения учетных данных.
session_start();

// Создание объекта клиента и установка конфигурации авторизации
// из файла client_secrets.json, скачанного из Developers Console.
$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/client_secrets.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

// Выполнение процесса авторизации с сервера.
if (! isset($_GET['code'])) {
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

