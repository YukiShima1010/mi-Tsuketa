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
require "../app/functions/student_id_check.php";
$student_id = studentIdCheck();

// 現在のメールアドレスを取得
try {
    $sql = "SELECT id, mailaddress, name FROM students WHERE id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        // データベースエラー
        http_response_code(500);
        include("../error/database.php");
        exit;
    }

    // メールアドレスを格納
    $mailaddress = $result["mailaddress"];

    // 氏名を格納
    $student_name = $result["name"];

    if ($mailaddress != null) {
        // mail@domain.com → m****@domain.comに変換
        [$address, $domain] = explode("@", $mailaddress);
        $mailaddress = substr($address, 0, 1) . "****@" . $domain;
    } else {
        $mailaddress = "null";
    }
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
    http_response_code(500);
    include("../error/database.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns#">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設定 | みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>/setting"/>
    <meta property="og:type" content="article"/>
    <meta property="og:title" content="設定 - みーつけた"/>
    <meta property="og:description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。"/>
    <meta property="og:site_name" content="みーつけた"/>
    <meta property="og:image" content="https://<?php echo $config["domain"]; ?>/assets/img/site-share.png"/>
    <meta property="og:locale" content="ja_JP"/>

    <!-- CSS -->
    <link rel="stylesheet" href="https://<?php echo $config["domain"]; ?>/assets/css/style.css" type="text/css">

    <!-- JSにドメインを受け渡し -->
    <script> var domain = "<?php echo $config["domain"]; ?>"; </script>
</head>
<body>

    <!-- ヘッダー -->
    <header>
        <a href="https://<?php echo $config["domain"]; ?>"><h1>みーつけた</h1></a>
        <nav class="desktop-nav">
            <div class="dropdown">
                <button class="dropdown-button">メニュー</button>
                <div class="dropdown-content">
                    <a href="https://<?php echo $config["domain"]; ?>/">ホーム</a>
                    <a href="https://<?php echo $config["domain"]; ?>/setting">設定</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropdown-button" onclick="logout()">ログアウト</button>
            </div>
        </nav>
        <div class="hamburger-menu" onclick="headerMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav class="side-menu">
            <ul>
                <li><a href="https://<?php echo $config["domain"]; ?>/">ホーム</a></li>
                <li><a href="https://<?php echo $config["domain"]; ?>/setting">設定</a></li>
                <li><a href="#" onclick="logout()">ログアウト</a></li>
            </ul>
        </nav>
    </header>

    <!-- コンテンツ -->
    <div class="content">

        <!-- コンテナ -->
        <div class="container">

            <!-- メールアドレス -->
            <p><i class="fa-solid fa-at" style="color: #188681;"></i> <?php if ( $mailaddress == "null" ) { echo "メールアドレスを登録"; } else { echo "メールアドレスを変更"; } ?></p>
            <form action="https://<?php echo $config["domain"]; ?>/app/api/mailaddress_change.php" method="POST">
                <input type="email" id="email" name="email" placeholder="<?php if ( $mailaddress == "null" ) { echo "入力してください"; } else { echo $mailaddress; } ?>" required>
                <button type="submit"><?php if ( $mailaddress == "null" ) { echo '<i class="fa-solid fa-plus"></i> 登録'; } else { echo '<i class="fa-solid fa-rotate-right"></i> 変更'; } ?></button>
            </form>

            <!-- パスキー -->
            <p><i class="fa-solid fa-key" style="color: #188681;"></i> <?php echo "かんたんログインを設定(パスキー)"; ?></p>
            <button class="passkey-button" onclick="passkey()"><?php echo '<i class="fa-solid fa-plus"></i> 設定'; ?></button>

        </div> <!-- container -->

    </div> <!-- contents -->

    <!-- フッター -->
    <footer>
        <p>&copy; 2025 YukiShima</p>
    </footer>

    <!-- FontAwesome -->
    <script type="text/javascript" charset="UTF-8" src="https://kit.fontawesome.com/91559ddbec.js" crossorigin="anonymous"></script>

    <!-- student-setting.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/student-setting.js"></script>

</body>
</html>