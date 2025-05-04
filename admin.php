<?php
// 检查 cookie 中是否存在验证信息
if (!isset($_COOKIE['authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        $password = $_POST['password'];
        if ($password === '123.abcd') {
            // 设置 cookie，有效期为 1 天
            setcookie('authenticated', 'true', time() + 86400, '/');
            // 重定向到原页面
            header("Location: {$_SERVER['PHP_SELF']}");
            exit;
        } else {
            $error = '密码错误，请重试。';
        }
    }
    // 显示密码输入表单
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>密码验证</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f9fafb;
            }
           .form-container {
                background-color: #ffffff;
                padding: 1.5rem;
                border-radius: 0.5rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            }
           .form-container label {
                display: block;
                color: #6b7280;
                font-size: 0.875rem;
                margin-bottom: 0.25rem;
            }
           .form-container input {
                width: 100%;
                padding: 0.625rem;
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
                margin-bottom: 1rem;
                font-size: 0.875rem;
                transition: border-color 0.3s ease;
            }
           .form-container input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 1px #3b82f6;
            }
           .form-container button {
                background-color: #3b82f6;
                color: #ffffff;
                padding: 0.625rem 1.25rem;
                border: none;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
           .form-container button:hover {
                background-color: #2563eb;
            }
           .error-message {
                color: #ef4444;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <label for="password">请输入密码:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">提交</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}
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

// 获取当前要显示的页面，默认为分类管理页面
$page = isset($_GET['page']) ? $_GET['page'] : 'category';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$resource_id = isset($_GET['resource_id']) ? $_GET['resource_id'] : '';
$current_page = isset($_GET['p']) ? intval($_GET['p']) : 1; // 当前页码，默认为第1页
$per_page = 20; // 每页显示的记录数

// 用于存储提示信息
$message = '';

// 获取会话中的提示消息
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // 显示消息后删除会话中的消息
}

// 处理分类编辑提交
if ($page === 'category' && $action === 'edit' && isset($_POST['update_category'])) {
    $category_name = $_POST['category_name'];
    $sql = "UPDATE categories SET category_name = '$category_name' WHERE category_id = $category_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md" role="alert">分类更新成功</div>';
    } else {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md" role="alert">错误: '. $sql. "<br>". $conn->error. '</div>';
    }
    // 重定向到分类管理页面
    header("Location: {$_SERVER['PHP_SELF']}?page=category");
    exit;
}

// 处理资源编辑提交
if ($page ==='resource' && $action === 'edit' && isset($_POST['update_resource'])) {
    $title = $_POST['title'];
    $star = $_POST['star'];
    $update_time = date('Y-m-d H:i:s'); // 设置更新时间为当前时间
    $introduction = $_POST['introduction'];
    $logo = $_POST['logo'];
    $download_url = $_POST['download_url'];
    $category_id = $_POST['category_id'];

    $sql = "UPDATE data SET title = '$title', star = '$star', update_time = '$update_time', introduction = '$introduction', logo = '$logo', download_url = '$download_url', category_id = $category_id WHERE id = $resource_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md" role="alert">资源更新成功</div>';
    } else {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md" role="alert">错误: '. $sql. "<br>". $conn->error. '</div>';
    }
    // 重定向到资源管理页面
    header("Location: {$_SERVER['PHP_SELF']}?page=resource");
    exit;
}

// 处理添加分类
if ($page === 'category' && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $sql = "INSERT INTO categories (category_name) VALUES ('$category_name')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md" role="alert">分类添加成功</div>';
    } else {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md" role="alert">错误: '. $sql. "<br>". $conn->error. '</div>';
    }
    // 重定向到分类管理页面
    header("Location: {$_SERVER['PHP_SELF']}?page=category");
    exit;
}

// 处理添加资源
if ($page ==='resource' && isset($_POST['add_data'])) {
    $title = $_POST['title'];
    $star = $_POST['star'];
    $update_time = date('Y-m-d H:i:s'); // 设置更新时间为当前时间
    $introduction = $_POST['introduction'];
    $logo = $_POST['logo'];
    $download_url = $_POST['download_url'];
    $category_id = $_POST['category_id'];

    $sql = "INSERT INTO data (title, star, update_time, introduction, logo, download_url, category_id) 
            VALUES ('$title', '$star', '$update_time', '$introduction', '$logo', '$download_url', $category_id)";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md" role="alert">资源添加成功</div>';
    } else {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md" role="alert">错误: '. $sql. "<br>". $conn->error. '</div>';
    }
    // 重定向到资源管理页面
    header("Location: {$_SERVER['PHP_SELF']}?page=resource");
    exit;
}

// 获取分类列表
$category_sql = "SELECT category_id, category_name FROM categories";
$category_result = $conn->query($category_sql);

// 获取资源列表
if ($page ==='resource') {
    // 计算偏移量
    $offset = ($current_page - 1) * $per_page;

    $data_sql = "SELECT data.id, data.title, data.star, data.update_time, data.introduction, data.logo, data.download_url, categories.category_id AS category_id, categories.category_name 
                 FROM data 
                 JOIN categories ON data.category_id = categories.category_id
                 ORDER BY data.update_time DESC
                 LIMIT $offset, $per_page";
    $data_result = $conn->query($data_sql);

    // 获取资源总数
    $total_sql = "SELECT COUNT(*) as total FROM data JOIN categories ON data.category_id = categories.category_id";
    $total_result = $conn->query($total_sql);
    $total_row = $total_result->fetch_assoc();
    $total_records = $total_row['total'];

    // 计算总页数
    $total_pages = ceil($total_records / $per_page);
}
?>
<?php
// ... 前面的数据库连接和逻辑保持不变 ...

// 添加处理更改时间的逻辑
if ($page === 'resource' && $action === 'update_time' && isset($_GET['resource_id'])) {
    $resource_id = $_GET['resource_id'];
    $update_time = date('Y-m-d H:i:s');
    
    $sql = "UPDATE data SET update_time = '$update_time' WHERE id = $resource_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md" role="alert">时间更新成功</div>';
    } else {
        $_SESSION['message'] = '<div id="message" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md" role="alert">错误: '. $sql. "<br>". $conn->error. '</div>';
    }
    header("Location: {$_SERVER['PHP_SELF']}?page=resource");
    exit;
}

// ... 后面的代码保持不变直到HTML部分 ...
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理界面</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        /* 全局样式，整体字体缩小 */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #374151;
            line-height: 1.5;
            font-size: 0.8rem; 
        }

        /* 侧边栏样式，宽度和字体变小 */
       .sidebar {
            background-color: #1f2937;
            width: 180px; 
            min-height: 100vh;
            padding: 1rem; 
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            z-index: 10;
        }

       .sidebar.collapsed {
            width: 60px; 
        }

       .sidebar.collapsed h2,
       .sidebar.collapsed span {
            display: none;
        }

       .sidebar h2 {
            color: #e5e7eb;
            font-size: 1rem; 
            font-weight: 600;
            margin-bottom: 1.5rem; 
            text-align: center;
        }

       .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

       .sidebar ul li {
            margin-bottom: 0.5rem; 
        }

       .sidebar ul li a {
            display: flex;
            align-items: center;
            color: #9ca3af;
            text-decoration: none;
            padding: 0.5rem; 
            border-radius: 0.375rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

       .sidebar ul li a:hover {
            background-color: #374151;
            color: #f9fafb;
        }

       .sidebar ul li a.active {
            background-color: #374151;
            color: #f9fafb;
        }

       .sidebar ul li a i {
            margin-right: 0.5rem; 
            font-size: 1rem; 
        }

        /* 主内容区域样式 */
       .main-content {
            flex: 1;
            padding: 1.5rem; 
        }

        /* 标题样式 */
       .main-content h2 {
            color: #1f2937;
            font-size: 1.5rem; 
            font-weight: 600;
            margin-bottom: 1.2rem; 
        }

        /* 表单样式 */
       .form-container {
            background-color: #ffffff;
            padding: 1.2rem; 
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem; 
        }

       .form-container label {
            display: block;
            color: #6b7280;
            font-size: 0.8rem; 
            margin-bottom: 0.2rem; 
        }

       .form-container input,
       .form-container select {
            width: 100%;
            padding: 0.5rem; 
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-bottom: 0.8rem; 
            font-size: 0.8rem; 
            transition: border-color 0.3s ease;
        }

       .form-container input:focus,
       .form-container select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

       .form-container button {
            background-color: #3b82f6;
            color: #ffffff;
            padding: 0.5rem 1rem; 
            border: none;
            border-radius: 0.375rem;
            font-size: 0.8rem; 
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

       .form-container button:hover {
            background-color: #2563eb;
        }

       .form-container a {
            color: #6b7280;
            text-decoration: none;
            margin-left: 0.8rem; 
            transition: color 0.3s ease;
        }

       .form-container a:hover {
            color: #374151;
        }

        /* 表格样式 */
       .table-container {
            background-color: #ffffff;
            padding: 1.2rem; 
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

       .table-container table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

       .table-container table th,
       .table-container table td {
            padding: 0.5rem; 
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 0.8rem; 
        }

       .table-container table th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
        }

       .table-container table tr:last-child td {
            border-bottom: none;
        }

       .table-container table a {
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.3s ease;
        }

       .table-container table a:hover {
            color: #2563eb;
        }

        /* 调整表格列宽，确保更新时间能显示完全 */
       .table-container th:nth-child(1) { width: 5%; }
       .table-container th:nth-child(2) { width: 12%; }
       .table-container th:nth-child(3) { width: 10%; }
       .table-container th:nth-child(4) { width: 15%; } 
       .table-container th:nth-child(5) { width: 10%; } 
       .table-container th:nth-child(6) { width: 10%; }
       .table-container th:nth-child(7) { width: 15%; }
       .table-container th:nth-child(8) { width: 10%; }
       .table-container th:nth-child(9) { width: 8%; }

        /* 添加文本截断 */
       .table-container td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 允许简介列换行并限制显示字数 */
       .table-container td:nth-child(5) {
            white-space: normal;
            word-break: break-word;
            max-width: 100px; 
        }

        /* 消息提示样式 */
       #message {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            animation: fadeOut 2s ease-in-out forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            80% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                display: none;
            }
        }

        /* 分页样式 */
       .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

       .pagination a {
            color: #3b82f6;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border: 1px solid #d1d5db;
            margin: 0 0.2rem;
            border-radius: 0.375rem;
            transition: background-color 0.3s ease;
        }

       .pagination a:hover {
            background-color: #f3f4f6;
        }

       .pagination a.active {
            background-color: #3b82f6;
            color: #ffffff;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const messageElement = document.getElementById('message');
            if (messageElement) {
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 2000);
            }
        });
    </script>
</head>

<body class="flex h-screen">
    <!-- 侧边栏导航 -->
    <div class="sidebar">
        <h2>后台管理</h2>
        <ul>
            <li><a href="admin.php?page=category" class="flex items-center <?php echo $page === 'category'? 'active' : ''; ?>">
                    <i class="fa-solid fa-folder"></i>
                    <span class="ml-2">分类管理</span>
                </a></li>
            <li><a href="admin.php?page=resource" class="flex items-center <?php echo $page ==='resource'? 'active' : ''; ?>">
                    <i class="fa-solid fa-file"></i>
                    <span class="ml-2">资源管理</span>
                </a></li>
        </ul>
    </div>

    <!-- 主内容区域 -->
    <div class="main-content">
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <?php if ($page === 'category'): ?>
            <?php if ($action === 'edit'): ?>
                <?php
                $edit_sql = "SELECT category_id, category_name FROM categories WHERE category_id = $category_id";
                $edit_result = $conn->query($edit_sql);
                $edit_row = $edit_result->fetch_assoc();
                ?>
                <div class="form-container">
                    <h2>编辑分类</h2>
                    <form action="admin.php?page=category&action=edit&category_id=<?php echo $category_id; ?>" method="post" class="space-y-4">
                        <div>
                            <label for="category_name">分类名:</label>
                            <input type="text" id="category_name" name="category_name" value="<?php echo $edit_row['category_name']; ?>" required>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="submit" name="update_category" value="更新分类" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <a href="admin.php?page=category">取消</a>
                        </div>
                    </form>
                </div>
            <?php elseif ($action === 'add'): ?>
                <div class="form-container">
                    <h2>添加分类</h2>
                    <form action="admin.php?page=category" method="post" class="space-y-4">
                        <div>
                            <label for="category_name">分类名:</label>
                            <input type="text" id="category_name" name="category_name" required>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="submit" name="add_category" value="添加分类" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <a href="admin.php?page=category">取消</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <h2>分类管理</h2>
                    <a href="admin.php?page=category&action=add" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4 inline-block">添加分类</a>
                    <table>
                        <thead>
                            <tr>
                                <th>分类ID</th>
                                <th>分类名</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $category_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['category_id']; ?></td>
                                    <td><?php echo $row['category_name']; ?></td>
                                    <td>
                                        <a href="admin.php?page=category&action=edit&category_id=<?php echo $row['category_id']; ?>" class="text-blue-500 hover:text-blue-600">编辑</a>
                                        <!-- 这里可以添加删除操作 -->
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php elseif ($page ==='resource'): ?>
            <?php if ($action === 'edit'): ?>
                <?php
                $edit_sql = "SELECT data.id, data.title, data.star, data.update_time, data.introduction, data.logo, data.download_url, data.category_id 
                             FROM data 
                             WHERE id = $resource_id";
                $edit_result = $conn->query($edit_sql);
                $edit_row = $edit_result->fetch_assoc();
                ?>
                <div class="form-container">
                    <h2>编辑资源</h2>
                    <form action="admin.php?page=resource&action=edit&resource_id=<?php echo $resource_id; ?>" method="post" class="space-y-4">
                        <div>
                            <label for="title">标题:</label>
                            <input type="text" id="title" name="title" value="<?php echo $edit_row['title']; ?>" required>
                        </div>
                        <div>
                            <label for="star">主演:</label>
                            <input type="text" id="star" name="star" value="<?php echo $edit_row['star']; ?>" required>
                        </div>
                        <div>
                            <label for="introduction">简介:</label>
                            <input type="text" id="introduction" name="introduction" value="<?php echo $edit_row['introduction']; ?>" required>
                        </div>
                        <div>
                            <label for="logo">图标:</label>
                            <input type="text" id="logo" name="logo" value="<?php echo $edit_row['logo']; ?>" required>
                        </div>
                        <div>
                            <label for="download_url">下载链接:</label>
                            <input type="text" id="download_url" name="download_url" value="<?php echo $edit_row['download_url']; ?>" required>
                        </div>
                        <div>
                            <label for="category_id">分类:</label>
                            <select id="category_id" name="category_id" required>
                                <?php 
                                $category_result->data_seek(0);
                                while ($category_row = $category_result->fetch_assoc()): ?>
                                    <option value="<?php echo $category_row['category_id']; ?>" <?php echo ($category_row['category_id'] == $edit_row['category_id']) ? 'selected' : ''; ?>><?php echo $category_row['category_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="submit" name="update_resource" value="更新资源" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <a href="admin.php?page=resource">取消</a>
                        </div>
                    </form>
                </div>
            <?php elseif ($action === 'add'): ?>
                <div class="form-container">
                    <h2>添加资源</h2>
                    <form action="admin.php?page=resource" method="post" class="space-y-4">
                        <div>
                            <label for="title">标题:</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        <div>
                            <label for="star">主演:</label>
                            <input type="text" id="star" name="star" required>
                        </div>
                        <div>
                            <label for="introduction">简介:</label>
                            <input type="text" id="introduction" name="introduction" required>
                        </div>
                        <div>
                            <label for="logo">图标:</label>
                            <input type="text" id="logo" name="logo" required>
                        </div>
                        <div>
                            <label for="download_url">下载链接:</label>
                            <input type="text" id="download_url" name="download_url" required>
                        </div>
                        <div>
                            <label for="category_id">分类:</label>
                            <select id="category_id" name="category_id" required>
                                <?php 
                                $category_result->data_seek(0);
                                while ($category_row = $category_result->fetch_assoc()): ?>
                                    <option value="<?php echo $category_row['category_id']; ?>"><?php echo $category_row['category_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="submit" name="add_data" value="添加资源" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <a href="admin.php?page=resource">取消</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <h2>资源管理</h2>
                    <a href="admin.php?page=resource&action=add" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4 inline-block">添加资源</a>
                    <table>
                        <thead>
                            <tr>
                                <th>资源ID</th>
                                <th>标题</th>
                                <th>主演</th>
                                <th>更新时间</th>
                                <th>简介</th>
                                <th>图标</th>
                                <th>下载链接</th>
                                <th>分类</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $data_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['title']; ?></td>
                                    <td><?php echo $row['star']; ?></td>
                                    <td><?php echo $row['update_time']; ?></td>
                                    <td><?php echo mb_substr($row['introduction'], 0, 10, 'UTF-8'); ?></td> 
                                    <td><img src="<?php echo $row['logo']; ?>" alt="Logo" width="30"></td> 
                                    <td><a href="<?php echo $row['download_url']; ?>" target="_blank">下载</a></td>
                                    <td><?php echo $row['category_name']; ?></td>
                                    <td>
                                        <a href="admin.php?page=resource&action=edit&resource_id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-600">编辑</a>
                                        <!-- 这里可以添加删除操作 -->
                                        <button onclick="updateTime(<?php echo $row['id']; ?>)" class="text-green-500 hover:text-green-600 ml-2">更改时间</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="admin.php?page=resource&p=<?php echo $i; ?>" <?php if ($i == $current_page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
<script>
function updateTime(resourceId) {
    if (confirm('确定要更新时间吗？')) {
        window.location.href = `admin.php?page=resource&action=update_time&resource_id=${resourceId}`;

    }
}
</script>
</html>    