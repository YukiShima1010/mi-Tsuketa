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

// 登録/変更する生徒のidをGETから取得
$student_id = isset($_GET["student"]) ? $_GET["student"] : false;
if (!$student_id) {
    // なかった場合
    http_response_code(400);
    header("Location: https://" . $config["domain"] . "/dashboard");
    exit;
}

// idが存在するか確認
try {
    $sql = "SELECT name, face FROM students WHERE id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        // なかった場合
        http_response_code(400);
        header("Location: https://" . $config["domain"] . "/dashboard");
        exit;
    }

    // 氏名を格納
    $student_name = $result["name"];

    // 顔が登録されているか
    $student_face = $result["face"] != null ? true : false;
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
    <title>顔情報登録 | みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>/dashboard/register-face"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="顔情報登録 - みーつけた"/>
    <meta property="og:description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。"/>
    <meta property="og:site_name" content="みーつけた"/>
    <meta property="og:image" content="https://<?php echo $config["domain"]; ?>/assets/img/site-share.png"/>
    <meta property="og:locale" content="ja_JP"/>

    <!-- CSS -->
    <link rel="stylesheet" href="https://<?php echo $config["domain"]; ?>/assets/css/style-dash.css" type="text/css">

    <!-- Face-api -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <!-- JSにドメインを受け渡し -->
    <script> var domain = "<?php echo $config["domain"]; ?>"; var student_id = "<?php echo $student_id; ?>"; </script>
</head>
<body>

    <!-- ヘッダー -->
    <header>
        <a href="https://<?php echo $config["domain"]; ?>"><h1>みーつけた</h1></a>
        <nav class="desktop-nav">
            <div class="dropdown">
                <button class="dropdown-button">メニュー</button>
                <div class="dropdown-content">
                    <a href="https://<?php echo $config["domain"]; ?>/dashboard/">生徒管理</a>
                    <a href="https://<?php echo $config["domain"]; ?>/dashboard/student-add/">生徒を追加</a>
                    <a href="https://<?php echo $config["domain"]; ?>/dashboard/images/">画像管理</a>
                    <a href="https://<?php echo $config["domain"]; ?>/dashboard/upload/">画像をアップロード</a>
                    <a href="https://<?php echo $config["domain"]; ?>/dashboard/sorting/">写真振り分け</a>
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
                <li><a href="https://<?php echo $config["domain"]; ?>/dashboard/">生徒管理</a></li>
                <li><a href="https://<?php echo $config["domain"]; ?>/dashboard/student-add/">生徒を追加</a></li>
                <li><a href="https://<?php echo $config["domain"]; ?>/dashboard/images/">画像管理</a></li>
                <li><a href="https://<?php echo $config["domain"]; ?>/dashboard/upload/">画像をアップロード</a></li>
                <li><a href="https://<?php echo $config["domain"]; ?>/dashboard/sorting/">写真振り分け</a></li>
                <li><a href="#" onclick="logout()">ログアウト</a></li>
            </ul>
        </nav>
    </header>

    <!-- コンテンツ -->
    <div class="content">

        <!-- コンテナ -->
        <div class="container">

            <!-- タイトル -->
            <h1><i class="fa-solid fa-face-laugh-squint" style="color: #188681;"></i> 顔情報登録</h1>

            <!-- 要素横並べ -->
            <div class="div-width">

                <!-- 要素横縦並べ(どっちやねん) -->
                <div class="div-height">
                    <p><i class="fa-solid fa-user" style="color: #188681;"></i> 生徒の情報</p>
                    <div class="operation2">
                        <p>ID：<?php echo $student_id; ?></p>
                        <p>氏名：<?php echo $student_name; ?></p>
                        <?php

                        if ($student_face) {
                            echo '<p class="p-text-green"><i class="fa-solid fa-check"></i> 顔登録済</p>';
                        } else {
                            echo '<p class="p-text-red"><i class="fa-solid fa-circle-exclamation"></i> 顔未登録</p>';
                        }

                        ?>
                    </div> <!-- operation2 -->
                </div> <!-- div-height -->

                <!-- 要素横縦並べ(どっちやねん) -->
                <div class="div-height" style="flex-direction: column; margin: 1em;">
                    <p><i class="fa-solid fa-plus" style="color: #188681;"></i> 登録</p>
                    <div class="operation2">
                        <button class="empty" id="select-btn" onclick="document.querySelector('#file').click();" disabled>画像を選択</button>
                        <input type="file" name="file" id="file" accept="image/*">
                        <button id="register" onclick="registerFace()" disabled>登録</button>
                    </div>
                </div> <!-- div-height -->

            </div> <!-- div-width -->

            <!-- プレビュー -->
            <div class="preview" id="preview">
                <p><i class="fa-solid fa-eye" style="color: #188681;"></i> プレビュー</p>
                <div class="operation preview2">
                    <img id="preview-img">
                    <canvas id="overlay"></canvas>
                </div>
            </div>  <!-- preview -->

            <!-- ログ -->
            <p><i class="fa-solid fa-magnifying-glass-chart" style="color: #188681;"></i> 実行ログ</p>
            <div class="operation" id="execution-log"></div>

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

    <!-- dash-register-face.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/dash-register-face.js"></script>

</body>
</html>