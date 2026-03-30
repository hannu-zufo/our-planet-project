<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Credentials - change these
    $valid_username = 'admin';
    $valid_password_hash = password_hash('changeme123', PASSWORD_DEFAULT);

    // Store hash in a config file if it doesn't exist
    $config_file = __DIR__ . '/config.php';
    if (file_exists($config_file)) {
        require $config_file;
    }

    if ($username === $valid_username && password_verify($password, $valid_password_hash)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Admin | Our Planet Project Foundation</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1a1a1a;
            --paper: #fdfcfb;
            --accent: #A14068;
            --border: #e5e5e5;
            --serif: 'Libre Baskerville', serif;
            --sans: 'Inter', sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--ink); font-family: var(--sans); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: var(--paper); padding: 50px 40px; width: 100%; max-width: 420px; }
        .login-box h1 { font-family: var(--serif); font-size: 1.8rem; margin-bottom: 6px; }
        .login-box .subtitle { color: #666; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 30px; }
        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .field input { width: 100%; padding: 12px 14px; border: 1px solid var(--border); font-family: var(--sans); font-size: 0.95rem; background: #fff; }
        .field input:focus { outline: 2px solid var(--accent); border-color: transparent; }
        .btn { width: 100%; padding: 14px; background: var(--accent); color: #fff; border: none; font-family: var(--sans); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; }
        .btn:hover { opacity: 0.9; }
        .error { background: #fde8e8; color: #c0392b; padding: 12px 14px; font-size: 0.85rem; margin-bottom: 20px; border-left: 3px solid #c0392b; }
        .accent-bar { width: 40px; height: 3px; background: var(--accent); margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="accent-bar"></div>
        <p class="subtitle">Our Planet Project Foundation</p>
        <h1>Blog Admin</h1>
        <p style="color:#666; font-size:0.9rem; margin: 15px 0 25px;">Sign in to manage your blog posts.</p>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
    </div>
</body>
</html>
