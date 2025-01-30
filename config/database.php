<?php

// config読み込み
require_once("config.php");

// 変数
$host = $config["host"];
$dbname = $config["dbname"];
$username = $config["username"];
$password = $config["password"];

// 接続準備
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 接続
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // エラー
    error_log("データベース接続エラー：" . $e->getMessage(), 3, __DIR__ . "/../logs/database_errors.log");
    die("データベース接続エラー");
}

?>