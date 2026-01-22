<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['user'];
$folderName = $_POST['folder_name'] ?? '';
$currentPath = $_POST['current_path'] ?? '';

if (empty($folderName)) {
    echo json_encode(['success' => false, 'message' => 'Folder name is required']);
    exit;
}

// Security: Remove ".."
if (strpos($folderName, '..') !== false || strpos($currentPath, '..') !== false) {
    echo json_encode(['success' => false, 'message' => 'Invalid path']);
    exit;
}

// Build Path
$base_dir = __DIR__ . '/uploads/' . $username . '/';
$target_dir = $base_dir . ($currentPath ? $currentPath . '/' : '') . $folderName;

if (file_exists($target_dir)) {
    echo json_encode(['success' => false, 'message' => 'Folder already exists']);
    exit;
}

if (mkdir($target_dir, 0777, true)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create folder']);
}
?>