<?php

// エラー表示(必要な時以外はコメントアウト)
// error_reporting(E_ALL);
// ini_set("display_errors", "1");

// ヘッダー(ここじゃなくていいとは思う)
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

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

    // POSTを取得
    $post_data = json_decode(file_get_contents("php://input"), true);

    // 値を確認
    $id = isset($post_data["student_id"]) ? $post_data["student_id"] : false;

    if (!$id || !is_array($id)) {
        // 値が正しくなかった場合
        $response["status"] = 500;
        $response["error_message"] = "POSTの値が不正です。";
    } else {
        try {
            $select_ids = implode(", ", array_fill(0, count($id), "?"));

            // 使用するSQLを選ぶ
            $select = isset($_POST["select"]) ? $_POST["select"] : false;
            if (!$select || $select == 1) {
                // 選択中
                $sql = "DELETE FROM photos WHERE id IN ($select_ids)";
            } elseif ($_POST["select"] == 2) {
                // 選択以外
                $sql = "DELETE FROM photos WHERE id NOT IN ($select_ids)";
            } else {
                $response["status"] = 500;
                $response["error_message"] = "POSTの値が不正です。";
                echo json_encode($response, JSON_PRETTY_PRINT);
                exit;
            }

            // SQL実行
            $stmt = $pdo->prepare($sql);
            $stmt->execute($id);

            if ($stmt->rowCount() > 0) {
                // 1つでも消せた場合
                // echo $stmt->rowCount(); // 個数表示
                $response["status"] = 200;
            } else {
                // 何の成果も得られなかった場合
                $response["status"] = 500;
                $response["error_message"] = "指定されたIDが一つも見つかりませんでした。";
            }
        } catch (PDOException $e) {
            // エラー表示(必要時以外はコメントアウト)
            // echo $e->getMessage();
            $response["status"] = 500;
            $response["error_message"] = "SQL実行中にエラーが発生しました。";
        }
    }
} else {
    // POSTでない
    $response["status"] = 500;
    $response["error_message"] = "POST形式のデータが見つかりませんでした。";
}

echo json_encode($response, JSON_PRETTY_PRINT);

?>