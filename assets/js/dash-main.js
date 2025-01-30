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