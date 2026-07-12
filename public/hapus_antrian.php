<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $delete = $conn->prepare("DELETE FROM antrian WHERE id = :id");
    $delete->execute([':id' => $id]);
}

header("Location: ambil_antrian.php");
exit;