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

// 写真データを取得
try {
    // すべての写真データを取得
    $sql = "SELECT id, name, path, comment, detection, expression, created FROM photos";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    if ($result === false) {
        // データベースエラー
        http_response_code(500);
        include("../../error/database.php");
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
    <title>画像管理 | みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>/dashboard/images"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="画像管理 - みーつけた"/>
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
            <h1><i class="fa-solid fa-images" style="color: #188681;"></i> 画像管理</h1>

            <!-- 操作 -->
            <p><i class="fa-solid fa-wrench" style="color: #188681;"></i> 操作</p>
            <div class="operation">
                <div class="form_hoge">
                    <label for="select">操作する項目:</label>
                    <select id="select" name="select" size="1">
                        <option value="1" selected>選択中</option>
                        <option value="2">選択以外</option>
                    </select>

                    <button type="submit" onclick="studentChange()"><i class="fa-solid fa-rotate-right" style="color: #ffffff;"></i> 情報を変更</button>
                    <div><button type="submit" onclick="studentDelete()"><i class="fa-solid fa-trash" style="color: #ffffff;"></i> 画像を削除</button></div>
                    <div><button type="submit" onclick="location.href = 'https://<?php echo $config["domain"]; ?>/dashboard/upload/';"><i class="fa-solid fa-cloud-arrow-up" style="color: #ffffff;"></i> アップロード</button></div>
                    <div><button type="submit" onclick="location.href = 'https://<?php echo $config["domain"]; ?>/dashboard/sorting/';"><i class="fa-solid fa-gifts" style="color: #ffffff;"></i> 写真振り分け</button></div>
                </div>
            </div> <!-- operation -->

            <!-- テーブル -->
            <div class="table-div">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkbox" name="checkbox"/></th>
                            <th><i class="fa-regular fa-circle-user"></i> ID</th>
                            <th><i class="fa-regular fa-image"></i> 写真</th>
                            <th><i class="fa-solid fa-pencil"></i> 題名</th>
                            <th><i class="fa-regular fa-comment"></i> コメント</th>
                            <th><i class="fa-regular fa-face-laugh-squint"></i> 検出された顔</th>
                            <th><i class="fa-regular fa-face-grin-tongue-wink"></i> 検出された表情</th>
                            <th><i class="fa-regular fa-clock"></i> 登録日時</th>
                            <th><i class="fa-solid fa-wrench"></i> 操作</th>
                        </tr>
                    </thead>
                    <tbody id="student-table">
                        <?php

                        // 表を"表"示(笑)
                        foreach ($result as $row) {

                            $table_id = htmlspecialchars($row["id"], ENT_QUOTES, "UTF-8");
                            $table_name = htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8");
                            $table_path = htmlspecialchars($row["path"], ENT_QUOTES, "UTF-8");
                            $table_comment = htmlspecialchars(isset($row["comment"]) ? $row["comment"] : "記載無し", ENT_QUOTES, "UTF-8");
                            $table_created = htmlspecialchars($row["created"], ENT_QUOTES, "UTF-8");

                            // 表情取得
                            if (!isset($row["expression"])) {
                                $table_expression = "検出無し";
                            } else {
                                $expression_json = json_decode($row["expression"], true);

                                if ($expression_json == null) {
                                    $table_expression = "検出無し";
                                } else {
                                    // 表情一覧
                                    $expression_map = [
                                        "happy" => "笑顔",
                                        "sad" => "悲しい",
                                        "angry" => "怒っている",
                                        "surprised" => "驚いた",
                                        "neutral" => "無表情"
                                    ];

                                    // 表情表示準備
                                    $display_expressions = [];
                                    foreach ($expression_json as $expression) {
                                        if (isset($expression_map[$expression])) {
                                            $display_expressions[] = $expression_map[$expression];
                                        }
                                    }
                                    $table_expression = implode("、", $display_expressions);
                                }
                            }

                            // 検出された人の名前取得
                            try {
                                if (!isset($row["detection"])) {
                                    $student_names = "検出無し";
                                } else {
                                    $student_ids = json_decode($row["detection"], true);
                                    $placeholders = implode(",", array_fill(0, count($student_ids), "?"));
                                    $sql = "SELECT id, name FROM students WHERE id IN ($placeholders)";

                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute($student_ids);
                                    $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if ($result2 === false) {
                                        $student_names = "取得に失敗しました";
                                    }

                                    $student_list = [];
                                    foreach ($result2 as $row) {
                                        $student_list[] = $row["name"] . "(" . $row["id"] . ")";
                                    }
                                    $student_names = implode(", ", $student_list);
                                }
                            } catch (PDOException $e) {
                                // エラー表示(必要時以外はコメントアウト)
                                // echo $e->getMessage();
                                $student_names = "取得に失敗しました";
                            }
                            $table_detection = htmlspecialchars($student_names, ENT_QUOTES, "UTF-8");

                            // 表示
                            echo "<tr>";
                            echo '<td><input type="checkbox" class="checkbox" name="checkbox" value="' . $table_id . '" /></td>';
                            echo "<td>" . $table_id . "</td>";
                            echo '<td><div class="gallery-div" style="margin: 0 auto;"><img src="https://' . $config["domain"] . '/app/api/media_read.php/?id=' . $table_path . '" alt="' . $table_name . '" data-comment="' . $table_comment . '" onclick="openModal(this)"></div></td>';
                            echo "<td>" . $table_name . "</td>";
                            echo "<td>" . $table_comment . "</td>";
                            echo "<td>" . $table_detection . "</td>";
                            echo "<td>" . $table_expression . "</td>";
                            echo "<td>" . $table_created . "</td>";
                            echo '<td><button class="empty" onclick="location.href = \'http://' . $config["domain"] . '/dashboard/media-change/?media_id=' . $table_id . '\';">変更</button></td>';
                            echo "</tr>";
                        }

                        ?>
                    </tbody>
                </table>
            </div> <!-- table-div -->

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

    <!-- dash-images.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/dash-images.js"></script>

</body>
</html>