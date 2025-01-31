# 📸 みーつけた　〜 学校と保護者を繋ぐ架け橋 〜

![key-visual](https://github.com/user-attachments/assets/48abfc2b-6352-422f-88fb-3962aac412a4)

<img src="https://img.shields.io/badge/PHP-ccc.svg?logo=php&style=flat">  <img src="https://img.shields.io/badge/Javascript-276DC3.svg?logo=javascript&style=flat">

「みーつけた」は保護者に生徒の学校で撮られた写真を簡単かつ迅速に届けるWebアプリケーションです。

## 特徴

- **シンプルかつ分かりやすい**

  主な利用者が生徒の保護者であるため、UIは難しい文言を使用せず、直感的な操作ができるようになっています。

  <img width="1710" alt="スクリーンショット 2025-01-30 12 08 02" src="https://github.com/user-attachments/assets/0f874bfb-5ec6-4d43-b79a-a7bdfd68779c" /><img width="1710" alt="スクリーンショット 2025-01-30 11 41 30" src="https://github.com/user-attachments/assets/b8d4163c-5d8b-4b03-bc2e-3301acd83c7d" />

- **見つけやすい**

  顔識別機能で、その生徒が写ってそうな写真をあらかじめピックアップしており、<ins>保護者はすぐに生徒の写真を見つけることができます</ins>。

  <img width="1710" alt="スクリーンショット 2025-01-30 12 06 59" src="https://github.com/user-attachments/assets/7d88aa65-be16-477a-ad10-303c7c3eaa1e" />

- **高画質な画像を共有できる**

  8K/60MBまでの写真なら一切圧縮せず保護者に共有することができます。また、写真をダウンロードする際や拡大する時以外は、写真が60KB以下になるまで圧縮した「節約画質」を表示し、データ量を削減してます。

  <img width="1710" alt="スクリーンショット 2025-01-30 11 59 54" src="https://github.com/user-attachments/assets/0193eee3-7914-4b61-b643-1844f027d80c" />

- **パスワードレス**

  保護者はメールアドレス認証、又はパスキー(※1)でログインすることができます。これによりパスワードを覚えたり、学校がパスワードのリセット作業に追われることもありません。

  <img width="654" alt="スクリーンショット 2025-01-30 11 49 03" src="https://github.com/user-attachments/assets/b39053df-1d99-40b4-b28b-aa9ac4eab6aa" />

- **音楽が再生できる（※1)**

  合唱コンクールの音声データなどをあらかじめ予め登録しておくことで、音楽を聴きながら写真の閲覧が可能です。

- **顔識別でサーバーに負荷がかからない**

  通常なら顔識別などの処理はサーバーで実行されますが、みーつけたではクライアント側で実行しており、<ins>サーバーに負荷かけすぎでレンタルサーバーなどから制限を食らうことはありません</ins>。また、処理自体はそこまで高負荷なものでは無いので、スマホでもサクサク実行可能です。

- **スマホだけで写真をUP&管理**

  みーつけたではスマホサイズのページ表示に対応しており、顔識別処理もスマホで実行可能となっています。そのため、スマホで撮影した写真を<ins>**そのままその場で**アップロード可能です</ins>。

- **PWAに対応**

  PWAに対応しているため、みーつけたをネイティブアプリのように使用することができます。
  ![IMG_8982](https://github.com/user-attachments/assets/809b7f03-1cc3-4701-939b-4255e12d2c16)

- **導入が容易**

  みーつけたでは「学校が簡単に導入できる」ことを目標としています。具体的には、「高校の情報科の先生が、一般的なレンタルサーバーにSSHでコマンドを打つことなく導入できる」ことを目指しています。そのため、サーバーに色々インストールしたりしなくてはいけないだとか、Node.jsなどのそもそも使用できない言語は使用せず、<ins>PHPと普通のJavaScript**のみ**で</ins>開発しました。また、フレームワークも一切使用せずに開発しています。

※1 今後実装予定です


## デモについて

みーつけたでは<ins>**学園関係者限定で**実際に動作させることができるデモを解放しております</ins>。

ですが、デモが必要と聞いて数時間で作ったので、README作成現在で同時使用は1つ、1回2時間程度しかご利用いただけません。

今後デモの個数を増やす予定なので、少々お待ちいただけますと幸いです。

→ → https://mi.syumikun.com/mission/ ← ←

使用中にご不明な点がございましたらREADMEの利用方法(絶賛準備中)を確認するか、Slack@ユキシマまでご連絡ください。

> 学園関係者以外の方はMisskey.ioにメルアド教えて♡とダイレクトノートしていただけますと幸いです。


## 利用方法

`現在準備中です！ごめんなさい`

## 動作確認環境

PHP：`8.3`

MySQL(MariaDB)：`10.6`

### 動作確認済みのブラウザ

- Google Chrome
- Safari

### PWAでの動作確認済みのOS/ブラウザ

> [!IMPORTANT]
> iOS(iPhone/iPad)はSafariでのみPWAを利用できます。

- MacOS 15 / Safari
- MacOS 15 / Safari
- iOS 18 / Safari

## LICENSE | ライセンス

### [face-api.js](https://github.com/justadudewhohacks/face-api.js)

Ver.0.22.0

MIT License

```Copyright (c) 2018 Vincent Mühler```

### [PHPMailer](https://github.com/PHPMailer/PHPMailer)

Ver.6.9.3

LGPL-2.1 License

```Copyright (C) 1991, 1999 Free Software Foundation, Inc.```


## 導入方法

> [!NOTE]
> みーつけたは現在も開発中で、導入については今後より容易になる予定です。

### 1. 環境を準備する

はじめに、次の条件に合うサーバーを準備/レンタルします。

1. FTPを使用できる
2. PHPを使用できる(PHP8.3が利用できるとなお良い)
3. <ins>**SSLを使用できる**</ins>
4. MySQLを使用できる
5. SMTPを利用できる<ins>**(587番ポート)**</ins>
6. TSLを使用できる
7. .htaccessを使用できる
8. 容量が15GB以上(推奨)

準備/レンタルでき次第、サイト・メールの設定を各自実施してください。


### 2. データベースを準備する

MySQLのデータベースを立ち上げ後次のSQLを実行してテーブルを作成してください。

#### テーブル名`students`を作成

```
CREATE TABLE `students` (
  `id` int(5) NOT NULL,
  `name` text NOT NULL,
  `face` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `mailaddress` text DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailaddress` (`mailaddress`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
```

#### テーブル名`photos`を作成

```
CREATE TABLE `photos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `path` TEXT NOT NULL,
  `name` TEXT NOT NULL,
  `comment` TEXT DEFAULT NULL,
  `face` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `detection` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `expression` VARCHAR(255) DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### テーブル名`mail_token`を作成

```
CREATE TABLE `mail_token` (
  `token` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `student_id` INT(5) NOT NULL,
  `ipaddress` VARCHAR(45) NOT NULL,
  `redirect_url` TEXT DEFAULT NULL,
  `mailaddress` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

テーブルを作成できたか必ず確認してください。


## 3. ファイルをアップロードする

1. まず、[みーつけたのGitHubのページ](https://github.com/YukiShima1010/mi-Tsuketa/)の`Code` → `Download ZIP`をクリックしてみーつけた本体をダウンロードします。
2. ダウンロードしたファイルを解凍しFTPで<ins>**ドメイン直下**に設置します</ins>。

### 4. `config.php`を編集する

> [!NOTE]
> 今後ymlやjsonといったファイルに変更する予定です

みーつけたでは`config/config.php`にDBの設定や管理者メールアドレスを保存しています。

初期は何も値が入っていないので、下記に従って<ins>全ての値を埋めてください</ins>。


#### 1. `domain`

ここではみーつけたを設置するドメインを指定します。

> [!WARNING]
> この際、ドメインのみを入れてください。「mi.sample.com/」など「/」を入れないでください。

例：mi.sample.comに設置する場合は

`"domain" => "mi.sample.com",`

と入れます。


#### 2. `host`, `dbname`, `username`, `password`

ここではMySQLの接続情報を入力します。

```
"host" => "localhost",
"dbname" => "database_name",
"username" => "database_user_name",
"password" => "password",
```


#### 3. `recaptcha_site_key`, `recaptcha_secret_key`

みーつけたでは保護者・管理者のログインページにGoogle reCAPTCHA V3を使用しており、README作成現在では<ins>OFFにする機能は作っていません</ins>。そのため、必ずreCAPTCHAからサイトキー・シークレットキーを取得し、値を入れてください。

```
"recaptcha_site_key" => "XXXXXXXXXXXXXXXXXXXX",
"recaptcha_secret_key" => "XXXXXXXXXXXXXXXXXXXX",
```


#### 4. `mail_server`, `mail_user`, `mail_password`, `mail_address`

ここではメールの送信元情報を入力します。

> [!IMPORTANT]
> メールはTLSで暗号化され、ポートは587を使用します。今後暗号化の有無やポート番号を変更できるようにしようと考えています。

```
"mail_server" => "mail.sample.com",
"mail_user" => "no-reply",
"mail_password" => "password",
"mail_address" => "no-reply@sample.com",
```


#### 5. `school_name`, `school_tel`, `school_email`

ここではメールの最後(署名)に表示される学校名・電話番号・メールアドレスを指定します。

例えば下記の通りに値を入れると、

```
"school_name" => "波城高等学校",
"school_tel" => "0120-000-000",
"school_email" => "contact@nami-shiro.ed.jp",
```

下記の通りになります。

```
━━━━━━━━━━━━━━━━━━━━
波城高等学校
TEL：0120-000-000
E-Mail：contact@nami-shiro.ed.jp
━━━━━━━━━━━━━━━━━━━━
```

このようになります。


#### 6. `admin_id`, `admin_mailaddress`

ここでは次の二つを指定します。

1. 管理者ID

   管理者IDはユーザーが目にする場所には表示されません。自由な値が入れられます。

   もし、<ins>不正なアクセスが発覚した際に、このIDを変更することで**全ての場所でセッションが破棄されます**</ins>。
   
3. 管理者メールアドレス

   管理者がログインする際に使用されるメールアドレスです。

   また、管理者がログインすると、本メールアドレス宛にログインしたIPとユーザーエージェント、日時が入ったメールが送信されます。

管理者ID：`001`　管理者メールアドレス：`admin@sample.com`の場合は下記の通りになります。

```
"admin_id" => "001",
"admin_mailaddress" => "admin@sample.com",
```


#### 7. `admin_password`

ここでは管理者がログインする際に使用されるパスワードを<ins>**ハッシュ化した状態で**保存します</ins>。

`SHA-256`でのハッシュ化をおすすめしています。

> [!CAUTION]
> 必ずハッシュ化した状態で保存してください。

> [!IMPORTANT]
> ソルトを使用しないでください。

パスワードを「`ABCabc123_`」としたい場合は`SHA-256`で<ins>**ソルトを使用せず**ハッシュ化</ins>し、

`126c08d9dd2aca7b36d3907967aa54456d8e6a9d7d7915567a8c53dae37f2909`

出てきた値を

```
"admin_password" => '126c08d9dd2aca7b36d3907967aa54456d8e6a9d7d7915567a8c53dae37f2909',
```

このように入れてください。


#### 8. `similarity` 最後ﾀﾞﾖ！

> [!TIP]
> この値は基本初期値のままで大丈夫です。

ここでは顔識別する際に、<ins>どのくらい似ていれば同一人物と判断するか</ins>を設定します。

`1.0`に近づけば近づくほど緩くなり、逆なら厳しくなります。

もし、顔識別がうまく機能しない場合は`0.5`にするとうまくいくかもしれません。

```
"similarity" => 0.6,
```

上記のように値を入れます。

***

これにて`config.php`の設定は完了です！！

必ず**保存してから**ファイルを閉じてくださいね！


### 5. ドメインにアクセスしてみる

ドメインに実際にアクセスしてきちんと動くかご確認ください。

これにて完了です。お疲れ様でした！！！！

## Copyright

```Copyright (C) 2025 YukiShima```
