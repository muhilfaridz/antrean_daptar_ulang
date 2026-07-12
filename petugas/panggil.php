<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$loket = (int)($_SESSION['loket'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['panggil_berikutnya'])) {
            $conn->beginTransaction();

            $stmt = $conn->prepare("
                SELECT id, nomor_antrian, nama, jalur
                FROM antrian
                WHERE status_antrian = 'waiting'
                ORDER BY id ASC
                LIMIT 1
                FOR UPDATE
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $update = $conn->prepare("
                    UPDATE antrian
                    SET status_antrian = 'called', current_loket = :loket
                    WHERE id = :id
                ");
                $update->execute([
                    ':loket' => $loket,
                    ':id' => $row['id']
                ]);

                $log = $conn->prepare("
                    INSERT INTO panggilan_antrian (antrian_id, nomor_antrian, loket_id, status)
                    VALUES (:antrian_id, :nomor, :loket, 'called')
                ");
                $log->execute([
                    ':antrian_id' => $row['id'],
                    ':nomor' => $row['nomor_antrian'],
                    ':loket' => $loket
                ]);

                $conn->commit();
                header("Location: panggil.php?success=1");
                exit;
            } else {
                $conn->rollBack();
                $message = 'Tidak ada antrian waiting.';
            }
        }

        if (isset($_POST['panggil_ulang'])) {
            $stmt = $conn->prepare("
                SELECT id, nomor_antrian
                FROM antrian
                WHERE current_loket = :loket AND status_antrian = 'called'
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([':loket' => $loket]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $log = $conn->prepare("
                    INSERT INTO panggilan_antrian (antrian_id, nomor_antrian, loket_id, status)
                    VALUES (:antrian_id, :nomor, :loket, 'repeat')
                ");
                $log->execute([
                    ':antrian_id' => $row['id'],
                    ':nomor' => $row['nomor_antrian'],
                    ':loket' => $loket
                ]);

                $message = 'Panggil ulang berhasil.';
            } else {
                $message = 'Tidak ada antrian yang sedang dipanggil.';
            }
        }

        if (isset($_POST['selesai']) && isset($_POST['antrian_id'])) {
            $update = $conn->prepare("
                UPDATE antrian
                SET status_antrian = 'done'
                WHERE id = :id
            ");
            $update->execute([':id' => (int)$_POST['antrian_id']]);
            header("Location: panggil.php");
            exit;
        }
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $message = 'Error: ' . $e->getMessage();
    }
}

$stmtCalled = $conn->prepare("
    SELECT *
    FROM antrian
    WHERE current_loket = :loket AND status_antrian = 'called'
    ORDER BY id DESC
");
$stmtCalled->execute([':loket' => $loket]);
$calledList = $stmtCalled->fetchAll(PDO::FETCH_ASSOC);

$stmtWaiting = $conn->prepare("
    SELECT *
    FROM antrian
    WHERE status_antrian = 'waiting'
    ORDER BY id ASC
");
$stmtWaiting->execute();
$waitingList = $stmtWaiting->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panggil Antrian</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
    <div class="topbar">
        <div>
            <h1>Panggil Antrian - Loket <?php echo $loket; ?></h1>
            <div class="user-info">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></div>
        </div>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <?php if ($message !== ''): ?>
            <div class="message info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="message info">Antrian berhasil dipanggil.</div>
        <?php endif; ?>

        <div class="card">
            <form method="post" style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" name="panggil_berikutnya" class="btn btn-save">Panggil Berikutnya</button>
                <button type="submit" name="panggil_ulang" class="btn btn-warning">Panggil Ulang</button>
            </form>
        </div>

        <div class="grid stats">
            <div class="card">
                <div class="stat-title">Antrian Waiting</div>
                <div class="stat-value"><?php echo count($waitingList); ?></div>
            </div>
            <div class="card">
                <div class="stat-title">Dipanggil Loket Ini</div>
                <div class="stat-value"><?php echo count($calledList); ?></div>
            </div>
        </div>

        <div class="card">
            <h2>Antrian Sedang Dipanggil</h2>
            <?php if (!empty($calledList)): ?>
                <?php foreach ($calledList as $row): ?>
                    <div class="queue-item">
                        <div>
                            <strong><?php echo htmlspecialchars($row['nomor_antrian']); ?></strong>
                            <div class="muted"><?php echo htmlspecialchars($row['nama']); ?> - <?php echo htmlspecialchars($row['jalur']); ?></div>
                        </div>
                        <form method="post">
                            <input type="hidden" name="antrian_id" value="<?php echo (int)$row['id']; ?>">
                            <button type="submit" name="selesai" class="btn btn-save">Selesai</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="muted">Belum ada antrian yang dipanggil.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Daftar Waiting</h2>
            <?php if (!empty($waitingList)): ?>
                <table class="queue-list">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor</th>
                            <th>Nama</th>
                            <th>Jalur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($waitingList as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nomor_antrian']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['jalur']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted">Tidak ada antrian waiting.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>