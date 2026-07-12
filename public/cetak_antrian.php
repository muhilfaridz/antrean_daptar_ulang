<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM antrian WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$antrian = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$antrian) {
    die('Data antrian tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Antrian</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @page {
            size: 60mm auto;
            margin: 0;
        }

        html, body {
            width: 60mm;
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: Arial, sans-serif;
        }

        .print-page {
            width: 60mm;
            padding: 0;
            margin: 0;
        }

        .ticket {
            width: 60mm;
            box-sizing: border-box;
            background: #fff;
            border: 1px dashed #000;
            padding: 6mm 4mm;
            text-align: center;
        }

        .ticket-title {
            font-size: 12pt;
            font-weight: 700;
            margin-bottom: 4mm;
        }

        .ticket-number {
            font-size: 24pt;
            font-weight: 800;
            margin: 4mm 0;
            line-height: 1;
        }

        .ticket-line {
            font-size: 8pt;
            margin: 2mm 0;
            line-height: 1.2;
        }

        .ticket-actions {
            margin-top: 2mm;
            display: flex;
            gap: 2mm;
            justify-content: center;
            flex-wrap: wrap;
        }

        .ticket-actions .btn {
            min-width: 20mm;
        }

        @media print {
            body {
                background: #fff !important;
            }

            .ticket-actions {
                display: none !important;
            }

            .ticket {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-page">
        <div class="ticket">
            <div class="ticket-title">Nomor Antrian</div>
            <div class="ticket-number"><?php echo htmlspecialchars($antrian['nomor_antrian']); ?></div>
            <div class="ticket-line"><?php echo htmlspecialchars($antrian['nama']); ?></div>
            <div class="ticket-line"><?php echo htmlspecialchars($antrian['jalur']); ?></div>
            <div class="ticket-actions">
                <button class="btn btn-save" onclick="window.print()">Cetak</button>
                <a href="ambil_antrian.php" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>