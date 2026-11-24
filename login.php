<?php
require 'config.php';

if (!empty($_SESSION['user'])) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $username = trim($_POST['username'] ?? '');
    $password =$_POST['password'] ?? '';

    if ($username === '' || $password === '') {$error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $db->prepare('SELECT id_user, username, password, role FROM user WHERE username = ?');$stmt->bind_param('s', $username);$stmt->execute();
        $u =$stmt->get_result()->fetch_assoc();

        if ($u && password_verify($password,$u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'       => $u['id_user'],
                'username' => $u['username'],
                'role'     => $u['role'],
            ];
            redirect('index.php');
        }
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | LaundryCrafty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fcf8f2 !important;
            font-family: 'Georgia', 'Times New Roman', serif;
            color: #2b2b2b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-custom {
            background-color: #5c1d24 !important;
            border-bottom: 3px solid #4a151b;
        }
        .navbar-custom .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            font-style: italic;
            color: #ffffff !important;
        }
        .login-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .card-login {
            background-color: #ffffff;
            border: 1px solid #e8dec9;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 440px;
            padding: 2.2rem;
        }
        .login-title {
            font-style: italic;
            font-weight: bold;
            color: #5c1d24;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: bold;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 0.9rem;
            color: #333;
        }
        .form-control {
            border: 1px solid #d4c5b3;
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
        }
        .form-control:focus {
            border-color: #5c1d24;
            box-shadow: 0 0 0 0.25rem rgba(92, 29, 36, 0.2);
        }
        .btn-marun {
            background-color: #5c1d24;
            border-color: #5c1d24;
            color: #ffffff;
            font-family: system-ui, -apple-system, sans-serif;
            font-weight: 500;
            padding: 0.5rem 1.4rem;
            border-radius: 6px;
        }
        .btn-marun:hover {
            background-color: #4a151b;
            border-color: #4a151b;
            color: #ffffff;
        }
        footer {
            color: #7d6b58;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="login.php">LaundryCrafty</a>
    </div>
</nav>

<div class="login-wrapper">
    <div class="card-login">
        <h2 class="login-title">Masuk ke LaundryCrafty</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-marun">Masuk</button>
        </form>

        <div class="mt-4 pt-2 border-top text-muted small" style="font-family: system-ui, sans-serif;">
            Akun awal: <strong>admin</strong> / <strong>admin123</strong> atau <strong>kasir</strong> / <strong>kasir123</strong>
        </div>
    </div>
</div>

<footer class="text-center py-3">
    &copy; <?= date('Y') ?> LaundryCrafty &middot