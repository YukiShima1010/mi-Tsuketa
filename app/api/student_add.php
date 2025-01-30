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

    // 値を確認
    $name = isset($_POST["name"]) ? $_POST["name"] : false;
    $mailaddress = isset($_POST["email"]) ? $_POST["email"] : false;

    if (!$name || !$mailaddress) {
        // 値が正しくなかった場合
        $response["status"] = 501;
        $response["error_message"] = "POSTの値が不正です。";
    } else {
        // メールアドレスを検証
        if (!filter_var($mailaddress, FILTER_VALIDATE_EMAIL)) {
            // 値が正しくなかった場合
            $response["status"] = 500;
            $response["error_message"] = "正しいメールアドレスを指定してください。";
        } else {
            // メールアドレスが正しい
            try {
                // IDを生成
                $id = random_int(10000, 99999);

                // 保存
                $sql = "INSERT INTO students (id, name, mailaddress) VALUES (?, ?, ?);";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id, $name, $mailaddress]);

                // レスポンス
                $response["status"] = 200;
            } catch (PDOException $e) {
                // エラー表示(必要時以外はコメントアウト)
                // echo $e->getMessage();

                // エラーの原因がID衝突か確認
                if ($e->getCode() == 1062) {
                    $response["status"] = 500;
                    $response["error_message"] = "IDまたはメールアドレスが重複しました。再度登録ボタンを押してください。何度試しても同じエラーが発生する場合は違うメールアドレスを設定してください。";
                } else {
                    // その他の場合
                    $response["status"] = 500;
                    $response["error_message"] = "SQL実行中にエラーが発生しました。";
                }
            }
        }
    }
} else {
    // POSTでない
    $response["status"] = 500;
    $response["error_message"] = "POST形式のデータが見つかりませんでした。";
}

echo json_encode($response, JSON_PRETTY_PRINT);

?>