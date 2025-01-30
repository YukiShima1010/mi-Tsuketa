// Service Worker
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("https://" + domain + "/sw.js")
    .then(() => console.log("Service Workerの登録が完了しました。"))
    .catch(error => console.error("Service Workerの登録に失敗しました：", error));
}


// ログアウト
function logout() {
    if (window.confirm("本当にログアウトしますか？")) {
        window.location.href = "https://" + domain + "/logout/?msg=true"; // メッセージありでログアウトさせる
    }
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


// モーダルを閉じる(ボタンクリック)
document.getElementById("modal-close").addEventListener("click", function() {
    document.getElementById("modal").style.display = "none";
});