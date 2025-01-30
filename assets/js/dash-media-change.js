// 変更ボタン
document.getElementById("studentChangeForm").addEventListener("submit", function(event) {
    event.preventDefault();

    // フォームの全データを取得
    let formData = [];
    var ids = document.querySelectorAll('input[name="id"]');
    var names = document.querySelectorAll('input[name="name"]');
    var comments = document.querySelectorAll('input[name="comment"]');

    for (let i = 0; i < ids.length; i++) {
        let changeData = {
            id: ids[i].value,
            name: names[i].value,
            comment: comments[i].value
        };
        formData.push(changeData);
    }

    // 一応確認
    if (window.confirm("本当に変更してもよろしいですか？")) {

        // 変更APIにPOSTする
        fetch("https://" + domain + "/app/api/media_change.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            // { status: 200 } or {status: 500, error_message: "ここにエラーメッセージ"}
            if (data.status === 200) {
                alert("情報変更に成功しました。");
                location.href = "https://" + domain + "/dashboard/images/";
            } else {
                alert("情報更新に失敗しました。\n" + data.error_message);
            }
        })
        .catch(error => {
            alert("生徒情報変更中にエラーが発生しました。");
        });
    }
});


// 変更点の背景を変える
document.querySelectorAll("form input").forEach(input => {
    input.addEventListener("input", () => {
        if (input.value !== input.placeholder) {
            // もし変更があった場合
            input.style.backgroundColor = "lightyellow";
        } else {
            // 変更がなかった場合
            input.style.backgroundColor = "";
        }
    });
});