<?php
// 数据库连接配置
$servername = "localhost";
$username = "share";
$password = "123.abcd";
$dbname = "share";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: ". $conn->connect_error);
}

// 获取分类列表
$category_sql = "SELECT category_name FROM categories";
$category_result = $conn->query($category_sql);

$categories = [];
if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category_name'];
    }
}

// 设置响应头为 JSON 格式
header('Content-Type: application/json');
echo json_encode($categories);

$conn->close();
?>