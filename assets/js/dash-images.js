// モーダルを開く
function openModal(img) {
    document.getElementById("modal").style.display = "flex"; // モーダル表示
    document.getElementById("modal-img").src = img.src + "&type=large"; // 画像挿入
    document.getElementById("modal-caption").innerHTML = img.alt; // 画像のキャプション挿入
    document.getElementById("comment").innerHTML = img.getAttribute("data-comment"); // コメント挿入
    document.getElementById("modal-dl-btn").href = img.src + "&type=large"; // 保存先URL挿入
    document.getElementById("modal-dl-btn").download = img.alt + ".png"; // ダウンロード挿入
}


// モーダルを閉じる(背景クリック)
function closeModal(event) {
    if (event.target === document.getElementById("modal")) {
        document.getElementById("modal").style.display = "none";
    }
}


// 生徒を変更
async function studentChange() {
    // 全てのチェックボックスを取得
    var checkBox = document.querySelectorAll(".checkbox");

    // "チェック"されたチェックボックスを取得(笑)
    let selectedImages = [];

    checkBox.forEach(function(checkBox) {
        if (checkBox.checked) {
            selectedImages.push(checkBox.value);
        }
    });

    // 「操作する項目」を取得
    let selectedImagesList = "";
    var select = document.getElementById("select").value;
    if (select == 2) {
        // 選択以外
        selectedImagesList = Array.from(checkBox)
            .filter(checkbox => !selectedImages.includes(checkbox.value))
            .map(checkbox => checkbox.value);
    } else {
        selectedImagesList = selectedImages;
    }

    if (selectedImagesList.length === 0) {
        // 何一つ選択されなかった
        alert("少なくとも一つは選択してください。");
        return false;
    }

    // ページへ移動
    location.href = "http://" + domain + "/dashboard/media-change/?media_id=" + encodeURIComponent(selectedImagesList);
};


// 生徒を削除
async function studentDelete() {
    // "チェック"されたチェックボックスを取得(笑)
    var checkBox = document.querySelectorAll(".checkbox");
    let selectedImages = [];

    checkBox.forEach(function(checkBox) {
        if (checkBox.checked) {
            selectedImages.push(checkBox.value);
        }
    });

    // 「選択以外」の場合の表示準備
    let selectedImagesList = "";
    var select = document.getElementById("select").value;
    if (select == 2) {
        selectedImagesList = Array.from(checkBox)
            .filter(checkbox => !selectedImages.includes(checkbox.value))
            .map(checkbox => checkbox.value);
    } else {
        selectedImagesList = selectedImages;
    }

    if (selectedImagesList.length === 0) {
        // 何一つ選択されなかった
        alert("少なくとも一つは選択してください。");
        return false;
    }

    // ひたすらに確認する
    if (window.confirm("写真を削除すると、再度アップロードするまで画像は戻りません。\n↓ 「今一度、削除する写真を確認してください」 ↓\n" + selectedImagesList)) {
        // 削除APIにPOSTする
        var response = await fetch("https://" + domain + "/app/api/media_delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                student_id: selectedImagesList,
                select: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            // { status: 200 } or {status: 500, error_message: "ここにエラーメッセージ"}
            if (data.status == 200) {
                alert("画像の削除に成功しました。");
                window.location.reload();
            } else {
                alert("画像の削除に失敗しました。\n" + data.error_message);
                window.location.reload();
            }
        })
        .catch(error => {
            alert("画像の削除中にエラーが発生しました。");
            window.location.reload();
        });
    }
};


// モーダルを閉じる(ボタンクリック)
document.getElementById("modal-close").addEventListener("click", function() {
    document.getElementById("modal").style.display = "none";
});


// 全体操作のチェックボックス
document.getElementById("checkbox").addEventListener("change", function () {
    document.querySelectorAll(".checkbox").forEach(checkbox => {
        checkbox.checked = document.getElementById("checkbox").checked;
    });
});