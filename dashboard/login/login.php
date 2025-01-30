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

// GETの中身を取得(ある場合)
$url = isset($_GET["url"]) ? $_GET["url"] : "/dashboard";

// POSTがあるか確認
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // POSTじゃない
    http_response_code(400);
    header("Location: https://" . $config["domain"] . "/dashboard/login/?url=" . urlencode($url) . "&error=admin_login_error");
    exit;
}

// POSTの中身を取得
$id = isset($_POST["id"]) ? $_POST["id"] : false;
$password = isset($_POST["password"]) ? $_POST["password"] : false;
$recaptcha_token = isset($_POST["recaptcha_token"]) ? $_POST["recaptcha_token"] : false;

// reCAPTCHA認証
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $config["recaptcha_secret_key"] . "&response=" . $recaptcha_token . "&remoteip=" . $_SERVER["REMOTE_ADDR"]);

if (!$response) {
    // reCAPTCHAエラー
    http_response_code(500);
    include("../../error/500.php");
    exit;
}

// デコード
$recaptcha_response = json_decode($response);

// 結果確認
if (!$recaptcha_response->success) {
    // reCAPTCHAに失敗した場合
    include("../../error/recaptcha.php");
    exit;
} elseif ($recaptcha_response->score < 0.5) {
    // スコアが低い場合
    include("../../error/recaptcha.php");
    exit;
}

// POSTが正しいか
if (!$id || !$password) {
    // POSTにIDまたはパスワードがない
    http_response_code(400);
    header("Location: https://" . $config["domain"] . "/dashboard/login/?url=" . urlencode($url) . "&error=admin_login_error");
    exit;
}

// IDとパスワードのチェック
if ($id != $config["admin_id"] || !password_verify($password, $config["admin_password"])) {
    // 失敗した場合
    http_response_code(400);
    header("Location: https://" . $config["domain"] . "/dashboard/login/?url=" . urlencode($url) . "&error=admin_login_error");
    exit;
}

// セッション保存
$_SESSION["admin_login"] = $config["admin_id"];

// PHPMailer読み込み
require "../../app/libraries/PHPMailer/src/PHPMailer.php";
require "../../app/libraries/PHPMailer/src/SMTP.php";
require "../../app/libraries/PHPMailer/src/Exception.php";
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer(true);

// 学校の情報取得
$school_name = $config["school_name"];
$admin_id = $config["admin_id"];
$ipaddress = $_SERVER["REMOTE_ADDR"];
$user_agent = $_SERVER["HTTP_USER_AGENT"];
$now_time = date("Y/m/d H:i:s");

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
    $mail->addAddress($config["admin_mailaddress"]);

    // $mail->isHTML(true);
    $mail->isHTML(false);
    $mail->Subject = "【みーつけた】管理者ログイン通知";
    $mail->Body = "
■ このメールはシステムからの自動送信です ■

下記の通り、管理者のログインがありました。
身に覚えがない場合は即座に管理者IDとパスワードを変更してください。
管理者IDを変更すると全ての場所でログアウトされます。

- - - - -
管理者ID：
{$admin_id}
IPアドレス：
{$ipaddress}
ユーザーエージェント：
{$user_agent}
ログイン日時：
{$now_time}
- - - - -

▼ 注意事項 ▲
- 本メールに身に覚えがない場合はお手数ですが破棄していただきますようお願い申し上げます。

━━━━━━━━━━━━━━━━━━━━
{$school_name}「みーつけた」
━━━━━━━━━━━━━━━━━━━━
";

    // メールを送信
    $mail->send();

    // メッセージ表示
    if ($url == "null") {
        echo '<script> alert("ログインに成功しました。"); window.location.href = "https://' . $config["domain"] . '/dashboard/"; </script>';
    } else {
        echo '<script> alert("ログインに成功しました。"); window.location.href = "https://' . $config["domain"] . $url . '"; </script>';
    }
    exit;
} catch (Exception $e) {
    // メールの送信が失敗
    // echo $mail->ErrorInfo; // エラー表示(必要時以外コメントアウト)

    // メッセージ表示
    if ($url == "null") {
        echo '<script> alert("ログインに成功しました。"); window.location.href = "https://' . $config["domain"] . '/dashboard/"; </script>';
    } else {
        echo '<script> alert("ログインに成功しました。"); window.location.href = "https://' . $config["domain"] . $url . '"; </script>';
    }
    exit;
}

?>