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

// 変更する生徒のidをGETから取得
$student_id = isset($_GET["student"]) ? $_GET["student"] : false;
if (!$student_id) {
    // なかった場合
    http_response_code(400);
    header("Location: https://" . $config["domain"] . "/dashboard");
    exit;
} else {
    // 配列に変換
    $student_id = explode(",", $student_id);
}

// DBから生徒の情報を取得
try {
    // SQL準備
    $student_id_array = array_map("intval", $student_id);
    $placeholders = implode(",", array_fill(0, count($student_id_array), "?"));
    $sql = "SELECT id, name, mailaddress FROM students WHERE id IN ($placeholders)";

    // 実行
    $stmt = $pdo->prepare($sql);
    $stmt->execute($student_id_array);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result === false) {
        // 生徒がいなかった場合
        http_response_code(400);
        header("Location: https://" . $config["domain"] . "/dashboard");
        exit;
    }
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    //echo $e->getMessage();
    http_response_code(500);
    include("../../error/database.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns#">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生徒情報変更 | みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>/dashboard/student-change"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="顔情報変更 - みーつけた"/>
    <meta property="og:description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。"/>
    <meta property="og:site_name" content="みーつけた"/>
    <meta property="og:image" content="https://<?php echo $config["domain"]; ?>/assets/img/site-share.png"/>
    <meta property="og:locale" content="ja_JP"/>

    <!-- CSS -->
    <link rel="stylesheet" href="https://<?php echo $config["domain"]; ?>/assets/css/style-dash.css" type="text/css">

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

            <!-- タイトル -->
            <h1><i class="fa-solid fa-user-pen" style="color: #188681;"></i> 生徒情報変更</h1>

            <!-- 変更フォーム -->
            <form class="student-change" id="studentChangeForm">
                <?php

                foreach ($result as $row) {
                    echo "<h2>" . $row["id"] . "</h2>";
                    echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
                    echo '<p><i class="fa-solid fa-pencil" style="color: #188681;"></i> 氏名</p>';
                    echo '<input type="text" id="name" name="name" value="' . $row["name"] . '" placeholder="' . $row["name"] . '" required>';
                    echo '<p><i class="fa-solid fa-at" style="color: #188681;"></i> メールアドレス</p>';
                    echo '<input type="email" id="email" name="email" value="' . $row["mailaddress"] . '" placeholder="' . $row["mailaddress"] . '" required>';
                }

                ?>
                <button id="form-button"><i class="fa-solid fa-rotate-right"></i> 変更</button>
            </form>

        </div> <!-- container -->

    </div> <!-- contents -->

    <!-- フッター -->
    <footer>
        <p>&copy; 2025 YukiShima</p>
    </footer>

    <!-- FontAwesome -->
    <script type="text/javascript" charset="UTF-8" src="https://kit.fontawesome.com/91559ddbec.js" crossorigin="anonymous"></script>

    <!-- dash-main.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/dash-main.js"></script>

    <!-- dash-student-change.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/dash-student-change.js"></script>

</body>
</html>