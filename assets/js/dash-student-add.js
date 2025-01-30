// 登録ボタン
document.getElementById("form-button").addEventListener("click", function () {
    event.preventDefault();

    // フォームデータを収集
    var formData = new FormData(document.getElementById("studentAddForm"));

    // 一応確認
    if (window.confirm("本当に登録してもよろしいですか？")) {

        // 削除APIにPOSTする
        var response = fetch("https://" + domain + "/app/api/student_add.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // { status: 200 } or {status: 500, error_message: "ここにエラーメッセージ"}
            if (data.status == 200) {
                alert("生徒登録に成功しました。");
                location.href = "https://" + domain + "/dashboard/";
            } else {
                alert("生徒登録に失敗しました。\n" + data.error_message);
            }
        })
        .catch(error => {
            alert("生徒登録中にエラーが発生しました。");
        });
    }
});