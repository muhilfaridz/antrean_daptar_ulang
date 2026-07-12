<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$id = (int)($_GET['id'] ?? 0);
$message = '';

$stmt = $conn->prepare("SELECT * FROM antrian WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$antrian = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$antrian) {
    die('Data antrian tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $jalur = trim($_POST['jalur'] ?? '');
    $status = trim($_POST['status_antrian'] ?? '');

    if ($nama === '' || $jalur === '' || $status === '') {
        $message = 'Semua field wajib diisi.';
    } else {
        $update = $conn->prepare("
            UPDATE antrian
            SET nama = :nama, jalur = :jalur, status_antrian = :status
            WHERE id = :id
        ");
        $update->execute([
            ':nama' => $nama,
            ':jalur' => $jalur,
            ':status' => $status,
            ':id' => $id
        ]);

        header("Location: ambil_antrian.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Antrian</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="public-page">
    <div class="container">
        <div class="card form-card">
            <h1>Edit Antrian</h1>
            <p class="muted">Perbarui data antrean yang dipilih.</p>

            <?php if ($message !== ''): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="ticket-preview">
                <div class="preview-label">Nomor Antrian</div>
                <div class="preview-number"><?php echo htmlspecialchars($antrian['nomor_antrian']); ?></div>
            </div>

            <form method="post">
                <div class="form-grid">
                    <div class="full">
                        <label>Nama</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($antrian['nama']); ?>" required>
                    </div>

                    <div class="full">
                        <label>Jalur</label>
                            <select name="jalur" required>
                            <option value="Domisili" <?php echo $antrian['jalur'] === 'Domisili' ? 'selected' : ''; ?>>Domisili</option>
                            <option value="KETM" <?php echo $antrian['jalur'] === 'KETM' ? 'selected' : ''; ?>>KETM</option>
                            <option value="CIBI" <?php echo $antrian['jalur'] === 'CIBI' ? 'selected' : ''; ?>>CIBI</option>
                            </select>
                    </div>

                    <div class="full">
                        <label>Status</label>
                        <select name="status_antrian" required>
                            <option value="waiting" <?php echo $antrian['status_antrian'] === 'waiting' ? 'selected' : ''; ?>>waiting</option>
                            <option value="called" <?php echo $antrian['status_antrian'] === 'called' ? 'selected' : ''; ?>>called</option>
                            <option value="done" <?php echo $antrian['status_antrian'] === 'done' ? 'selected' : ''; ?>>done</option>
                        </select>
                    </div>

                    <div class="full form-actions">
                        <button type="submit" class="btn btn-save">Simpan Perubahan</button>
                        <a href="ambil_antrian.php" class="btn btn-warning">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>