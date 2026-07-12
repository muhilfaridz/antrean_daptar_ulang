<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$export = $_GET['export'] ?? '';

$where = [];
$params = [];

if ($from !== '') {
    $where[] = "DATE(created_at) >= :from_date";
    $params[':from_date'] = $from;
}

if ($to !== '') {
    $where[] = "DATE(created_at) <= :to_date";
    $params[':to_date'] = $to;
}

$sql = "SELECT id, nomor_antrian, nama, jalur, status_antrian, current_loket, created_at
        FROM antrian";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($export === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=laporan_antrian_" . date('Ymd_His') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>No</th>";
    echo "<th>Nomor</th>";
    echo "<th>Nama</th>";
    echo "<th>Jalur</th>";
    echo "<th>Status</th>";
    echo "<th>Loket</th>";
    echo "<th>Tanggal</th>";
    echo "</tr>";

    $no = 1;
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($row['nomor_antrian']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['jalur']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status_antrian']) . "</td>";
        echo "<td>" . htmlspecialchars((string)$row['current_loket']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
    exit;
}

$total = count($rows);
$waiting = 0;
$called = 0;
$done = 0;

foreach ($rows as $row) {
    if ($row['status_antrian'] === 'waiting') $waiting++;
    if ($row['status_antrian'] === 'called') $called++;
    if ($row['status_antrian'] === 'done') $done++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Antrian</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .admin-nav,
            .report-filter,
            .report-actions {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: 0;
            }

            body {
                background: #fff !important;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <div class="admin-nav">
        <div class="admin-nav-brand">Antrean Daptar Ulang</div>
        <div class="admin-nav-links">
            <a href="admin.php">Dashboard</a>
            <a href="laporan.php" class="active">Laporan</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="hero">
            <div class="hero-badge">Laporan</div>
            <h1 class="hero-title">Laporan Antrian</h1>
            <p class="hero-text">
                Filter data berdasarkan tanggal, lalu cetak atau ekspor ke Excel kapan pun diperlukan.
            </p>
        </div>

        <div class="card report-filter">
            <h2>Filter Tanggal</h2>
            <form method="get" class="form-grid">
                <div>
                    <label>Dari Tanggal</label>
                    <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>">
                </div>
                <div>
                    <label>Sampai Tanggal</label>
                    <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>">
                </div>
                <div class="full form-actions">
                    <button type="submit" class="btn btn-save">Tampilkan</button>
                    <a href="laporan.php" class="btn btn-warning">Reset</a>
                    <button type="button" class="btn btn-danger" onclick="window.print()">Print</button>
                    <a href="?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&export=excel" class="btn btn-save">Export Excel</a>
                </div>
            </form>
        </div>

        <div class="panel-grid">
            <div class="card panel-stat">
                <div class="stat-title">Total Data</div>
                <div class="stat-value"><?php echo $total; ?></div>
            </div>
            <div class="card panel-stat">
                <div class="stat-title">Waiting</div>
                <div class="stat-value"><?php echo $waiting; ?></div>
            </div>
            <div class="card panel-stat">
                <div class="stat-title">Called</div>
                <div class="stat-value"><?php echo $called; ?></div>
            </div>
            <div class="card panel-stat">
                <div class="stat-title">Done</div>
                <div class="stat-value"><?php echo $done; ?></div>
            </div>
        </div>

        <div class="card">
            <h2>Daftar Laporan</h2>

            <?php if (!empty($rows)): ?>
                <div class="table-wrap">
                    <table class="queue-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor</th>
                                <th>Nama</th>
                                <th>Jalur</th>
                                <th>Status</th>
                                <th>Loket</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nomor_antrian']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jalur']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status_antrian']); ?></td>
                                    <td><?php echo htmlspecialchars((string)$row['current_loket']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="muted">Tidak ada data laporan pada rentang tanggal tersebut.</p>
            <?php endif; ?>
        </div>

        <div class="report-actions">
            <a href="admin.php" class="btn btn-warning">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>