<?php
// register.php
require 'classes/Database.php';
$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $exists = $db->select("forum_users", ["username = '$username' OR email = '$email'"]);
    if ($exists) {
        die("用户名或邮箱已存在");
    }

    $id = $db->insert("forum_users", [
        "username" => $username,
        "email" => $email,
        "password" => $password
    ]);

    if ($id) {
        header("Location: login.php");
    } else {
        echo "注册失败";
    }
}
?>
<form method="POST" class="container mt-4">
    <h2>注册</h2>
    <input name="username" class="form-control mb-2" placeholder="用户名" required>
    <input name="email" type="email" class="form-control mb-2" placeholder="邮箱" required>
    <input name="password" type="password" class="form-control mb-2" placeholder="密码" required>
    <button class="btn btn-primary">注册</button>
</form>
