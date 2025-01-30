// 初期変数
const preview = document.getElementById("preview");
const previewImg = document.getElementById("preview-img");
const overlay = document.getElementById("overlay");
const executionLog = document.getElementById("execution-log");
const register = document.getElementById("register");
var descriptorData = "";


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
    executionLog2("モデルの読み込みが完了しました");

    // 選択ボタンを有効化
    document.getElementById("select-btn").disabled = false;
}


// 登録
async function registerFace() {
    // 登録用APIにPOSTする
    var response = await fetch("https://" + domain + "/app/api/register_face.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
            body: JSON.stringify({
            student_id: student_id,
            face_data: descriptorData
        })
    })
    .then(response => response.json())
    .then(data => {
        // { status: 200 } or {status: 500, error_message: "ここにエラーメッセージ"}
        if (data.status == 200) {
            alert("顔情報の登録/変更に成功しました。\n前のページに戻ります。");
            location.href = "https://" + domain + "/dashboard/";
            return false;
        } else {
            alert("登録中にエラーが発生しました。\n時間が経ってから再度お試しください。");
            return false;
        }
    })
    .catch(error => {
        alert("登録中にエラーが発生しました。\n時間が経ってから再度お試しください。");
    });
}


// 画像が選択されたら
document.getElementById("file").addEventListener("change", async (event) => {

    // ボタンを無効化(初期化)
    register.disabled = true;

    // 画像があるか確認
    executionLog2("画像を確認しています...");
    var select_img = event.target.files[0];
    if (!select_img) {
        // 画像がない
        executionLog2("[エラー]画像が選択されていません", "error");
        return false;
    }

    // 画像を読み込み、imgタグに出力
    executionLog2("画像を読み込んでいます...");
    var read_img = new FileReader();
    read_img.onload = (e) => {
        preview.style.display = "block";
        previewImg.src = e.target.result;
    };
    read_img.readAsDataURL(select_img);

    // 分析処理開始
    previewImg.onload = async () => {
        executionLog2("<< 分析を開始しました >>", "complete");

        // サイズを取得
        executionLog2("サイズを確認中...");
        var size = { width: previewImg.width, height: previewImg.height };
        overlay.width = size.width;
        overlay.height = size.height;

        // これを追加しろとエラーが出たため追加
        // しかし、エラーが消えない。拡張機能が原因で表示され続けるらしいのでもう放置
        var ctx = overlay.getContext("2d", { willReadFrequently: true });

        // 顔特徴量を分析(face-api)
        executionLog2("顔検出・顔特徴量計測中...");
        var detections = await faceapi.detectAllFaces(previewImg)
            .withFaceLandmarks()
            .withFaceDescriptors();

        // 検出された顔の数
        var faceCount = detections.length;

        if (faceCount == 0) {
            // 顔が検出されなかった場合
            executionLog2("[エラー]顔が検出されませんでした", "error");
            return false;
        }

        if (faceCount > 1) {
            // 顔が複数検出された場合
            executionLog2("[エラー]複数の顔が検出されました。対象の顔しか写っていない写真を選択してください。", "error");
            return false;
        }

        // jsonとして保存
        descriptorData = detections[0].descriptor;

        // リサイズ
        executionLog2("描写準備中...");
        faceapi.matchDimensions(overlay, size);
        var resizedDetections = faceapi.resizeResults(detections, size);

        // 顔検出のボックス・ランドマークを描画
        executionLog2("描写中...");
        faceapi.draw.drawDetections(overlay, resizedDetections);
        faceapi.draw.drawFaceLandmarks(overlay, resizedDetections);
        executionLog2("描写完了");

        // ボタンを有効化
        register.disabled = false;

        executionLog2("<< 分析が無事に終了しました >>", "complete");
    };
});


// 読み込まれたら実行
window.onload = function () {
    // モデルを読み込む
    loadModels();
}