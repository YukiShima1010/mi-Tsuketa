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

// 生徒ログイン確認
function studentCheck() {
    global $pdo;

    $student_id = isset($_SESSION["student_id"]) ? $_SESSION["student_id"] : false;
    if (!$student_id) {
        return false;
    }

    // ユーザー存在確認
    try {
        $sql = "SELECT COUNT(*) FROM students WHERE id = :student_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchColumn();

        if ($result === false) {
            // データベースエラー
            http_response_code(500);
            include("../../error/database.php");
            exit;
        } elseif ($result == "0") {
            // ユーザーが存在しなかった
            $_SESSION = array(); // 不正なセッションを一旦全削除
            return false;
        } else {
            // ユーザーの存在が確認できたら
            return true;
        }
    } catch (PDOException $e) {
        // エラー表示(必要時以外はコメントアウト)
        // echo $e->getMessage();
        http_response_code(500);
        include(__DIR__ . "/../../error/database.php");
        exit;
    }
}

// 管理者ログイン確認
function adminCheck() {
    global $config;

    // ログイン情報取得
    $admin_login = isset($_SESSION["admin_login"]) ? $_SESSION["admin_login"] : false;
    if (!$admin_login) {
        // ログインしていない
        return false;
    } elseif ($admin_login != $config["admin_id"]) {
        // admin_idが違う
        $_SESSION = array(); // セッション削除
        return false;
    } else {
        return true;
    }
}

// ログイン確認
if (!studentCheck()) {
    if (!adminCheck()) {
        // 管理者・生徒どちらとでもログインしていなかった場合
        http_response_code(401);
        include("../../error/401.php");
        exit;
    }
}

// GET受信
$file_id = isset($_GET["id"]) ? basename($_GET["id"]) : null; // id(無ければnull)
$file_type = isset($_GET["type"]) ? $_GET["type"] : "small"; // type(large or small)

// idがnullか
if (!$file_id) {
    include("../../error/404.php");
    http_response_code(404);
    exit;
}

// ファイルパス
$file_extension = ($file_type == "large") ? ".png" : ".jpg";
$file_path = "../../storage/img/" . $file_id . $file_extension;

// ファイルが存在するのか
if (!file_exists($file_path)) {
    http_response_code(404);
    include("../../error/404.php");
    exit;
}

// ファイル出力
ob_clean();
$mime_type = mime_content_type($file_path);
header("Content-Type: " . $mime_type);
readfile($file_path);

?>
