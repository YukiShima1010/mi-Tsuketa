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

// ユーザーログイン確認
require "../functions/student_id_check.php";
$student_id = studentIdCheck();

// POSTがあるか確認
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // POSTじゃない
    http_response_code(400);
    include("../../error/400.php");
    exit;
}

// POSTの中身を取得
$mailaddress = isset($_POST["email"]) ? $_POST["email"] : false;

// POSTにメールアドレスがあるか
if (!$mailaddress) {
    // POSTにメールアドレスがない
    http_response_code(400);
    include("../error/400.php");
    exit;
}

// メールアドレスの形式チェック
if (!filter_var($mailaddress, FILTER_VALIDATE_EMAIL)) {
    // メールアドレスが適切でない
    http_response_code(400);
    include("../../error/400.php");
    exit;
}

// 5分以内に作成されたトークンがないか確認
try {
    $sql = "SELECT COUNT(*) AS token_count FROM mail_token WHERE student_id = :mailaddress_tied_id AND is_verified = 3 AND created_at >= NOW() - INTERVAL 5 MINUTE;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":mailaddress_tied_id", $mailaddress_tied_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        // データベースエラー
        http_response_code(500);
        include("../../error/database.php");
        exit;
    } elseif ($result["token_count"] > 0) {
        // 5分以内に作成したトークンがある場合
        echo '<script> alert("5分以内に送信された確認メールがあります。\nメールボックスをご確認ください。"); window.location.href = "https://' . $config["domain"] . '/setting/"; </script>';
        exit;
    }
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    http_response_code(500);
    include("../../error/database.php");
    exit;
}

// 認証用Tokenを作成
$token = bin2hex(random_bytes(16));

// is_verifiedを3にする
$is_verified = 3;
// 0=認証待機、1=認証完了、2=認証失敗、3=変更待機、4=変更完了、5=変更失敗

// Tokenを認証用DBに保存
try {
    $stmt = $pdo->prepare("INSERT INTO mail_token (student_id, token, ipaddress, is_verified, mailaddress) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $token, $_SERVER["REMOTE_ADDR"], $is_verified, $mailaddress]);
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    http_response_code(500);
    include("../../error/database.php");
    exit;
}

// PHPMailer読み込み
require "../libraries/PHPMailer/src/PHPMailer.php";
require "../libraries/PHPMailer/src/SMTP.php";
require "../libraries/PHPMailer/src/Exception.php";
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
    $mail->Subject = "【みーつけた】メールアドレス変更確認のお知らせ";
    $mail->Body = "
■ このメールはシステムからの自動送信です ■

日頃より、みーつけたをご利用いただき、誠にありがとうございます。

メールアドレス変更の要求を受け付けました。
下記のリンクをクリックして、メールアドレスの変更を完了してください。

- - - - -
URL:
https://{$domain}/app/api/mailaddress_verify.php/?token={$token}
- - - - -

▼ 注意事項 ▲
- 本メールに身に覚えがない場合はお手数ですが破棄していただきますようお願い申し上げます。
- このメールが届いてから15分以内に変更を完了させてください。
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
    echo '<script> alert("指定されたメールアドレスに確認メールを送信しました。\nメールボックスをご確認ください。"); window.location.href = "https://' . $config["domain"] . '/setting/"; </script>';
    exit;
} catch (Exception $e) {
    // メールの送信が失敗
    // echo $mail->ErrorInfo; // エラー表示(必要時以外コメントアウト)

    try {
        // tokenを無効に
        $sql = "UPDATE mail_token SET is_verified = 5 WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->rowCount();

        if ($result === false) {
            // データベースエラー
            http_response_code(500);
            include("../error/database.php");
            exit;
        }
    } catch (PDOException $e) {
        // エラー表示(必要時以外はコメントアウト)
        // echo $e->getMessage();
        http_response_code(500);
        include("../error/database.php");
        exit;
    }

    // エラーを表示
    echo '<script> alert("確認メールの送信に失敗しました。\n時間が経ってから再度お試しください。"); window.location.href = "https://' . $config["domain"] . '/setting/"; </script>';
    exit;
}

?>
