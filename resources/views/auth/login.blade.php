<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — Starget</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            width: 100%;
            max-width: 420px;
            padding: 48px 40px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: #2563eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
        }

        .logo-text {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -.5px;
        }

        h1 {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 28px;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            color: #111827;
            outline: none;
            transition: border-color .15s;
            background: #fff;
        }

        input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

        .btn {
            width: 100%;
            padding: 11px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover { background: #1d4ed8; }
        .btn:disabled { background: #93c5fd; cursor: not-allowed; }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            display: none;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <div class="logo-icon">S</div>
            <span class="logo-text">Starget</span>
        </div>

        <h1>Добро пожаловать</h1>
        <p class="subtitle">Войдите в систему управления логистикой</p>

        <div class="alert alert-error" id="alertError"></div>

        <form id="loginForm" autocomplete="off">
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="user@starget.kz" required autofocus>
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">Войти</span>
            </button>
        </form>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const spinner = document.getElementById('spinner');
        const btnText = document.getElementById('btnText');
        const submitBtn = document.getElementById('submitBtn');
        const alertError = document.getElementById('alertError');

        function showError(msg) {
            alertError.textContent = msg;
            alertError.style.display = 'block';
        }

        function hideError() {
            alertError.style.display = 'none';
        }

        function setLoading(loading) {
            submitBtn.disabled = loading;
            spinner.style.display = loading ? 'block' : 'none';
            btnText.textContent = loading ? 'Вход...' : 'Войти';
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            hideError();
            setLoading(true);

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email: document.getElementById('email').value,
                        password: document.getElementById('password').value,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    showError(data.message || 'Неверный email или пароль.');
                    return;
                }

                localStorage.setItem('starget_token', data.data.token);
                localStorage.setItem('starget_user', JSON.stringify(data.data.user));

                window.location.href = '/dashboard';

            } catch (err) {
                showError('Ошибка соединения с сервером.');
            } finally {
                setLoading(false);
            }
        });
    </script>
</body>
</html>
