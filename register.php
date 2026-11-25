<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($username) > 50) {
        $pesan = "Username tidak valid.";
    } elseif (strlen($password) < 8) {
        $pesan = "Password minimal 8 karakter.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, password) VALUES (?, ?)"
            );
            $stmt->execute([$username, $hash]);

            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $pesan = "Username sudah digunakan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar | LaporFasilitas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<main class="auth-layout">
    <section class="auth-intro">
        <div class="auth-mark" aria-hidden="true">L</div>
        <h1>Mulai kelola laporan dengan mudah.</h1>
        <p>Buat akun untuk mencatat dan memantau perbaikan fasilitas sekolah.</p>
    </section>

    <section class="auth-card">
        <h2>Buat akun</h2>
        <p class="auth-subtitle">Daftar menggunakan username dan password.</p>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       autocomplete="username" maxlength="50" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       autocomplete="new-password" minlength="8" required>
                <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <button type="submit" class="btn btn-primary auth-submit">Daftar</button>
        </form>

        <p class="auth-foot">Sudah punya akun? <a href="login.php">Masuk</a></p>
    </section>
</main>
</body>
</html>
