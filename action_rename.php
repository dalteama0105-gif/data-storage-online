<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['user'];
$oldName = $_POST['oldName'] ?? '';
$newName = $_POST['newName'] ?? '';
$currentPath = $_POST['path'] ?? ''; // Relative path of the folder containing the file

if (empty($oldName) || empty($newName)) {
    echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
    exit;
}

// Security: Prevent directory traversal
if (strpos($newName, '..') !== false || strpos($newName, '/') !== false || strpos($newName, '\\') !== false) {
    echo json_encode(['success' => false, 'message' => 'Invalid filename']);
    exit;
}

$base_dir = __DIR__ . '/uploads/' . $username . '/';
$dir = $base_dir . ($currentPath ? $currentPath . '/' : '');

$oldPath = $dir . $oldName;
$newPath = $dir . $newName;

if (!file_exists($oldPath)) {
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
}

if (file_exists($newPath)) {
    echo json_encode(['success' => false, 'message' => 'Name already exists']);
    exit;
}

if (rename($oldPath, $newPath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Rename failed']);
}
?>