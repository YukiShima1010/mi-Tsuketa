// 初期変数
const executionLog = document.getElementById("execution-log");
const previewImg = document.getElementById("preview-img");
const overlay = document.getElementById("overlay");
const ctx = overlay.getContext("2d");

let imgFaceDatas = [];

// 最大ファイルサイズ(50MB)
const maxFileSize = 50 * 1024 * 1024;

// 最大縦横(8000x8000[px])
const maxWidth = 8000;
const maxHeight = 8000;


// 実行ログ
async function executionLog2(message, type) {
    var new_log = document.createElement("p");
    new_log.textContent = message;

    switch (type) {
        case "error":
            new_log.classList.add("red");
            break;
        case "complete":
            new_log.classList.add("green");
            break;
    }
    executionLog.insertBefore(new_log, executionLog.firstChild);
}


// モデル読み込み
async function loadModels() {
    executionLog2("必要なモデルを読み込んでいます...");
    await faceapi.nets.ssdMobilenetv1.loadFromUri("/models");
    await faceapi.nets.faceLandmark68Net.loadFromUri("/models");
    await faceapi.nets.faceRecognitionNet.loadFromUri("/models");
    await faceapi.nets.faceExpressionNet.loadFromUri("/models");
    executionLog2("モデルの読み込みが完了しました", "complete");

    // 選択ボタンを有効化
    document.getElementById("upload-btn").disabled = false;
}


// アップロード
async function upload() {
    const fileInput = document.getElementById("file");
    const fileCount = fileInput.files.length;
    const imgFaceDatas = [];

    if (fileCount == 0) {
        // もしファイル数が0だったら
        alert("少なくとも一つはファイルを選択してください。");
        return false;
    } else {
        executionLog2(fileCount + "個の画像が選択されました");
    }

    if (window.confirm(fileCount + "枚の画像が選択されました。\n処理を開始してもよろしいですか？")) {
        executionLog2("<< 処理を開始します >>", "complete");

        const files = fileInput.files;

        // ファイルを1つずつ処理する
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            executionLog2(i + 1 + "枚目の画像を確認しています...");

            // 画像ファイルかどうか確認
            if (!file.type.startsWith("image/")) {
                executionLog2("[エラー]画像でないファイルが選択されています：" + (i + 1) + "枚目(" + file.name + ")", "error");
                alert("↓ 画像でないファイルが選択されています ↓\n" + file.name);
                return false;
            }

            // 画像の容量確認
            if (file.size > maxFileSize) {
                executionLog2("[エラー]容量制限を超える画像が選択されています：" + (i + 1) + "枚目(" + file.name + ")", "error");
                alert("↓ 容量制限を超える画像が選択されています ↓\n" + file.name);
                return false;
            }

            // 画像の解析・保存
            await analysisImage(file, imgFaceDatas, i);
        }

        executionLog2("全ての写真の分析が完了しました", "complete");

        // サーバーに画像と解析結果をアップロード
        executionLog2("画像と解析結果をサーバーにアップロードする準備をしています...");
        let errorFileList = [];
        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // アップロード処理
            executionLog2((i + 1) + "枚目(" + files[i].name + ")をサーバーにアップロード中...");
            var result = await sendPost(file, imgFaceDatas[i]);

            // 結果を確認
            if (result) {
                executionLog2((i + 1) + "枚目(" + files[i].name + ")のアップロードに成功しました", "complete");
            } else {
                errorFileList.push(files[i].name);
            }
        }


        // 結果発表
        if (errorFileList.length == 0) {
            executionLog2("=======");
            executionLog2(fileCount + "個の画像を分析し、アップロードしました。", "complete")
            executionLog2("「写真メニュー」の「写真振り分け」からアップロードした写真の生徒振り分けを行なってください。")
            executionLog2("=======");
        } else {
            executionLog2("=======");
            executionLog2(fileCount + "個の内" + (fileCount - errorFileList.length) + "個のアップロードに成功し" + errorFileList.length + "個のアップロードに失敗しました。", "complete")
            executionLog2("↑ アップロードに失敗した画像 ↑", "error")
            executionLog2(errorFileList, "error")
            executionLog2("「メニュー」の「写真振り分け」からアップロードした写真の生徒振り分けを行なってください。")
            executionLog2("=======");
        }
    }
}


// 顔検出・顔特徴量計測・表情測定・保存
async function analysisImage(file, imgFaceDatas, i) {
    // 準備
    var img = new Image();
    imgFaceDatas[i] = [];

    return new Promise((resolve) => {
        img.onload = async () => {

            // 縦横サイズ制限確認
            let size = { width: img.width, height: img.height };
            if (size.width > maxWidth || size.height > maxHeight) {
                // サイズエラーの場合(ここで処理全終了)
                executionLog2("[エラー]サイズ制限(" + maxWidth + "x" + maxHeight + ")を超える画像が選択されています：" + (i + 1) + "枚目(" + file.name + ")", "error");
                alert("↓ サイズ制限(" + maxWidth + "x" + maxHeight + ")を超える画像が選択されています ↓\n" + (i + 1) + "枚目(" + file.name + ")");
                executionLog2("エラーで処理が終了しました", "error");
                resolve();
                return;
            }

            // 非表示imgに画像挿入(後で処理で使う)
            executionLog2((i + 1) + "枚目(" + file.name + ")の写真を読み込み中...");
            previewImg.src = URL.createObjectURL(file);

            // canvasのサイズを画像に合わせる
            overlay.width = size.width;
            overlay.height = size.height;

            // 顔検出・顔特徴量計測・表情計測 ← 中国語かな
            executionLog2("顔検出・顔特徴量計測・表情測定中...");
            const detections = await faceapi.detectAllFaces(previewImg)
                .withFaceLandmarks()
                .withFaceDescriptors()
                .withFaceExpressions();

            // 検出された顔の数
            const faceCount = detections.length;

            if (faceCount == 0) {
                // 誰も検出されなかった場合(ここはjsonにnullと入れて処理続行、アラートも無し)
                executionLog2("[エラー]顔が検出されませんでした：" + (i + 1) + "枚目(" + file.name + ")", "error");
                imgFaceDatas[i].push({
                    descriptor: "null",
                    expressions: "null"
                });
            } else {
                executionLog2(faceCount + "人の顔が検出されました");

                // 顔情報を保存
                detections.forEach((detection, faceIndex) => {
                    const { descriptor, expressions } = detection;

                    // 一番割合の高い表情を選別
                    const dominantExpression = Object.keys(expressions).reduce((a, b) =>
                        expressions[a] > expressions[b] ? a : b
                    );

                    // ここでJSONに格納(複数の画像のデータを一つのJSONに入れるため「i」で階層分ける)
                    imgFaceDatas[i].push({
                        descriptor: Array.from(descriptor),
                        expression: dominantExpression
                    });
                });
            }

            // 画像の処理完了
            executionLog2((i + 1) + "枚目(" + file.name + ")の写真の分析が完了しました", "complete");
            resolve();
        };

        img.src = URL.createObjectURL(file);
    });
}


// データをAPIにPOST
async function sendPost(file, imgFaceData) {

    // フォームデータを作成
    const formData = new FormData();

    // ファイル名を取得（拡張子を取り除いた名前）
    const fileName = file.name.split(".")[0];  // 拡張子を削除したファイル名を取得

    // フォームデータにファイル名と顔データを追加
    formData.append("file", file);
    formData.append("name", fileName);  // ファイル名（拡張子なし）を追加
    formData.append("faceData", JSON.stringify(imgFaceData));  // 顔情報をJSON文字列として追加

    try {
        // POST送信
        const response = await fetch("https://" + domain + "/app/api/upload_media.php", {
            method: "POST",
            body: formData,
        });

        const data = await response.json();

        if (data.status != 200) {
            executionLog2("[エラー]" + file.name + "のアップロード中にエラーが発生しました：" + data.error_message, "error");
            alert("【 ↓ 下記のファイルのアップロードに失敗しました ↓ 】\n" + file.name + "\nエラーが発生しましたが、次のファイルをアップロードします。\n繰り返しこのエラーが表示される場合はタブを閉じて最初からやり直してください。")
            return false;
        } else {
            return true;
        }
    } catch (error) {
        executionLog2("[エラー]" + file.name + "のアップロード中にエラーが発生しました：" + error.message, "error");
        alert("【 ↓ 下記のファイルのアップロードに失敗しました ↓ 】\n" + file.name + "\nエラーが発生しましたが、次のファイルをアップロードします。\n繰り返しこのエラーが表示される場合はタブを閉じて最初からやり直してください。")
        return false;
    }
}


// 読み込まれたら実行
window.onload = function () {
    // モデルを読み込む
    loadModels();
}