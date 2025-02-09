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

    // POSTがきちんと来ているか確認
    if (isset($_FILES["file"]) && isset($_POST["name"]) && isset($_POST["faceData"])) {
        // POSTされたファイルとデータを取得
        $file = $_FILES["file"];
        $fileName = $_POST["name"];
        $faceData = json_decode($_POST["faceData"], true);

        // faceDataを解析
        $faceDataIsNull = false;
        $descriptors = [];
        $expressions = [];

        foreach ($faceData as $row) {
            if ($row["descriptor"] == "null" || $row["expression"] == "null") {
                $faceDataIsNull = true;
                continue;
            }
            $descriptors[] = $row["descriptor"];
            $expressions[] = $row["expression"];
        }

        $jsonDescriptors = json_encode($descriptors);
        $jsonExpressions = json_encode($expressions);

        // ファイルサイズを確認
        $maxSize = 60 * 1024 * 1024; // 60MB

        if ($file["size"] > $maxSize) {
            // 60MB以上の画像だった場合
            $response["status"] = 500;
            $response["error_message"] = "画像の容量が大きすぎます。";
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }

        // ファイルが画像なのか拡張子から確認
        if (!in_array(strtolower(pathinfo($file["name"], PATHINFO_EXTENSION)), ["jpg", "jpeg", "png", "gif", "webp"])) {
            $response["status"] = 500;
            $response["error_message"] = "アップロードされたファイルが画像ではありません。";
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }

        // ファイルが画像なのかgetimagesizeから確認
        if (!getimagesize($file["tmp_name"])) {
            $response["status"] = 500;
            $response["error_message"] = "アップロードされたファイルが画像ではありません。";
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }

        switch ($file["type"]) {
            case "image/jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;
            case "image/png":
                $image = imagecreatefrompng($file["tmp_name"]);
                break;
            case "image/gif":
                $image = imagecreatefromgif($file["tmp_name"]);
                break;
            case "image/webp":
                $image = imagecreatefromwebp($file["tmp_name"]);
                break;
            default:
                $response["status"] = 500;
                $response["error_message"] = "アップロードされたファイルが画像ではありません。";
                echo json_encode($response, JSON_PRETTY_PRINT);
                exit;
        }

        // ファイル名(uuid)生成
        $uuid = uniqid("", true);

        // 保存するディレクトリ
        $uploadDir = "../../storage/img/";

        // 最高画質でPNG保存
        imagepng($image, $uploadDir . $uuid . ".png");

        // jpegが60kb以下になるまで画質下げ下げして保存
        $quality = 80;
        $maxSize = 60 * 1024;
        $jpegFilePath = $uploadDir . $uuid . ".jpg";

        do {
            imagejpeg($image, $jpegFilePath, $quality);
            $currentSize = filesize($jpegFilePath);

            if ($currentSize > $maxSize) {
                $quality -= 10;
            }
        } while ($currentSize > $maxSize && $quality > 10);

        imagedestroy($image);
        unlink($file["tmp_name"]);

        try {
            // データをDBに保存
            if ($faceDataIsNull) {
                $sql = "INSERT INTO photos (path, name) VALUES (?, ?);";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$uuid, $fileName]);
            } else {
                $sql = "INSERT INTO photos (path, name, face, expression) VALUES (?, ?, ?, ?);";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$uuid, $fileName, $jsonDescriptors, $jsonExpressions]);
            }

            // レスポンス
            $response["status"] = 200;
        } catch (PDOException $e) {
            // エラー表示(必要時以外はコメントアウト)
            // echo $e->getMessage();

            // その他の場合
            $response["status"] = 500;
            $response["error_message"] = "SQL実行中にエラーが発生しました。";
        }
    } else {
        // POSTのデータがおかしい
        $response["status"] = 500;
        $response["error_message"] = "POSTの値が不正です。";
    }
} else {
    // POSTでない
    $response["status"] = 500;
    $response["error_message"] = "POST形式のデータが見つかりませんでした。";
}

echo json_encode($response, JSON_PRETTY_PRINT);

?>
