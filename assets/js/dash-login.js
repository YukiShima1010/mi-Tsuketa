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