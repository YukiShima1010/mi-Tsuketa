<?php

// エラー表示(必要な時以外はコメントアウト)
// error_reporting(E_ALL);
// ini_set("display_errors", "1");

// ユーザーIDをセッションから取得し、DBに存在するか確認
function studentIdCheck() {
    global $config;
    global $pdo;

    // ログイン情報取得
    $student_id = isset($_SESSION["student_id"]) ? $_SESSION["student_id"] : false;
    if (!$student_id) {
        http_response_code(401);
        header("Location: https://" . $config["domain"] . "/login/?url=" . urlencode($_SERVER["REQUEST_URI"]));
        exit;
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
            include(__DIR__ . "/../../error/database.php");
            exit;
        } elseif ($result == "0") {
            // ユーザーが存在しなかった
            $_SESSION = array(); // 不正なセッションを一旦全削除
            http_response_code(401);
            header("Location: https://" . $config["domain"] . "/login/?url=" . urlencode($_SERVER["REQUEST_URI"]));
            exit;
        } else {
            // ユーザーの存在が確認できたら
            return $student_id;
        }
    } catch (PDOException $e) {
        // エラー表示(必要時以外はコメントアウト)
        //echo $e->getMessage();
        http_response_code(500);
        include(__DIR__ . "/../../error/database.php");
        exit;
    }
}

?>