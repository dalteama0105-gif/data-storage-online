<?php
session_start();
header('Content-Type: application/json');

// 1. 检查登录
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['user']; // 获取当前用户名 (例如 "Darren")

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // === 关键修改：设置用户的专属目录 ===
    // 结果变成: uploads/Darren/
    $user_dir = 'uploads/' . $username . '/';

    // 如果这个用户的文件夹不存在，就创建一个
    if (!is_dir($user_dir)) {
        mkdir($user_dir, 0777, true);
    }

    // === 安全检查 (保持不变) ===
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large']);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['txt', 'mp3', 'png', 'jpg', 'jpeg', 'pdf'];
    if (!in_array($ext, $allowed_exts)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        exit;
    }

    // === 移动文件 ===
    // 目标路径变成: uploads/Darren/abc.txt
    // 为了防止同名覆盖，我们在文件名前加个时间戳，或者你可以保留原名
    $target_filename = basename($file['name']); 
    $target_path = $user_dir . $target_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        echo json_encode([
            'success' => true,
            'file' => [
                'name' => $target_filename,
                'path' => $target_path, // JS 会拿到 uploads/Darren/xxx.jpg
                'type' => $file['type'] // 这里的 type 其实不太准，最好用 ext 判断
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Server write error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file received']);
}

// In action_upload.php

$relativePath = $_POST['relativePath'] ?? $_FILES['file']['name'];
// If relativePath contains slashes, it's a folder upload
if (strpos($relativePath, '/') !== false) {
    // Extract directory path (e.g. "MyFolder/Sub")
    $dirStruct = dirname($relativePath); 
    
    // Create that directory on server
    $targetDir = "uploads/" . $dirStruct;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Recursive creation
    }
    // Update upload target
    $targetFile = "uploads/" . $relativePath;
}
?>

