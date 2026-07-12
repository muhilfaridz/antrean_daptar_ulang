<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin.php");
        exit;
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: ../petugas/panggil.php");
        exit;
    }
}

$db = new Database();
$conn = $db->getConnection();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['loket'] = $user['loket'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/admin.php");
            exit;
        } else {
            header("Location: ../petugas/panggil.php");
            exit;
        }
    } else {
        $message = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="card auth-card">
        <div class="auth-header">
            <div class="auth-logo">A</div>
            <h1 class="auth-title">Masuk Sistem</h1>
            <p class="auth-subtitle">Login admin atau petugas loket.</p>
        </div>

        <?php if ($message !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-save">Login</button>
            <a href="http://localhost/antrean_daptar_ulang/" class="btn btn-warning">Kembali</a>
        </form>
    </div>
</body>
</html>