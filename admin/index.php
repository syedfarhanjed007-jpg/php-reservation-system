<?php
/**
 * Admin Panel — Login
 */

session_start();

require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/config/config.php';

use App\Config\Database;
use App\Includes\Security;

$error = null;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username']) && !empty($_POST['password'])) {
    try {
        $user = Database::query(
            "SELECT * FROM admin_users WHERE username = ? AND is_active = 1",
            [$_POST['username']]
        )->fetch();

        if ($user && Security::verifyPassword($_POST['password'], $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];

            Database::query(
                "UPDATE admin_users SET last_login = NOW() WHERE id = ?",
                [$user['id']]
            );

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } catch (\Exception $e) {
        $error = 'System error. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif; background: #0f172a; color: #f8fafc;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: #1e293b; border: 1px solid #334155; border-radius: 16px;
            padding: 40px; width: 100%; max-width: 400px;
        }
        .login-card h1 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .login-card p { color: #94a3b8; font-size: 14px; margin-bottom: 28px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        input {
            width: 100%; padding: 11px 14px; font-size: 15px; font-family: 'Inter', sans-serif;
            background: #0f172a; border: 1.5px solid #334155; border-radius: 8px;
            color: #f8fafc; outline: none; transition: all 0.2s;
        }
        input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        button {
            width: 100%; padding: 12px; font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif;
            background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer;
            transition: all 0.2s; margin-top: 8px;
        }
        button:hover { background: #2563eb; }
        .error { background: #7f1d1d; color: #fca5a5; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .back { text-align: center; margin-top: 16px; }
        .back a { color: #64748b; font-size: 13px; text-decoration: none; }
        .back a:hover { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Admin Login</h1>
        <p>Enter your credentials to manage reservations.</p>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="admin">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit">Sign In</button>
        </form>

        <div class="back">
            <a href="../index.php">← Back to booking page</a>
        </div>
    </div>
</body>
</html>
