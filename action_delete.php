<?php
session_start();
header('Content-Type: application/json');

// 1. 检查登录
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. 获取参数
$username = $_SESSION['user'];
$filename = $_POST['filename'] ?? '';

if (empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Filename missing']);
    exit;
}

// 3. 构建路径 (确保只删当前用户文件夹里的东西)
// basename() 非常重要！它可以防止黑客利用 "../" 去删别人的文件
$safe_filename = basename($filename);
$file_path = __DIR__ . '/uploads/' . $username . '/' . $safe_filename;

// 4. 执行删除
if (file_exists($file_path)) {
    if (unlink($file_path)) { // unlink 就是 PHP 的删除命令
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed (permission error?)']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File not found']);
}
?>