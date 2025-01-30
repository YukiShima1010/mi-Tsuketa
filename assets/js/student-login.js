// Service Worker
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("https://" + domain + "/sw.js")
    .then(() => console.log("Service Workerの登録が完了しました。"))
    .catch(error => console.error("Service Workerの登録に失敗しました：", error));
}


// 読み込まれた際に実行
window.onload = function () {
    if (error != "null") {
        // エラーメッセージがある場合
        var error_message = document.getElementById("error-message");

        switch (error) {
            case "email_is_invalid":
                error_message.textContent = "正しいメールアドレスを入れてください";
                break;
            case "email_is_not_exist":
                error_message.textContent = "メールアドレスが間違っています";
                break;
            case "admin_login_error":
                error_message.textContent = "ログインに失敗しました";
                break;
            case "recaptcha_error":
                error_message.textContent = "reCAPTCHA認証に失敗しました";
                break;
            case "database_error":
                error_message.textContent = "データベースでエラーが発生しました。";
                break;
            case "mail_rate_error":
                alert("5分以内に送信されたログインURLがあります。\nメールボックスをご確認ください。");
                error_message.textContent = "5分以内に送信されたメールがあります";
                break;
            default:
                error_message.textContent = "処理中にエラーが発生しました";
                break;
        }

        // エラーメッセージを表示
        error_message.style.display = "block";
    }
};


// ログインフォームが送信されたら
document.getElementById("loginForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    const response = await fetch("https://" + domain + "/app/api/student_mail_login.php/?url=" + url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        // { status: 200 } or {status: 500, error_message: "ここにエラーメッセージ"}
        if (data.status === 200) {
            alert("メールアドレスにログインURLを送信しました。\nメールボックスをご確認ください。");
            window.location.href = "https://" + domain + "/login";
        } else {
            window.location.href = "https://" + domain + "/login/?url=" + url + "&error=" + data.error_code;
        }
    })
    .catch(error => {
        alert("生徒情報変更中にエラーが発生しました。");
    });
});


// reCAPTCHA v3のトークンを取得してフォームにセット
grecaptcha.ready(function () {
    grecaptcha.execute(recaptcha_site_key, { action: "submit" }).then(function (token) {
        document.getElementById("recaptchaResponse").value = token;
    });
});


// パスキー
function passkey() {
    alert("ごめんなさい！！期限までに実装できませんでした、、、");
}