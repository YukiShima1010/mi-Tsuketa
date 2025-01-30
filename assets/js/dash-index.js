// 全体操作のチェックボックス
document.getElementById("checkbox").addEventListener("change", function () {
    document.querySelectorAll(".checkbox").forEach(checkbox => {
        checkbox.checked = document.getElementById("checkbox").checked;
    });
});


// 生徒を変更
async function studentChange() {
    // 全てのチェックボックスを取得
    var checkBox = document.querySelectorAll(".checkbox");

    // "チェック"されたチェックボックスを取得(笑)
    let selectedStudents = [];

    checkBox.forEach(function(checkBox) {
        if (checkBox.checked) {
            selectedStudents.push(checkBox.value);
        }
    });

    // 「操作する項目」を取得
    let selectedStudentsList = "";
    var select = document.getElementById("select").value;
    if (select == 2) {
        // 選択以外
        selectedStudentsList = Array.from(checkBox)
            .filter(checkbox => !selectedStudents.includes(checkbox.value))
            .map(checkbox => checkbox.value);
    } else {
        selectedStudentsList = selectedStudents;
    }

    if (selectedStudentsList.length === 0) {
        // 何一つ選択されなかった
        alert("少なくとも一つは選択してください。");
        return false;
    }

    // ページへ移動
    location.href = "http://" + domain + "/dashboard/student-change/?student=" + encodeURIComponent(selectedStudentsList);
};


// 生徒を削除
async function studentDelete() {
    // "チェック"されたチェックボックスを取得(笑)
    var checkBox = document.querySelectorAll(".checkbox");
    let selectedStudents = [];

    checkBox.forEach(function(checkBox) {
        if (checkBox.checked) {
            selectedStudents.push(checkBox.value);
        }
    });

    // 「選択以外」の場合の表示準備
    let selectedStudentsList = "";
    var select = document.getElementById("select").value;
    if (select == 2) {
        selectedStudentsList = Array.from(checkBox)
            .filter(checkbox => !selectedStudents.includes(checkbox.value))
            .map(checkbox => checkbox.value);
    } else {
        selectedStudentsList = selectedStudents;
    }

    if (selectedStudentsList.length === 0) {
        // 何一つ選択されなかった
        alert("少なくとも一つは選択してください。");
        return false;
    }

    // ひたすらに確認する
    if (window.confirm("生徒を削除すると、顔・メールなどの一切の情報が削除されます。\n↓ 「今一度、削除する生徒を確認してください」 ↓\n" + selectedStudentsList)) {
        if (window.confirm("【最後の確認です】\n下記の生徒を削除します。今一度生徒を確認してください。\n↓ 「本当に下記の生徒を削除しますか？」 ↓\n" + selectedStudentsList)) {
            // 削除APIにPOSTする
            var response = await fetch("https://" + domain + "/app/api/student_delete.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    student_id: selectedStudentsList,
                    select: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                // { status: 200 } or {status: 500, error_message: "ここにエラーメッセージ"}
                if (data.status == 200) {
                    alert("生徒の削除に成功しました。");
                    window.location.reload();
                } else {
                    alert("生徒の削除に失敗しました。\n" + data.error_message);
                    window.location.reload();
                }
            })
            .catch(error => {
                alert("生徒削除中にエラーが発生しました。");
                window.location.reload();
            });
        }
    }
};