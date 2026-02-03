<?php
session_start();

if (!isset($_SESSION['user'])) {
    die("Unauthorized");
}

$username = $_SESSION['user'];
$files = $_POST['files'] ?? []; // Expecting an array of relative paths

if (empty($files)) {
    die("No files selected.");
}

$base_dir = __DIR__ . '/uploads/' . $username . '/';
$zip = new ZipArchive();
$zip_filename = "bulk_download_" . date('Ymd_His') . ".zip";
$zip_filepath = sys_get_temp_dir() . '/' . $zip_filename;

if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Could not create ZIP file.");
}

foreach ($files as $file) {
    // 1. Security Check (Directory Traversal)
    if (strpos($file, '..') !== false) continue;
    
    $full_path = $base_dir . $file;
    
    if (file_exists($full_path)) {
        if (is_dir($full_path)) {
            // 2. If it's a folder, add recursively
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($full_path),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $name => $f) {
                if (!$f->isDir()) {
                    $filePath = $f->getRealPath();
                    // Calculate relative path inside ZIP
                    $relativePath = substr($filePath, strlen($base_dir));
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } else {
            // 3. If it's a file, add directly
            // $file is likely "subfolder/file.txt" or just "file.txt"
            $zip->addFile($full_path, $file);
        }
    }
}

$zip->close();

// 4. Serve the file
if (file_exists($zip_filepath)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
    header('Content-Length: ' . filesize($zip_filepath));
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    
    ob_clean();
    flush();
    readfile($zip_filepath);
    
    // 5. Cleanup
    unlink($zip_filepath);
    exit;
} else {
    die("Error generating download.");
}
?>