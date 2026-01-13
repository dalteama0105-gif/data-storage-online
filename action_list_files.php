<?php
session_start();
header('Content-Type: application/json');

// 1. 检查登录
if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}

$username = $_SESSION['user'];

// === 关键修改：只扫描用户的专属目录 ===
// 使用 __DIR__ 确保路径准确
$user_dir = __DIR__ . '/uploads/' . $username . '/';
$web_dir_prefix = 'uploads/' . $username . '/'; // 用于生成给浏览器的链接

$file_list = [];

// 2. 检查用户的文件夹是否存在
// (如果用户刚注册还没上传过东西，文件夹是不存在的，这时候直接返回空数组)
if (is_dir($user_dir)) {
    
    $files = scandir($user_dir);
    $files = array_diff($files, array('.', '..'));

    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        $file_list[] = [
            'name' => $file,
            // 路径变成了 uploads/Darren/xxx.txt
            'path' => $web_dir_prefix . $file, 
            'isServerFile' => true,
            'type' => $ext
        ];
    }
}

// 3. 输出 JSON
echo json_encode(array_values($file_list));
?>