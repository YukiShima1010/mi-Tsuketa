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

    try {
        // 変更を保存
        $sql = "UPDATE students SET name = ?, mailaddress = ? WHERE id = ?";
        $pdo->beginTransaction();
        $stmt = $pdo->prepare($sql);

        foreach ($post_data as $student) {
            $stmt->execute([$student["name"], $student["email"], $student["id"]]);
        }

        // コミット
        $pdo->commit();

        $response["status"] = 200;
    } catch (PDOException $e) {
        // エラー表示(必要時以外はコメントアウト)
        // echo $e->getMessage();

        // ロールバック
        $pdo->rollBack();

        $response["status"] = 500;
        $response["error_message"] = "SQL実行中にエラーが発生しました。";
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);

?>