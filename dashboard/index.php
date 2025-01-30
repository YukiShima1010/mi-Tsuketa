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

// 生徒データを取得
try {
    $sql = "SELECT id, name, mailaddress, created FROM students";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    if ($result === false) {
        // データベースエラー
        http_response_code(500);
        include("../error/database.php");
        exit;
    }

    // faceがnullのid出力
    $sql = "SELECT id FROM students WHERE face IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result_face_is_null = $stmt->fetchAll();

    if ($result_face_is_null === false) {
        // データベースエラー
        http_response_code(500);
        include("../error/database.php");
        exit;
    } else {
        // データを整頓
        $result_face_is_null_id = array_column($result_face_is_null, "id");
    }

} catch (PDOException $e) {
    // エラー表示(必要時以外はコメントアウト)
    // echo $e->getMessage();
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
    <title>生徒管理 | みーつけた</title>
    <meta name="description" content="「みーつけた」は生徒の学校での様子を保護者に簡単・安全に届けることができるWebアプリケーションです。">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="robots" content="noindex">
    <link rel="manifest" href="https://<?php echo $config["domain"] ?>/manifest.json">

    <!-- アイコン -->
    <link rel="icon" href="https://<?php echo $config["domain"]; ?>/assets/img/favicon.ico"/>
    <link rel="apple-touch-icon" href="https://<?php echo $config["domain"]; ?>/assets/img/apple-touch-icon.png"/>

    <!-- OGP -->
    <meta property="og:url" content="https://<?php echo $config["domain"]; ?>/dashboard"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="生徒管理 - みーつけた"/>
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
            <h1><i class="fa-solid fa-user-check" style="color: #188681;"></i> 生徒管理</h1>

            <!-- 操作 -->
            <p><i class="fa-solid fa-wrench" style="color: #188681;"></i> 操作</p>
            <div class="operation">
                <div class="form_hoge">
                    <label for="select">操作する項目:</label>
                    <select id="select" name="select" size="1">
                        <option value="1" selected>選択中</option>
                        <option value="2">選択以外</option>
                    </select>

                    <button type="submit" onclick="studentChange()"><i class="fa-solid fa-rotate-right" style="color: #ffffff;"></i> 生徒を変更</button>
                    <div><button type="submit" onclick="studentDelete()"><i class="fa-solid fa-trash" style="color: #ffffff;"></i> 生徒を削除</button></div>
                    <div><button type="submit" onclick="location.href = 'https://<?php echo $config["domain"]; ?>/dashboard/student-add/';"><i class="fa-solid fa-user-plus" style="color: #ffffff;"></i> 生徒を追加</button></div>
                </div>
            </div> <!-- operation -->

            <!-- テーブル -->
            <div class="table-div">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkbox" name="checkbox"/></th>
                            <th><i class="fa-regular fa-circle-user"></i> ID</th>
                            <th><i class="fa-solid fa-pencil"></i> 氏名</th>
                            <th><i class="fa-solid fa-at"></i> メールアドレス</th>
                            <th><i class="fa-regular fa-face-laugh-squint"></i> 顔情報</th>
                            <th><i class="fa-regular fa-clock"></i> 登録日時</th>
                            <th><i class="fa-solid fa-wrench"></i> 操作</th>
                        </tr>
                    </thead>
                    <tbody id="student-table">
                        <?php

                        // 表を"表"示(笑)
                        foreach ($result as $row) {

                            echo "<tr>";
                            echo '<td><input type="checkbox" class="checkbox" name="checkbox" value="' . $row["id"] . '" /></td>';
                            echo "<td>" . htmlspecialchars($row["id"], ENT_QUOTES, "UTF-8") . "</td>";
                            echo "<td>" . htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") . "</td>";
                            echo "<td>" . htmlspecialchars($row["mailaddress"], ENT_QUOTES, "UTF-8") . "</td>";

                            // 顔情報が登録されているか
                            if (in_array($row["id"], $result_face_is_null_id)) {
                                // 登録されていない
                                echo '<td><button class="fill" onclick="location.href = \'http://' . $config["domain"] . '/dashboard/register-face/?student=' . $row["id"] . '\';">登録</button></td>';
                            } else {
                                // 登録されている
                                echo '<td><button class="empty" onclick="location.href = \'http://' . $config["domain"] . '/dashboard/register-face/?student=' . $row["id"] . '\';">変更</button></td>';
                            }

                            echo "<td>" . htmlspecialchars($row["created"], ENT_QUOTES, "UTF-8") . "</td>";
                            echo '<td><button class="empty" onclick="location.href = \'http://' . $config["domain"] . '/dashboard/student-change/?student=' . $row["id"] . '\';">変更</button></td>';
                            echo "</tr>";
                        }

                        ?>
                    </tbody>
                </table>
            </div> <!-- table-div -->

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

    <!-- dash-index.js -->
    <script type="text/javascript" charset="UTF-8" src="https://<?php echo $config["domain"]; ?>/assets/js/dash-index.js"></script>

</body>
</html>