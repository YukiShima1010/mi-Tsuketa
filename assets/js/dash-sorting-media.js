// 初期変数
const executionLog = document.getElementById("execution-log");


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
}


// 振り分けボタン
async function sorting() {
    executionLog2("<< 処理を開始します >>", "complete");

    // モデルを読み込み
    document.getElementById("log-div").style.display = "block";
    await loadModels();

    // 振り分け準備
    const result = {}
    executionLog2(img_faces.length + "枚の画像の顔情報を読み込みました。");
    executionLog2(student_faces.length + "人の生徒の顔情報を読み込みました。");

    // 振り分け処理
    executionLog2("振り分けています...");
    student_faces.forEach(student_item => {
        var student_id = student_item.id;
        var student_item_face = student_item.face;

        img_faces.forEach(img_item => {
            img_item.face.forEach(img_item_face => {
                // 画像の顔と生徒の顔を比べる
                const distance = faceapi.euclideanDistance(img_item_face, student_item_face);

                // 類似度を確認(configで設定するsimilarityの値以下なら一致)
                if (distance < similarity) {
                    // 枠なかったら作る
                    if (!result[img_item.id]) {
                        result[img_item.id] = [];
                    }

                    // 生徒を保存
                    if (!result[img_item.id].includes(student_id)) {
                        result[img_item.id].push(student_id);
                    }
                }
            });
        });
    });
    executionLog2("振り分けが完了しました", "complete");

    // APIにPOSTする
    executionLog2("結果をサーバーに保存しています...");
    var response = await fetch("https://" + domain + "/app/api/sorting_media.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(result)
    })
    .then(response => response.json())
    .then(data => {
        // { status: 200 } or {status: 500, error_message: "ここにエラーメッセージ"}
        if (data.status == 200) {
            executionLog2("保存が完了しました", "complete");
            executionLog2("<< 処理が全て無事に完了しました >>", "complete");
            alert("写真振り分けが完了しました。");
            return false;
        } else {
            executionLog2("振り分け中にエラーが発生しました：" + data.error_message, "error");
            alert("振り分け中にエラーが発生しました。" + data.error_message);
            return false;
        }
    })
    .catch(error => {
        executionLog2("振り分け中にエラーが発生しました", "error");
        alert("振り分け中にエラーが発生しました。");
    });
}


// 1分に1度リロードする(ページ読み込み時にDBから値を取るので、リロードしないままだと古い情報で実行されちゃう)
setInterval(() => {
    location.reload();
}, 60000);