<?php

// エラー表示(必要な時以外はコメントアウト)
// error_reporting(E_ALL);
// ini_set("display_errors", "1");

// セッション開始
session_start();

// config読み込み
require_once "../config/config.php";

// セッションを一切合切消す
$_SESSION = array();

// セッションを破棄
session_destroy();

// GETからmsgを取得
$message = isset($_GET["msg"]) ? $_GET["msg"] : "false";

// メッセージ表示の指定がなければそのままリダイレクト
if ($message != "true") {
    header("Location: https://" . $config["domain"] . "/login");
    exit;
}

// メッセージを表示して移動
echo '<script> alert("ログアウトに成功しました。"); window.location.href = "https://' . $config["domain"] . '/login/"; </script>';

?>