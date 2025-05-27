<?php
// post_thread.php - 发布新帖
require 'includes/header.php';
require 'classes/Database.php';

if (!isset($_SESSION['user'])) {
    die("请先登录。<a href='login.php'>点此登录</a>");
}

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user']['id'];
    $category_id = 1; // 默认分类，可扩展

    $id = $db->insert("forum_threads", [
        "title" => $title,
        "content" => $content,
        "user_id" => $user_id,
        "category_id" => $category_id
    ]);

    if ($id) {
        header("Location: thread.php?id=$id");
    } else {
        echo "发帖失败。";
    }
}
?>
<div class="container mt-4">
    <h2>发表新帖</h2>
    <form method="POST">
        <input name="title" class="form-control mb-2" placeholder="标题" required>
        <textarea name="content" class="form-control mb-2" placeholder="内容" rows="5" required></textarea>
        <button class="btn btn-success">发布</button>
    </form>
</div>
<?php require 'includes/footer.php'; ?>