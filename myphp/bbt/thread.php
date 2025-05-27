<?php
// thread.php - 帖子详情页
require 'includes/header.php';
require 'classes/Database.php';

if (!isset($_GET['id'])) {
    die("未指定帖子 ID。");
}

$db = new Database();
$conn = $db->connect();
$thread_id = intval($_GET['id']);

$thread = $db->select("forum_threads", ["id = $thread_id"]);
$posts = $db->select("forum_posts", ["thread_id = $thread_id"]);
?>
<div class="container mt-4">
    <?php if ($thread): ?>
        <h2><?= htmlspecialchars($thread[0]['title']) ?></h2>
        <p><?= nl2br(htmlspecialchars($thread[0]['content'])) ?></p>
        <hr>
        <h4>回复</h4>
        <?php foreach ($posts as $post): ?>
            <div class="border rounded p-2 mb-2">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
                <small class="text-muted float-end">发表于 <?= $post['created_at'] ?></small>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_SESSION['user'])): ?>
            <form method="POST" action="comment.php">
                <input type="hidden" name="thread_id" value="<?= $thread_id ?>">
                <textarea name="content" class="form-control mb-2" placeholder="写下你的回复..." required></textarea>
                <button class="btn btn-primary">回复</button>
            </form>
        <?php else: ?>
            <p>请 <a href="login.php">登录</a> 后回复。</p>
        <?php endif; ?>
    <?php else: ?>
        <p>帖子不存在。</p>
    <?php endif; ?>
</div>
<?php require 'includes/footer.php'; ?>