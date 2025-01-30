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

// GETの中身を取得(ある場合)
$url = isset($_GET["url"]) ? $_GET["url"] : "none";

// POSTがあるか確認
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // POSTじゃない
    $response_code["status"] = 500;
    $response_code["error_code"] = "is_not_post";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// POSTを取得
$post_data = json_decode(file_get_contents("php://input"), true);
$mailaddress = isset($post_data["email"]) ? $post_data["email"] : false;
$recaptcha_token = isset($post_data["recaptcha_token"]) ? $post_data["recaptcha_token"] : false;

// reCAPTCHA認証
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $config["recaptcha_secret_key"] . "&response=" . $recaptcha_token . "&remoteip=" . $_SERVER["REMOTE_ADDR"]);

if (!$response) {
    // reCAPTCHAエラー
    $response_code["status"] = 500;
    $response_code["error_code"] = "recaptcha_error";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// デコード
$recaptcha_response = json_decode($response);

// 結果確認
if (!$recaptcha_response->success) {
    // reCAPTCHAに失敗した場合
    $response_code["status"] = 500;
    $response_code["error_code"] = "recaptcha_error";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
} elseif ($recaptcha_response->score < 0.5) {
    // スコアが低い場合
    $response_code["status"] = 500;
    $response_code["error_code"] = "recaptcha_error";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// POSTにメールアドレスがあるか
if (!$mailaddress) {
    // POSTにメールアドレスがない
    $response_code["status"] = 500;
    $response_code["error_code"] = "email_is_null";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// メールアドレスの形式チェック
if (!filter_var($mailaddress, FILTER_VALIDATE_EMAIL)) {
    // メールアドレスが適切でない
    $response_code["status"] = 500;
    $response_code["error_code"] = "email_is_invalid";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// メールアドレスが存在するか確認
try {
    $sql = "SELECT id FROM students WHERE mailaddress = :mailaddress;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":mailaddress", $mailaddress, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($result === false) {
        // データベースエラー
        $response_code["status"] = 500;
        $response_code["error_code"] = "database_error";
        echo json_encode($response_code, JSON_PRETTY_PRINT);
        exit;
    } elseif (count($result) == 0) {
        // メールアドレスが存在しなかった
        $response_code["status"] = 500;
        $response_code["error_code"] = "email_is_not_exist";
        echo json_encode($response_code, JSON_PRETTY_PRINT);
        exit;
    } elseif (count($result) == 1) {
        // メールアドレスが存在した
        $mailaddress_tied_id = $result[0];
    } else {
        $response_code["status"] = 500;
        $response_code["error_code"] = "database_error";
        echo json_encode($response_code, JSON_PRETTY_PRINT);
        exit;
    }
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    $response_code["status"] = 500;
    $response_code["error_code"] = "database_error";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// 5分以内に作成されたトークンがないか確認
try {
    $sql = "SELECT COUNT(*) AS token_count FROM mail_token WHERE student_id = :mailaddress_tied_id AND is_verified = 0 AND created_at >= NOW() - INTERVAL 5 MINUTE;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":mailaddress_tied_id", $mailaddress_tied_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        // データベースエラー
        $response_code["status"] = 500;
        $response_code["error_code"] = "database_error";
        echo json_encode($response_code, JSON_PRETTY_PRINT);
        exit;
    } elseif ($result["token_count"] > 0) {
        // 5分以内に作成したトークンがある場合
        // echo '<script> alert("5分以内に送信されたログインURLがあります。\nメールボックスをご確認ください。"); window.location.href = "https://' . $config["domain"] . '/login/"; </script>';
        // exit;
        $response_code["status"] = 500;
        $response_code["error_code"] = "mail_rate_error";
        echo json_encode($response_code, JSON_PRETTY_PRINT);
        exit;
    }
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    $response_code["status"] = 500;
    $response_code["error_code"] = "database_error";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// 認証用Tokenを作成
$token = bin2hex(random_bytes(16));

// Tokenを認証用DBに保存
try {
    $stmt = $pdo->prepare("INSERT INTO mail_token (student_id, token, ipaddress, redirect_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$mailaddress_tied_id, $token, $_SERVER["REMOTE_ADDR"], $url]);
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    $response_code["status"] = 500;
    $response_code["error_code"] = "database_error";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

// PHPMailer読み込み
require "../../app/libraries/PHPMailer/src/PHPMailer.php";
require "../../app/libraries/PHPMailer/src/SMTP.php";
require "../../app/libraries/PHPMailer/src/Exception.php";
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer(true);

// 学校の情報取得
$school_name = $config["school_name"];
$school_tel = $config["school_tel"];
$school_email = $config["school_email"];

$domain = $config["domain"];

// ログの文字化け対策
mb_language("uni");
mb_internal_encoding("UTF-8");

try {
    // ログ出力
    $mail->SMTPDebug = 0; // テスト"2", 本番"0"

    // メール送信準備1
    $mail->isSMTP();
    $mail->Host = $config["mail_server"];
    $mail->SMTPAuth = true;
    $mail->Username = $config["mail_user"];
    $mail->Password = $config["mail_password"];
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;
    $mail->CharSet = "UTF-8";
    $mail->Encoding = "base64";

    // メール送信準備2
    $mail->setFrom($config["mail_address"], $school_name);
    $mail->addAddress($mailaddress);

    // $mail->isHTML(true);
    $mail->isHTML(false);
    $mail->Subject = "【みーつけた】ログインURLのご案内";
    $mail->Body = "
■ このメールはシステムからの自動送信です ■

日頃より、みーつけたをご利用いただき、誠にありがとうございます。

この度、ログインの要求がございましたので、ログインURLをご案内させていただきます。
下記のリンクより、ログインを完了させていただきますよう、お願い申し上げます。

- - - - -
URL:
https://{$domain}/login/verify/?token={$token}
- - - - -

▼ 注意事項 ▲
- 本メールに身に覚えがない場合はお手数ですが破棄していただきますようお願い申し上げます。
- このメールが届いてから15分以内にログインを完了させてください。
- 期限を過ぎますと、リンクが無効となりますので、ご了承ください。

ご不明な点がございましたら、下記連絡先まで連絡ください。

━━━━━━━━━━━━━━━━━━━━
{$school_name}
TEL：{$school_tel}
E-Mail：{$school_email}
━━━━━━━━━━━━━━━━━━━━
";

    // メールを送信
    $mail->send();

    $response_code["status"] = 200;
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
} catch (Exception $e) {
    // メールの送信が失敗
    // echo $mail->ErrorInfo; // エラー表示(必要時以外コメントアウト)]

    try {
        // tokenを無効に
        $sql = "UPDATE mail_token SET is_verified = 2 WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->rowCount();

        if ($result === false) {
            // データベースエラー
            $response_code["status"] = 500;
            $response_code["error_code"] = "database_error";
            echo json_encode($response_code, JSON_PRETTY_PRINT);
            exit;
        }
    } catch (PDOException $e) {
        // エラー表示(必要時以外はコメントアウト)
        // echo $e->getMessage();
        $response_code["status"] = 500;
        $response_code["error_code"] = "database_error";
        echo json_encode($response_code, JSON_PRETTY_PRINT);
        exit;
    }

    $response_code["status"] = 500;
    $response_code["error_code"] = "error_error";
    echo json_encode($response_code, JSON_PRETTY_PRINT);
    exit;
}

?>