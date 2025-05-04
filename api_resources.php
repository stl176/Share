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

// 获取请求的分类
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

if ($category === 'all') {
    $data_sql = "SELECT data.id, data.title, data.star, data.update_time, data.introduction, data.logo, data.download_url, categories.category_name 
                 FROM data 
                 JOIN categories ON data.category_id = categories.category_id";
} else {
    $data_sql = "SELECT data.id, data.title, data.star, data.update_time, data.introduction, data.logo, data.download_url, categories.category_name 
                 FROM data 
                 JOIN categories ON data.category_id = categories.category_id
                 WHERE categories.category_name = '$category'";
}

$data_result = $conn->query($data_sql);

$resources = [];
if ($data_result->num_rows > 0) {
    while ($row = $data_result->fetch_assoc()) {
        $resources[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'star' => $row['star'],
            'update_time' => $row['update_time'],
            'introduction' => $row['introduction'],
            'logo' => $row['logo'],
            'download_url' => $row['download_url'],
            'category' => $row['category_name']
        ];
    }
}

// 设置响应头为 JSON 格式
header('Content-Type: application/json');
echo json_encode($resources);

$conn->close();
?>