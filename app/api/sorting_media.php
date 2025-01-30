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

    if (!is_array($post_data)) {
        // 値が空だったら
        $response["status"] = 500;
        $response["error_message"] = "POSTの値が不正です。";
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    // var_dump($post_data);

    // 更新処理
    foreach ($post_data as $id => $faces) {
        $sql = "UPDATE photos SET detection = :detection WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $faces_json = json_encode($faces);
        // var_dump($faces_json);
        $stmt->bindParam(":detection", $faces_json, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        // 実行
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            // エラー表示(必要時以外はコメントアウト)
            // echo $e->getMessage();

            $response["status"] = 500;
            $response["error_message"] = "実行中にエラーが発生しました。";
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
    }
    $response["status"] = 200;
}

echo json_encode($response, JSON_PRETTY_PRINT);

?>