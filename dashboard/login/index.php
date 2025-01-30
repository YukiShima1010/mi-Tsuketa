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

// セッションからログイン情報を取得
$admin_login = isset($_SESSION["admin_login"]) ? $_SESSION["admin_login"] : false;
if ($admin_login == $config["admin_id"]) {
    // ログインしていた場合
    echo '<script> if (window.confirm("既にログインしています。ログアウトしますか？")) { window.location.href = "https://' . $config["domain"] . '/logout/?msg=true"; } else { window.location.href = "https://' . $config["domain"] . '/dashboard/"; } </script>';
    exit;
} elseif ($admin_login != false && $admin_login != $config["admin_id"]) {
    // セッションの値がtureでもnullでもなかった場合
    $_SESSION = array();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns#">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン | みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>/dashboard/login"/>
    <meta property="og:type" content="article"/>
    <meta property="og:title" content="管理者ログイン - みーつけた"/>
    <meta property="og:description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。"/>
    <meta property="og:site_name" content="みーつけた"/>
    <meta property="og:image" content="https://<?php echo $config["domain"]; ?>/assets/img/site-share.png"/>
    <meta property="og:locale" content="ja_JP"/>

    <!-- CSS -->
    <link rel="stylesheet" href="https://<?php echo $config["domain"]; ?>/assets/css/style-login.css" type="text/css">

    <!-- JSにドメイン・reCAPTCHAサイトキー・GETを受け渡し -->
    <script> var domain = "<?php echo $config["domain"]; ?>"; var recaptcha_site_key = "<?php echo $config["recaptcha_site_key"]; ?>"; var error = "<?php echo isset($_GET["error"]) ? $_GET["error"] : "null" ?>"; </script>
</head>
<body>

    <!-- ヘッダー -->
    <header>
        <a href="https://<?php echo $config["domain"]; ?>"><h1>みーつけた</h1></a>
    </header>

    <!-- コンテンツ -->
    <div class="content" style="background: linear-gradient(180deg, #6891b3, rgb(24, 134, 129));">

        <!-- エラーメッセージ -->
        <div id="error-message" class="error"></div>

        <!-- コンテナ -->
        <div class="container">
            <h1><i class="fa-solid fa-user-tie" style="color: #ffffff;"></i> 管理者用ログイン</h1>
            <form action="login.php<?php echo isset($_GET["url"]) ? "/?url=" . $_GET["url"] : ""; ?>" method="post">
                <label for="id"><i class="fa-solid fa-id-card" style="color: #ffffff;"></i> 管理者コード</label>
                <input type="text" id="id" name="id" required autocomplete="username">
                <label for="password"><i class="fa-solid fa-key" style="color: #ffffff;"></i> パスワード</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <input type="hidden" name="recaptcha_token" id="recaptchaResponse">
                <button type="submit"><i class="fa-solid fa-check" style="color: #72b368;"></i> ログイン</button>
            </form>
            <p>保護者の方は<a href="https://<?php echo $config["domain"]; ?>/login">こちら</a>からログイン</p>
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

    <!-- dash-login.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/dash-login.js"></script>

</body>
</html>