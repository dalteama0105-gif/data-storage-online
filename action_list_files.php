<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) { echo json_encode([]); exit; }

$username = $_SESSION['user'];
// Define the base directory for this user
$base_dir = __DIR__ . '/uploads/' . $username . '/';

// 1. Get the requested directory from JavaScript (default to root)
$req_dir = isset($_GET['dir']) ? $_GET['dir'] : '';

// 2. Security: Remove ".." to prevent hacking (Directory Traversal)
$req_dir = str_replace('..', '', $req_dir);

// 3. Determine the actual path to scan
$current_scan_dir = $base_dir . $req_dir;

// Safety Check: If the directory doesn't exist, go back to root
if (!is_dir($current_scan_dir)) {
    $current_scan_dir = $base_dir;
    $req_dir = '';
}

$file_list = [];

if (is_dir($current_scan_dir)) {
    $files = scandir($current_scan_dir);
    // Remove system dots '.' and '..'
    $files = array_diff($files, array('.', '..'));

    foreach ($files as $file) {
        $filepath = $current_scan_dir . '/' . $file;
        
        // 4. Check if this item is a Folder or a File
        $is_dir = is_dir($filepath);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Calculate the web-accessible path
        // If we are in a subfolder, append it to the path
        $relativePath = ($req_dir ? $req_dir . '/' : '') . $file;
        $web_path = 'uploads/' . $username . '/' . $relativePath;

        $file_list[] = [
            'name' => $file,
            'path' => $web_path,
            'relativePath' => $relativePath, // Important for deleting files inside folders
            'type' => $is_dir ? 'folder' : $ext, // Tell JS this is a folder
            'date' => date("Y-m-d H:i", filemtime($filepath)) 
        ];
    }
}

// Return JSON (array_values ensures it's a clean list, not an object with missing keys)
echo json_encode(array_values($file_list));
?>