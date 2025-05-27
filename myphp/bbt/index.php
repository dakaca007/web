<?php
// index.php - 首页显示帖子列表
require 'includes/header.php';
require 'classes/Database.php';

$db = new Database();
$conn = $db->connect();
$threads = $db->select("forum_threads");
?>
<div class="container mt-4">
    <h2>最新帖子</h2>
    <?php foreach ($threads as $thread): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($thread['title']) ?></h5>
                <p class="card-text"><?= substr(htmlspecialchars($thread['content']), 0, 100) ?>...</p>
                <a href="thread.php?id=<?= $thread['id'] ?>" class="btn btn-sm btn-primary">查看详情</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>
