<?php
session_start();
header('Content-Type: application/json');

// Allow specific roles to manage ISOs
$allowed_roles = ['Admin', 'Developer', 'ISO User'];
$userRole = $_SESSION['role'] ?? 'User';

// 1. Ensure Data Directory Exists
if (!file_exists('data')) { mkdir('data', 0777, true); }
$file = 'data/iso_data.json';

// 2. GET Request: List ISOs
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode([]);
    }
    exit;
}

// 3. POST Request: Add New ISO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Permission Check
    if (!in_array($userRole, $allowed_roles)) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $currentData = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    
    // Handle Image Upload
    $webPath = 'logo.png'; // Default fallback
    if (isset($_FILES['iso_image']) && $_FILES['iso_image']['error'] === 0) {
        $uploadDir = 'uploads/iso_icons/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $filename = time() . '_' . basename($_FILES['iso_image']['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['iso_image']['tmp_name'], $targetPath)) {
            $webPath = $targetPath;
        }
    }

    $newIso = [
        'id' => uniqid(),
        'name' => $_POST['iso_name'] ?? 'New ISO',
        'number' => $_POST['iso_number'] ?? '',
        'image' => $webPath,
        'date' => date('Y-m-d')
    ];

    $currentData[] = $newIso;
    
    if (file_put_contents($file, json_encode($currentData, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Save failed']);
    }
    exit;
}
?>