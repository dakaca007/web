<?php
// comment.php - 处理回复
session_start();
require 'classes/Database.php';

if (!isset($_SESSION['user'])) {
    die("未登录。");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thread_id = intval($_POST['thread_id']);
    $content = $_POST['content'];
    $user_id = $_SESSION['user']['id'];

    $db = new Database();
    $conn = $db->connect();

    $db->insert("forum_posts", [
        "thread_id" => $thread_id,
        "user_id" => $user_id,
        "content" => $content
    ]);

    header("Location: thread.php?id=$thread_id");
    exit;
}
?>
