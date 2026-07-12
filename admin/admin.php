<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Suara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
    <div class="admin-nav">
        <div class="admin-nav-brand">Antrean Daptar Ulang</div>
        <div class="admin-nav-links">
            <a href="laporan.php">Laporan</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="hero">
            <div class="hero-badge">Admin Panel</div>
            <h1 class="hero-title">Panel Admin Suara</h1>
            <p class="hero-text">
                Monitoring panggilan antrean terbaru dengan text-to-speech.
            </p>
            <div class="hero-actions">
                <a href="laporan.php" class="btn btn-warning">Buka Laporan</a>
            </div>
        </div>

        <div class="panel-grid">
            <div class="card panel-stat">
                <div class="stat-title">Status</div>
                <div class="stat-value">Aktif</div>
                <div class="panel-note">Mendengarkan panggilan baru.</div>
            </div>

            <div class="card panel-stat">
                <div class="stat-title">Format Suara</div>
                <div class="stat-value">ID</div>
                <div class="panel-note">Antrian 001 silakan ke loket 1.</div>
            </div>

            <div class="card panel-stat">
                <div class="stat-title">Mode</div>
                <div class="stat-value">Realtime</div>
                <div class="panel-note">Polling setiap beberapa detik.</div>
            </div>
        </div>
    </div>

    <script>
    let lastId = 0;
    let busy = false;

    function speakText(text) {
        if (!('speechSynthesis' in window)) return;
        const synth = window.speechSynthesis;
        synth.cancel();
        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = 'id-ID';
        utter.rate = 1;
        utter.pitch = 1;
        utter.volume = 1;
        synth.speak(utter);
    }

    function formatQueueText(data) {
        const nomor = String(data.nomor_antrian).padStart(3, '0');
        return `Antrian ${nomor} silakan ke loket ${data.loket_id}`;
    }

    async function poll() {
        if (busy) return;
        try {
            const res = await fetch('latest_call.php?last_id=' + lastId);
            const data = await res.json();
            if (data && data.id) {
                lastId = data.id;
                busy = true;
                speakText(formatQueueText(data));
                setTimeout(() => busy = false, 4000);
            }
        } catch (e) {
            busy = false;
        }
    }

    setInterval(poll, 2000);
    </script>
</body>
</html>