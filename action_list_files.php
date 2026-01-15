<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) { echo json_encode([]); exit; }

$username = $_SESSION['user'];
$user_dir = __DIR__ . '/uploads/' . $username . '/';
$web_dir_prefix = 'uploads/' . $username . '/';

$file_list = [];

if (is_dir($user_dir)) {
    $files = scandir($user_dir);
    $files = array_diff($files, array('.', '..'));

    foreach ($files as $file) {
        $filepath = $user_dir . $file;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        $file_list[] = [
            'name' => $file,
            'path' => $web_dir_prefix . $file,
            'isServerFile' => true,
            'type' => $ext,
            // === NEW: Get File Modification Time ===
            'date' => date("Y-m-d H:i", filemtime($filepath)) 
        ];
    }
}

echo json_encode(array_values($file_list));
?>