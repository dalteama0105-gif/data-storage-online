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

// 3. POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Permission Check
    if (!in_array($userRole, $allowed_roles)) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $currentData = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    
    // Check Action
    $action = $_POST['action'] ?? 'add'; // Default to add for compatibility

    // --- DELETE ACTION ---
    if ($action === 'delete') {
        $idToDelete = $_POST['id'] ?? '';
        $newData = [];
        $found = false;

        foreach ($currentData as $iso) {
            if ($iso['id'] === $idToDelete) {
                $found = true;
                continue; // Skip adding this to the new array
            }
            $newData[] = $iso;
        }

        if ($found) {
            file_put_contents($file, json_encode($newData, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ISO not found']);
        }
        exit;
    }

    // --- ADD ACTION ---
    // Get params
    $isoNumber = $_POST['iso_number'] ?? '';
    $isoName = $_POST['iso_name'] ?? '';

    // Automatic Logo Assignment Logic
    $autoImageName = 'iso_' . $isoNumber . '.png';
    $targetPath = 'uploads/iso_icons/' . $autoImageName;
    
    // Ensure folder exists
    if (!is_dir('uploads/iso_icons')) {
        mkdir('uploads/iso_icons', 0777, true);
    }

    $webPath = $targetPath;

    $newIso = [
        'id' => uniqid(),
        'name' => $isoName,
        'number' => $isoNumber,
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