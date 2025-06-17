<?php
$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'myapp';
$username = getenv('DB_USER') ?: 'myuser';
$password = getenv('DB_PASS') ?: 'mypassword';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to MySQL database!";
    
    // 创建测试表
    $sql = "CREATE TABLE IF NOT EXISTS test_table (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        email VARCHAR(50),
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    // 插入测试数据
    $stmt = $conn->prepare("INSERT INTO test_table (name, email) VALUES (:name, :email)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    
    $name = "John Doe";
    $email = "john@example.com";
    $stmt->execute();
    
    echo "<br>Test data inserted successfully!";
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
