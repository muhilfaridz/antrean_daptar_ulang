<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$users = [
    [
        'nama' => 'Admin',
        'username' => 'admin',
        'password' => 'admin123',
        'role' => 'admin',
        'loket' => null
    ],
    [
        'nama' => 'Petugas 1',
        'username' => 'petugas1',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 1
    ],
    [
        'nama' => 'Petugas 2',
        'username' => 'petugas2',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 2
    ],
    [
        'nama' => 'Petugas 3',
        'username' => 'petugas3',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 3
    ],
    [
        'nama' => 'Petugas 4',
        'username' => 'petugas4',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 4
    ],
    [
        'nama' => 'Petugas 5',
        'username' => 'petugas5',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 5
    ],
    [
        'nama' => 'Petugas 6',
        'username' => 'petugas6',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 6
    ],
    [
        'nama' => 'Petugas 7',
        'username' => 'petugas7',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 7
    ],
    [
        'nama' => 'Petugas 8',
        'username' => 'petugas8',
        'password' => 'petugas123',
        'role' => 'petugas',
        'loket' => 8
    ]
];

try {
    $conn->beginTransaction();

    $check = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
    $insert = $conn->prepare("
        INSERT INTO users (nama, username, password, role, loket)
        VALUES (:nama, :username, :password, :role, :loket)
    ");

    foreach ($users as $user) {
        $check->execute([':username' => $user['username']]);
        $exists = $check->fetch(PDO::FETCH_ASSOC);

        if (!$exists) {
            $insert->execute([
                ':nama' => $user['nama'],
                ':username' => $user['username'],
                ':password' => password_hash($user['password'], PASSWORD_DEFAULT),
                ':role' => $user['role'],
                ':loket' => $user['loket']
            ]);
        }
    }

    $conn->commit();
    echo "Seed user berhasil dijalankan.";
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage();
}