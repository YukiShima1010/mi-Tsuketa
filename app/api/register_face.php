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

// ログイン情報取得
$admin_login = isset($_SESSION["admin_login"]) ? $_SESSION["admin_login"] : false;
if (!$admin_login) {
    // ログインしてなかったら飛ばす
    http_response_code(401);
    header("Location: https://" . $config["domain"] . "/dashboard/login/?url=" . urlencode($_SERVER["REQUEST_URI"]));
    exit;
} elseif ($admin_login != $config["admin_id"]) {
    // admin_idが違うならログアウトして飛ばす
    $_SESSION = array();
    http_response_code(401);
    header("Location: https://" . $config["domain"] . "/dashboard/login/?url=" . urlencode($_SERVER["REQUEST_URI"]));
    exit;
}

// POSTなのか確認
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // POSTの値を取得
    $student_id = isset($_POST["student_id"]) ? $_POST["student_id"] : false;
    $face_data = isset($_POST["face_data"]) ? $_POST["face_data"] : false;

    // 値を確認
    if ($student_id != false && $face_data != false) {

        // faceをjsonに
        $json_face_data = json_encode($face_data);

        try {
            // 顔情報を更新(登録)
            $sql = "UPDATE students SET face = :face WHERE id = :student_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":face", $json_face_data, PDO::PARAM_STR);
            $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->execute()) {
                // 成功
                $response["status"] = 200;
            } else {
                // 失敗
                $response["status"] = 500;
                $response["error_message"] = "SQL実行に失敗しました。";
            }
        } catch (PDOException $e) {
            // エラー表示(必要時以外はコメントアウト)
            // echo $e->getMessage();
            $response["status"] = 500;
            $response["error_message"] = "SQL実行中にエラーが発生しました。";
        }
    } else {
        // student_id or face_dataのどちらかの値がない
        $response["status"] = 500;
        $response["error_message"] = "POSTの値が不正です。";
    }
} else {
    // POSTでない
    $response["status"] = 500;
    $response["error_message"] = "POST形式のデータが見つかりませんでした。";
}

// ヘッダー準備
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// 表示
echo json_encode($response, JSON_PRETTY_PRINT);

?>