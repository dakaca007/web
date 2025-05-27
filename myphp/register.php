<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    exit(json_encode(['error' => '用户名和密码不能为空']));
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode(['error' => '用户名已存在']);
}
?>