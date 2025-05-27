<?php
// login.php
session_start();
require 'classes/Database.php';
$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = $db->select("forum_users", ["username = '$username'"]);
    if ($user && password_verify($password, $user[0]['password'])) {
        $_SESSION['user'] = $user[0];
        header("Location: index.php");
    } else {
        echo "用户名或密码错误";
    }
}
?>
<form method="POST" class="container mt-4">
    <h2>登录</h2>
    <input name="username" class="form-control mb-2" placeholder="用户名" required>
    <input name="password" type="password" class="form-control mb-2" placeholder="密码" required>
    <button class="btn btn-primary">登录</button>
</form>
