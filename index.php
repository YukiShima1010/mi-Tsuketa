<?php

// エラー表示(必要な時以外はコメントアウト)
// error_reporting(E_ALL);
// ini_set("display_errors", "1");

// セッション開始
session_start();

// config読み込み
require_once "config/config.php";

// データベース接続
require_once "config/database.php";

// ユーザーログイン確認
require "app/functions/student_id_check.php";
$student_id = studentIdCheck();

// 写真データを取得
try {
    // すべての写真データを取得
    $sql = "SELECT name, path, detection, comment FROM photos";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result_photo_data = $stmt->fetchAll();

    if ($result_photo_data === false) {
        // データベースエラー
        http_response_code(500);
        include("error/database.php");
        exit;
    }

    $sql = "SELECT name FROM students WHERE id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student_name = $stmt->fetchColumn();

    // 結果を出力
    if ($student_name === false) {
        // データベースエラー
        http_response_code(500);
        include("error/database.php");
        exit;
    }
} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    //echo $e->getMessage();
    http_response_code(500);
    include("error/database.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns#">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="みーつけた - 生徒写真共有Webアプリ"/>
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
                    <a href="https://<?php echo $config["domain"]; ?>/dashboard">ダッシュボード</a>
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
                <li><a href="https://<?php echo $config["domain"]; ?>/dashboard">ダッシュボード</a></li>
                <li><a href="#" onclick="logout()">ログアウト</a></li>
            </ul>
        </nav>
    </header>

    <!-- コンテンツ -->
    <div class="content">

        <!-- コンテナ -->
        <div class="container">

            <!-- らしき写真 -->
            <p><i class="fa-solid fa-user" style="color: #188681;"></i> <?php echo $student_name; ?>さんらしき写真</p>
            <div class="gallery" id="gallery1">
                <?php

                // らしき写真挿入数
                $i = 0;

                // らしき写真を挿入
                foreach ($result_photo_data as $row) {
                    $comment = isset($row["comment"]) ? htmlspecialchars($row["comment"], ENT_QUOTES, "UTF-8") : "";
                    $detection = isset($row["detection"]) ? json_decode($row["detection"], true) : false;
                    if ($detection != false) {
                        if (in_array($student_id, $detection)) {
                            echo '<div class="gallery-div">';
                            echo '<img src="https://' . $config["domain"] . '/app/api/media_read.php/?id=' . htmlspecialchars($row["path"], ENT_QUOTES, "UTF-8") . '" alt="' . htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") . '" data-comment="' . $comment . '" onclick="openModal(this)">';
                            echo "</div>";
                            $i++;
                        }
                    }
                }

                // もし挿入数が0だったら
                if ($i == 0) {
                    echo"<p>らしき写真が見つかりませんでした。</p>";
                }

                ?>
            </div> <!-- gallery -->

            <!-- すべての写真 -->
            <p><i class="fa-solid fa-images" style="color: #188681;"></i> 全ての写真</p>
            <div class="gallery" id="gallery2">
                <?php

                // すべての写真挿入
                $total_pages = ceil(count($result_photo_data) / 10); // 写真の総数/1ページあたりの表示数
                $page_now = isset($_GET["page"]) ? $_GET["page"] : 1; // 現在のページをGETから受信(値がない場合は「1」)
                $start_img = ($page_now - 1) * 10; // 現在のページの最初のimg

                // 画像をページごとに表示
                $i = 0;
                foreach (array_slice($result_photo_data, $start_img, 10) as $row) {
                    $comment = isset($row["comment"]) ? htmlspecialchars($row["comment"], ENT_QUOTES, "UTF-8") : "";
                    echo '<div class="gallery-div">';
                    echo '<img src="https://' . $config["domain"] . '/app/api/media_read.php/?id=' . htmlspecialchars($row["path"], ENT_QUOTES, "UTF-8") . '" alt="' . htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") . '" data-comment="' . $comment . '" onclick="openModal(this)">';
                    echo "</div>";
                    $i++;
                }

                // もし挿入数が0だったら
                if ($i == 0) {
                    echo "<p>写真がありませんでした。</p>";
                }

                ?>
            </div> <!-- gallery -->

            <!-- モーダル -->
            <div class="modal" id="modal" onclick="closeModal(event)">
                <span class="modal-close" id="modal-close">&times;</span>
                <img class="modal-img" id="modal-img">
                <div class="modal-frame" id="modal-frame">
                    <div class="modal-caption" id="modal-caption"></div>
                    <p id="comment"></p>
                    <a class="modal-dl-btn" id="modal-dl-btn">保存</a>
                </div>
            </div> <!-- modal -->

            <!-- ページネーション -->
            <div class="pagination">
                <?php

                // 前へボタン挿入
                if ($page_now > 1) {
                    echo '<a href="?page=' . $page_now - 1 . '">&laquo; 前へ</a>';
                } else {
                    echo "<span>&laquo; 前へ</span>";
                }

                // ページボタン挿入
                for ($page = 1; $page <= $total_pages; $page++) {
                    if ($page == $page_now) {
                        echo '<a href="?page=' . strval($page) . '" class="active">' . $page . '</a>';
                    } else {
                        echo '<a href="?page=' . strval($page) . '">' . $page . '</a>';
                    }
                }

                // 次へボタン挿入
                if ($page_now < $total_pages) {
                    echo '<a href="?page=' .  $page_now + 1 . '">次へ &raquo;</a>';
                } else {
                    echo "<span>次へ &raquo;</span>";
                }

                ?>
            </div> <!-- pagination -->

        </div> <!-- container -->

    </div> <!-- contents -->

    <!-- フッター -->
    <footer>
        <p>&copy; 2025 YukiShima</p>
    </footer>

    <!-- FontAwesome -->
    <script type="text/javascript" charset="UTF-8" src="https://kit.fontawesome.com/91559ddbec.js" crossorigin="anonymous"></script>

    <!-- student-home.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/student-home.js"></script>

</body>
</html>