<?php

// エラー表示(必要な時以外はコメントアウト)
// error_reporting(E_ALL);
// ini_set("display_errors", "1");

// セッション開始
session_start();

// config読み込み
require_once "../config/config.php";

// データベース接続
require_once "../config/database.php";

// ユーザーログイン確認
require "../app/functions/student_login_check.php";
studentLoginCheck();

?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns#">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>/login"/>
    <meta property="og:type" content="article"/>
    <meta property="og:title" content="ログイン - みーつけた"/>
    <meta property="og:description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。"/>
    <meta property="og:site_name" content="みーつけた"/>
    <meta property="og:image" content="https://<?php echo $config["domain"]; ?>/assets/img/site-share.png"/>
    <meta property="og:locale" content="ja_JP"/>

    <!-- CSS -->
    <link rel="stylesheet" href="https://<?php echo $config["domain"]; ?>/assets/css/style-login.css" type="text/css">

    <!-- JSにドメイン・reCAPTCHAサイトキー・GETを受け渡し -->
    <script> var domain = "<?php echo $config["domain"]; ?>"; var recaptcha_site_key = "<?php echo $config["recaptcha_site_key"]; ?>"; var error = "<?php echo isset($_GET["error"]) ? $_GET["error"] : "null" ?>"; var url = "<?php $_GET["url"] ?>"; </script>
</head>
<body>

    <!-- ヘッダー -->
    <header>
        <a href="https://<?php echo $config["domain"]; ?>"><h1>みーつけた</h1></a>
    </header>

    <!-- コンテンツ -->
    <div class="content">

        <!-- エラーメッセージ -->
        <div id="error-message" class="error"></div>

        <!-- コンテナ -->
        <div class="container">
            <h1><i class="fa-solid fa-right-to-bracket" style="color: #ffffff;"></i> 生徒用ログイン</h1>
            <form id="loginForm">
                <label for="email"><i class="fa-solid fa-at" style="color: #ffffff;"></i> メールアドレス</label>
                <input type="email" id="email" name="email" required>
                <input type="hidden" name="recaptcha_token" id="recaptchaResponse">
                <button type="submit"><i class="fa-solid fa-check" style="color: #72b368;"></i> メールアドレスでログイン</button>
            </form>
            <hr>
            <button><i class="fa-solid fa-key" style="color: #72b368;" onclick="passkey()"></i> かんたんログイン</button>
            <p>管理者の方は<a href="https://<?php echo $config["domain"]; ?>/dashboard/login">こちら</a>からログイン</p>
        </div> <!-- container -->

    </div> <!-- contents -->

    <!-- フッター -->
    <footer>
        <p>&copy; 2025 YukiShima</p>
    </footer>

    <!-- FontAwesome -->
    <script type="text/javascript" charset="UTF-8" src="https://kit.fontawesome.com/91559ddbec.js" crossorigin="anonymous"></script>

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $config["recaptcha_site_key"]; ?>"></script>

    <!-- student-login.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/student-login.js"></script>

</body>
</html>