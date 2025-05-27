<?php
// 实例化 Database 类
$db = new Database();
$conn = $db->connect();

// 创建所需的表

$tables = [
    "CREATE TABLE IF NOT EXISTS forum_users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL UNIQUE,
        email VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS forum_categories (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS forum_threads (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) UNSIGNED,
        category_id INT(6) UNSIGNED,
        title VARCHAR(150) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES forum_users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE SET NULL
    )",

    "CREATE TABLE IF NOT EXISTS forum_posts (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        thread_id INT(6) UNSIGNED,
        user_id INT(6) UNSIGNED,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (thread_id) REFERENCES forum_threads(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES forum_users(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS forum_comments (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id INT(6) UNSIGNED,
        user_id INT(6) UNSIGNED,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES forum_users(id) ON DELETE CASCADE
    )",
    "
ALTER TABLE forum_users 
ADD COLUMN role ENUM('user', 'moderator', 'admin') DEFAULT 'user',
ADD COLUMN avatar VARCHAR(255) DEFAULT 'default_avatar.png';

    ",
    "
ALTER TABLE forum_threads ADD FULLTEXT(title, content);

",
"
CREATE TABLE forum_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action VARCHAR(50),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);",
"
ALTER TABLE forum_threads ADD INDEX idx_category (category_id);
ALTER TABLE forum_posts ADD INDEX idx_thread (thread_id);

"
];

foreach ($tables as $sql) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        echo "表创建成功！<br>";
    } catch (PDOException $e) {
        die("创建表错误: " . $e->getMessage());
    }
}
?>