<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = new Database();
    $conn = $db->getConnection();

    $lastId = (int)($_GET['last_id'] ?? 0);

    $stmt = $conn->prepare("
        SELECT id, nomor_antrian, loket_id, status, created_at
        FROM panggilan_antrian
        WHERE id > :last_id
        ORDER BY id ASC
        LIMIT 1
    ");
    $stmt->execute([':last_id' => $lastId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'success' => true,
            'id' => (int)$row['id'],
            'nomor_antrian' => $row['nomor_antrian'],
            'loket_id' => (int)$row['loket_id'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ]);
    } else {
        echo json_encode([
            'success' => false
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}