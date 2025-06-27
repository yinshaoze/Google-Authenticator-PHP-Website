<?php
require 'utils.php';

$error = '';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputCaptcha = $_POST['captcha'] ?? '';
    $action = $_POST['action'];
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    if ($action === 'login') {
        if (verifyPassword($username, $password)) {
            $_SESSION['user'] = $username;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "用户名或密码错误。";
        }
    } elseif ($action === 'register') {
        if (empty($inputCaptcha) || strtolower($inputCaptcha) !== strtolower($_SESSION['captcha_code'] ?? '')) {
            $error = "验证码错误，请重新输入。";
        }else{
            if (userExists($username)) {
                $error = "该用户名已存在。";
            } else {
                $userDir = USER_DIR . '/' . $username;
                mkdir($userDir, 0700, true);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                file_put_contents($userDir . '/password.txt', $hashedPassword);
                file_put_contents($userDir . '/secrets.txt', '');
                $_SESSION['user'] = $username;
                header("Location: dashboard.php");
                exit;
            }
        }

    }
    
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {



}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 / 注册</title>
    <style>
        :root {
            --primary: #00b4d8;
            --background: #1e1e2f;
            --foreground: #2e2e42;
            --text: #ffffff;
            --muted: #aaaaaa;
            --error: #ff4d4f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: var(--background);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }

        .container {
            background: var(--foreground);
            border-radius: 16px;
            padding: 30px 24px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
        }

        h2 {
            text-align: center;
            margin-bottom: 24px;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
        }

        .tabs button {
            flex: 1;
            padding: 12px;
            background: #3b3b55;
            color: var(--muted);
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s, color 0.3s;
        }

        .tabs button:hover {
            background: #4c4c6a;
        }

        .tabs button.active {
            background: var(--primary);
            color: #fff;
        }

        .form-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .form-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        form input {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            background: #2a2a3d;
            border: 1px solid #444;
            border-radius: 8px;
            color: var(--text);
        }

        form input::placeholder {
            color: var(--muted);
        }

        form button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        form button:hover {
            background: #0096c7;
        }

        .error {
            color: var(--error);
            text-align: center;
            margin-bottom: 10px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px 16px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align: center; margin-bottom: 16px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="#00b4d8" stroke="#00b4d8" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" >
            <path d="M12 2L3 7v7a9 9 0 0 0 18 0V7l-9-5z"/>
            <path d="M12 12v5"/>
            <path d="M12 17a1 1 0 0 0 1-1"/>
        </svg>
    </div>
    <h2>Google 身份验证器</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="tabs">
        <button id="tab-login" class="active" onclick="switchTab('login')">登录</button>
        <button id="tab-register" onclick="switchTab('register')">注册</button>
    </div>

    <div id="login" class="form-section active">
        <form method="post">
            <input type="hidden" name="action" value="login">
            <input type="text" name="username" placeholder="用户名" required>
            <input type="password" name="password" placeholder="密码" required>
            <button type="submit">登录</button>
        </form>
    </div>

    <div id="register" class="form-section">
        <form method="post">
            <input type="hidden" name="action" value="register">
            <input type="text" name="username" placeholder="用户名" required>
            <input type="password" name="password" placeholder="密码" required>
        <div style="display: flex; align-items: center; gap: 12px; margin-top: 12px;">
            <img src="captcha.php" alt="验证码" style="height: 40px; border-radius: 8px; cursor: pointer;" onclick="this.src='captcha.php?'+Math.random()" title="点击刷新验证码">
            <input type="text" name="captcha" placeholder="请输入验证码" required style="margin: 0px 0px 0px 7px;height: 40px; flex: 1; padding: 8px; border-radius: 8px; border: 1px solid #444; background: #2a2a3d; color: #fff;">
        </div>
            <button type="submit">注册</button>
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
        document.getElementById(tab).classList.add('active');

        document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
    }
</script>
</body>
</html>
