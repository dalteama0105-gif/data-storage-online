<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // 1. Determine Path
    // If it's a folder upload, this will look like "MyFolder/image.png"
    // If it's a file upload, it will just be "image.png"
    $relativePath = $_POST['relativePath'] ?? $file['name'];
    
    // 2. Base Directory for User
    $base_dir = 'uploads/' . $username . '/';
    
    // 3. Create Target Directory
    $target_dir = $base_dir;
    
    if (strpos($relativePath, '/') !== false) {
        // Extract the directory part (e.g., "MyFolder/SubFolder")
        $dir_struct = dirname($relativePath);
        $target_dir = $base_dir . $dir_struct . '/';
        
        // Recursively create folder
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
    } else {
        // Ensure base user dir exists
        if (!is_dir($base_dir)) {
            mkdir($base_dir, 0777, true);
        }
    }

    // 4. Security Checks
    if ($file['size'] > 50 * 1024 * 1024) { // 50MB Limit
        echo json_encode(['success' => false, 'message' => 'File too large']);
        exit;
    }
    
    // 5. Move File
    $target_filename = basename($file['name']);
    $target_path = $target_dir . $target_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Write error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file received']);
}
?>