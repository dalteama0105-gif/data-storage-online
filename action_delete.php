<?php
session_start();
header('Content-Type: application/json');

// 1. Check Login
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Get Parameters
$username = $_SESSION['user'];
$filename = $_POST['filename'] ?? '';

if (empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Filename missing']);
    exit;
}

// 3. Build Path
// basename() is generally good, but for deleting subfolders passed from JS (e.g. "Folder/Subfolder")
// we might need to be careful. However, your JS sends 'relativePath'.
// Ideally, we validate that the path doesn't contain ".."
if (strpos($filename, '..') !== false) {
    echo json_encode(['success' => false, 'message' => 'Invalid path']);
    exit;
}

$base_dir = __DIR__ . '/uploads/' . $username . '/';
$target_path = $base_dir . $filename;

// 4. Recursive Delete Function
function deleteFolder($dir) {
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            deleteFolder($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

// 5. Execute Delete
if (file_exists($target_path)) {
    $result = false;
    
    if (is_dir($target_path)) {
        $result = deleteFolder($target_path);
    } else {
        $result = unlink($target_path);
    }

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed (permission or not empty?)']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File not found']);
}
?>