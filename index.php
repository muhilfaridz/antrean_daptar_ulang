<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/admin.php");
        exit;
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: petugas/panggil.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrean Daptar Ulang</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-page">
    <div class="container">
        <div class="hero">
            <div class="hero-badge">Sistem Antrean Online</div>
            <h1 class="hero-title">Antrean Daptar Ulang</h1>
            <p class="hero-text">
                Sistem antrean sederhana untuk ambil nomor, memanggil antrean per loket,
                dan membacakan panggilan terbaru secara otomatis dengan text-to-speech.
            </p>
            <div class="hero-actions">
                <a href="public/ambil_antrian.php" class="btn btn-save">Ambil Antrian</a>
                <a href="auth/login.php" class="btn btn-warning">Login Petugas / Admin</a>
            </div>
        </div>

        <div class="feature-grid">
            <div class="card feature-card">
                <div class="feature-icon">1</div>
                <h2>Ambil Nomor</h2>
                <p class="muted">Pengunjung dapat mengambil nomor antrean dengan cepat.</p>
            </div>

            <div class="card feature-card">
                <div class="feature-icon">2</div>
                <h2>Kelola Loket</h2>
                <p class="muted">Petugas memanggil antrean sesuai loket masing-masing.</p>
            </div>

            <div class="card feature-card">
                <div class="feature-icon">3</div>
                <h2>Suara Otomatis</h2>
                <p class="muted">Admin mendengarkan panggilan terbaru dengan text-to-speech.</p>
            </div>
        </div>
    </div>
</body>
</html>