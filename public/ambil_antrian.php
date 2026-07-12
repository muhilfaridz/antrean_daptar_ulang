<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $jalur = trim($_POST['jalur'] ?? '');

    if ($nama === '' || $jalur === '') {
        $message = 'Nama dan jalur wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT nomor_antrian FROM antrian ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        $nextNumber = 1;
        if ($last) {
            $num = (int)preg_replace('/\D/', '', $last['nomor_antrian']);
            $nextNumber = $num + 1;
        }

        $nomor = str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);

        $insert = $conn->prepare("
            INSERT INTO antrian (nomor_antrian, nama, jalur, status_antrian)
            VALUES (:nomor, :nama, :jalur, 'waiting')
        ");
        $insert->execute([
            ':nomor' => $nomor,
            ':nama' => $nama,
            ':jalur' => $jalur
        ]);

        header("Location: ambil_antrian.php?success=1");
        exit;
    }
}

$success = isset($_GET['success']) ? 'Nomor antrian berhasil dibuat.' : '';

$stmt = $conn->query("SELECT * FROM antrian ORDER BY id DESC");
$antrianList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrian</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="public-page">
    <div class="container">
        <div class="card">
            <h1>Ambil Antrian</h1>

            <?php if ($message !== ''): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="message info"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-grid">
                    <div class="full">
                        <label>Nama</label>
                        <input type="text" name="nama" required>
                    </div>

                    <div class="full">
                        <label>Jalur</label>
                        <select name="jalur" required>
                            <option value="">Pilih Jalur</option>
                            <option value="Domisili">Domisili</option>
                            <option value="KETM">KETM</option>
                            <option value="CIBI">CIBI</option>
                        </select>
                    </div>

                    <div class="full form-actions">
                        <button type="submit" class="btn btn-save">Simpan Antrian</button>
                        <a href="http://localhost/antrean_daptar_ulang/" class="btn btn-warning">Kembali</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Daftar Antrian</h2>

            <?php if (!empty($antrianList)): ?>
                <div class="table-wrap">
                    <table class="queue-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor</th>
                                <th>Nama</th>
                                <th>Jalur</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($antrianList as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nomor_antrian']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jalur']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status_antrian']); ?></td>
                                    <td>
                                        <div class="action-group">
                                            <a class="btn btn-warning" href="edit_antrian.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                                            <a class="btn btn-danger" href="hapus_antrian.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Hapus antrian ini?')">Hapus</a>
                                            <a class="btn btn-save" href="cetak_antrian.php?id=<?php echo (int)$row['id']; ?>" target="_blank">Cetak</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="muted">Belum ada data antrian.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>