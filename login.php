<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    echo json_encode(['success' => true]); // 修正这里
} else {
    http_response_code(401);
    echo json_encode(['error' => '用户名或密码错误']); // 同样修正此处
}
?>