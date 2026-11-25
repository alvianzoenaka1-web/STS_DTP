
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

    $stmt = $pdo->prepare(
        "SELECT id, username, password FROM users WHERE username = ?"
    );

    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header("Location: dashboard.php");
        exit;
    } else {
        $pesan = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | LaporFasilitas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<main class="auth-layout">
    <section class="auth-intro">
        <div class="auth-mark" aria-hidden="true">L</div>
        <h1>Fasilitas sekolah lebih terawat.</h1>
        <p>Catat kerusakan, pantau tindak lanjut, dan bantu lingkungan belajar tetap nyaman.</p>
    </section>

    <section class="auth-card">
        <h2>Selamat datang</h2>
        <p class="auth-subtitle">Masuk untuk mengelola laporan fasilitas.</p>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success" role="status">Akun berhasil dibuat. Silakan masuk.</div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       autocomplete="username" maxlength="50" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary auth-submit">Masuk</button>
        </form>

        <p class="auth-foot">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </section>
</main>
</body>
</html>
