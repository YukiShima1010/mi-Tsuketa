// Service Worker
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("https://" + domain + "/sw.js")
    .then(() => console.log("Service Workerの登録が完了しました。"))
    .catch(error => console.error("Service Workerの登録に失敗しました：", error));
}


// PWAをインストール
let deferredPrompt;
window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();
    deferredPrompt = event;
    showInstallButton();
});

installButton.addEventListener("click", () => {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(choiceResult => {
      if (choiceResult.outcome === "accepted") {
        console.log("PWAがインストールされました。");
      }
    });
  }
});


// ログアウト
function logout() {
    if (window.confirm("本当にログアウトしますか？")) {
        window.location.href = "https://" + domain + "/logout/?msg=true"; // メッセージありでログアウトさせる
    }
}


// パスキー
function passkey() {
    alert("ごめんなさい！！期限までに実装できませんでした、、、");
}


// サイドメニュー
function headerMenu() {
    document.querySelector(".side-menu").classList.toggle("open");
}


// 背景がクリックされた際メニューを非表示
document.addEventListener("click", function(event) {
    const sideMenuId = document.querySelector(".side-menu");
    const hamburgerMenuId = document.querySelector(".hamburger-menu");

    if (!sideMenuId.contains(event.target) && !hamburgerMenuId.contains(event.target)) {
        sideMenuId.classList.remove("open");
    }
});