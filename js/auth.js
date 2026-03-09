document.getElementById("auth-form").addEventListener("submit", (e) => {
    e.preventDefault();

    const login    = document.getElementById("auth-login").value.trim();
    const password = document.getElementById("auth-password").value;
    const errorEl  = document.getElementById("error");
    const btn      = document.getElementById("auth-button");

    errorEl.innerText = "";

    if (!login || !password) {
        errorEl.innerText = "Заполните все поля";
        return;
    }

    btn.disabled    = true;
    btn.innerText   = "Вход...";

    fetch("/php/auth.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ login, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = "/dashboard.html";
        } else {
            errorEl.innerText = data.message || "Ошибка авторизации";
            btn.disabled  = false;
            btn.innerText = "Войти";
        }
    })
    .catch(() => {
        errorEl.innerText = "Ошибка соединения с сервером";
        btn.disabled  = false;
        btn.innerText = "Войти";
    });
});
