<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user'])) {
    die("Unauthorized");
}

$username = $_SESSION['user'];
$folder_name = $_GET['folder'] ?? '';

// 2. Prevent hacking (Directory Traversal)
if (strpos($folder_name, '..') !== false || strpos($folder_name, '/') === 0) {
    die("Invalid path");
}

$base_dir = __DIR__ . '/uploads/' . $username . '/';
$folder_path = $base_dir . $folder_name;

if (empty($folder_name) || !is_dir($folder_path)) {
    die("Folder not found.");
}

// 3. Initialize ZipArchive
$zip = new ZipArchive();
$zip_filename = "download_" . time() . ".zip";
$zip_filepath = sys_get_temp_dir() . '/' . $zip_filename;

if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Could not create ZIP file.");
}

// 4. Recursive Folder Scan
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($folder_path),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    // Skip directories (they will be added when files are added)
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        
        // Calculate the path inside the zip
        // If downloading "MyFolder", file "MyFolder/A.txt" should be stored as "MyFolder/A.txt"
        // We get the path relative to the User's Root Uploads folder
        $relativePath = substr($filePath, strlen($base_dir));
        
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

// 5. Trigger Download
if (file_exists($zip_filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($folder_name) . '.zip"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zip_filepath));
    
    // Clear buffer to avoid corrupt zip
    ob_clean();
    flush();
    
    readfile($zip_filepath);
    
    // 6. Delete temp file after download
    unlink($zip_filepath);
    exit;
} else {
    die("Error creating zip file.");
}
?>