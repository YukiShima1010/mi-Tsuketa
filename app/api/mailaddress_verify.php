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
    $sql = "SELECT student_id, mailaddress FROM mail_token WHERE ipaddress = :ipaddress AND token = :token AND is_verified = 3 AND created_at >= NOW() - INTERVAL 15 MINUTE;";
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
        $mailaddress = $result["mailaddress"];
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
    $stmt = $pdo->prepare("UPDATE mail_token SET is_verified = 4 WHERE token = :token;");
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    http_response_code(500);
    include("../../error/database.php");
    exit;
}

// メールアドレスを変更
try {
    $stmt = $pdo->prepare("UPDATE students SET mailaddress = :mailaddress WHERE id = :student_id;");
    $stmt->bindParam(":mailaddress", $mailaddress, PDO::PARAM_STR);
    $stmt->bindParam(":student_id", $student_id, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    http_response_code(500);
    include("../../error/database.php");
    exit;
}

// 変更完了
echo '<script> alert("メールアドレス変更が完了しました。"); window.location = "https://' . $config["domain"] . '/setting/"; </script>';

?>