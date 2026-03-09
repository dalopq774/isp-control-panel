// ─── Проверка сессии ───────────────────────────────────────────────────────
fetch("/php/check_session.php")
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            window.location.href = "/index.html";
        } else {
            document.getElementById("user-label").innerText = data.login;
            loadNetworkInfo();
        }
    })
    .catch(() => { window.location.href = "/index.html"; });

// ─── Вкладки ───────────────────────────────────────────────────────────────
const tabBtns     = document.querySelectorAll(".tab-btn");
const tabContents = document.querySelectorAll(".tab-content");

tabBtns.forEach(btn => {
    btn.addEventListener("click", () => {
        const target = btn.dataset.tab;

        tabBtns.forEach(b => b.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));

        btn.classList.add("active");
        document.getElementById("tab-" + target).classList.add("active");

        if (target === "recommendations") loadRecommendations();
        if (target === "troubleshoot")    loadTroubleshoot();
    });
});

// ─── Вкладка: Сеть ─────────────────────────────────────────────────────────
function loadNetworkInfo() {
    fetch("/php/network.php")
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            const statusColor = data.status === "connected" ? "green" : "red";
            const statusText  = data.status === "connected" ? "Подключено" : "Отключено";

            document.getElementById("network-info").innerHTML = `
                <label class="section-title">Состояние сети</label>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-key">Статус интернета</span>
                        <span class="info-val" style="color:${statusColor}">${statusText}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Тип подключения</span>
                        <span class="info-val">${escHtml(data.connection_type)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Внешний IP-адрес</span>
                        <span class="info-val dashboard-span-ip">${escHtml(data.external_ip)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Ваш IP (запрос)</span>
                        <span class="info-val dashboard-span-ip">${escHtml(data.client_ip)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Обновлено</span>
                        <span class="info-val">${escHtml(data.updated_at)}</span>
                    </div>
                </div>
            `;

            // Логи входов
            const tbody = document.getElementById("auth-log-body");
            if (data.auth_logs && data.auth_logs.length > 0) {
                tbody.innerHTML = data.auth_logs.map(log => `
                    <tr>
                        <td>${escHtml(log.ip)}</td>
                        <td class="${log.status === 'success' ? 'log-success' : 'log-fail'}">
                            ${log.status === 'success' ? 'Успешно' : 'Неудача'}
                        </td>
                        <td>${escHtml(log.created_at)}</td>
                    </tr>
                `).join("");
            } else {
                tbody.innerHTML = "<tr><td colspan='3'>Нет записей</td></tr>";
            }
        })
        .catch(() => {
            document.getElementById("network-info").innerHTML =
                "<p class='error-text'>Ошибка загрузки данных сети</p>";
        });
}

// ─── Вкладка: Утилиты ──────────────────────────────────────────────────────
const outputEl    = document.getElementById("util-output");
const outputTitle = document.getElementById("util-output-title");

function setOutput(title, text) {
    outputTitle.innerText = title;
    outputEl.innerText    = text;
}

function runUtil(action, host, title) {
    setOutput(title, "Выполняется...");
    const body = new URLSearchParams({ action });
    if (host) body.append("host", host);

    fetch("/php/diagnostics.php", { method: "POST", body })
        .then(r => r.json())
        .then(data => {
            setOutput(title, data.result || data.message || "Нет данных");
        })
        .catch(() => setOutput(title, "Ошибка соединения с сервером"));
}

document.getElementById("ping-btn").addEventListener("click", () => {
    const host = document.getElementById("ping-host").value.trim() || "google.com";
    runUtil("ping", host, `Ping → ${host}`);
});

document.getElementById("dns-btn").addEventListener("click", () => {
    const host = document.getElementById("dns-host").value.trim() || "google.com";
    runUtil("dns", host, `DNS Lookup → ${host}`);
});

document.getElementById("whois-btn").addEventListener("click", () => {
    const host = document.getElementById("whois-host").value.trim() || "8.8.8.8";
    runUtil("whois", host, `WHOIS → ${host}`);
});

document.getElementById("speed-btn").addEventListener("click", () => {
    runUtil("speedtest", null, "Тест скорости");
});

document.getElementById("clear-output").addEventListener("click", () => {
    outputTitle.innerText = "Результат";
    outputEl.innerText    = "Выберите утилиту и нажмите кнопку...";
});

// ─── Вкладка: Рекомендации ─────────────────────────────────────────────────
let recommendationsLoaded = false;
function loadRecommendations() {
    if (recommendationsLoaded) return;
    recommendationsLoaded = true;

    fetch("/php/recommendations.php")
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const el = document.getElementById("recommendations-list");
            el.innerHTML = data.recommendations.map(rec => `
                <div class="rec-card priority-${rec.priority}">
                    <div class="rec-header">
                        <span class="rec-title">${escHtml(rec.title)}</span>
                        <span class="rec-badge ${rec.priority}">${rec.priority === 'high' ? 'Важно' : 'Средне'}</span>
                    </div>
                    <p class="rec-desc">${escHtml(rec.description)}</p>
                </div>
            `).join("");
        })
        .catch(() => {
            document.getElementById("recommendations-list").innerHTML =
                "<p class='error-text'>Ошибка загрузки рекомендаций</p>";
        });
}

// ─── Вкладка: Решение проблем ──────────────────────────────────────────────
let troubleshootLoaded = false;
function loadTroubleshoot() {
    if (troubleshootLoaded) return;
    troubleshootLoaded = true;

    fetch("/php/troubleshoot.php")
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const el = document.getElementById("troubleshoot-list");
            el.innerHTML = data.problems.map(p => `
                <div class="trouble-card">
                    <button class="trouble-header" onclick="toggleTrouble('${p.id}')">
                        <span>${escHtml(p.title)}</span>
                        <span class="trouble-arrow" id="arrow-${p.id}">▶</span>
                    </button>
                    <div class="trouble-steps" id="steps-${p.id}">
                        <ol>
                            ${p.steps.map(s => `<li>${escHtml(s)}</li>`).join("")}
                        </ol>
                    </div>
                </div>
            `).join("");
        })
        .catch(() => {
            document.getElementById("troubleshoot-list").innerHTML =
                "<p class='error-text'>Ошибка загрузки</p>";
        });
}

function toggleTrouble(id) {
    const el    = document.getElementById("steps-" + id);
    const arrow = document.getElementById("arrow-" + id);
    const open  = el.classList.toggle("open");
    arrow.innerText = open ? "▼" : "▶";
}

// ─── Утилиты ───────────────────────────────────────────────────────────────
function escHtml(str) {
    return String(str ?? "")
        .replace(/&/g,"&amp;")
        .replace(/</g,"&lt;")
        .replace(/>/g,"&gt;");
}
