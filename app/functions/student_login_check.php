<?php

// エラー表示(必要な時以外はコメントアウト)
// error_reporting(E_ALL);
// ini_set("display_errors", "1");

// 既にログインしてるか確認
function studentLoginCheck() {
    global $config;
    global $pdo;

    // セッションがあるか確認
    $student_id = isset($_SESSION["student_id"]) ? $_SESSION["student_id"] : false;
    if ($student_id != false) {
        // セッションがある場合
        try {
            // DBにIDが存在するか確認
            $sql = "SELECT COUNT(*) FROM students WHERE id = :student_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result === false) {
                // データベースエラー
                $_SESSION = array();
                http_response_code(500);
                include(__DIR__ . "/../../error/database.php");
                exit;
            } elseif ($result == "0") {
                // DBにIDがなかった
                $_SESSION = array();
            } else {
                // ログインしていた
                echo '<script> if (window.confirm("既にログインしています。ログアウトしますか？")) { window.location.href = "https://' . $config["domain"] . '/logout/?msg=true"; } else { window.location.href = "https://' . $config["domain"] . '/"; } </script>';
                exit;
            }
        } catch (PDOException $e) {
            // エラー表示(必要時以外はコメントアウト)
            // echo $e->getMessage();
            $_SESSION = array();
            http_response_code(500);
            include(__DIR__ . "/../../error/database.php");
            exit;
        }
    }
}

?>