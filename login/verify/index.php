<?php

// エラー表示(必要な時以外はコメントアウト)
// error_reporting(E_ALL);
// ini_set("display_errors", "1");

// セッション開始
session_start();

// config読み込み
require_once "../../config/config.php";

// データベース接続
require_once "../../config/database.php";

// tokenをGETから取得
$token = isset($_GET["token"]) ? $_GET["token"] : false;

// tokenを取得できなかった場合
if ($token == false) {
    http_response_code(400);
    include("../../error/400.php");
    exit;
}

// tokenがあるか確認
try {
    $sql = "SELECT student_id, redirect_url FROM mail_token WHERE ipaddress = :ipaddress AND token = :token AND is_verified = 0 AND created_at >= NOW() - INTERVAL 15 MINUTE;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->bindParam(":ipaddress", $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result === false) {
        // 一致するレコードがなかった
        http_response_code(400);
        include("../../error/400.php");
        exit;
    } else {
        // レコードの情報を取得
        $student_id = $result["student_id"];
        $redirect_url = $result["redirect_url"];
    }
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    http_response_code(500);
    include("../../error/database.php");
    exit;
}

// tokenを利用済に変更
try {
    $stmt = $pdo->prepare("UPDATE mail_token SET is_verified = 1 WHERE token = :token;");
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    http_response_code(500);
    include("../../error/database.php");
    exit;
}

// セッション保存
$_SESSION["student_id"] = $student_id;

// ログイン完了
if ($redirect_url == "none") {
    echo '<script> alert("ログインに成功しました。"); window.location.href = "https://' . $config["domain"] . '"; </script>';
} else {
    echo '<script> alert("ログインに成功しました。"); window.location.href = "https://' . $config["domain"] . $redirect_url . '"; </script>';
}

?>