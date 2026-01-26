<?php
session_start();
header('Content-Type: application/json');

// Only Admin can access
if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$file = 'data/users.json';
$users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

$action = $_REQUEST['action'] ?? '';

if ($action === 'list') {
    // Return users without passwords
    $output = [];
    foreach ($users as $u) {
        unset($u['password_hash']);
        $output[] = $u;
    }
    echo json_encode($output);
    exit;
}

if ($action === 'add') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    
    // Check duplicates
    foreach ($users as $existing) {
        if ($existing['username'] === $u) {
            echo json_encode(['success' => false, 'message' => 'Username exists']);
            exit;
        }
    }

    $newUser = [
        'username' => $u,
        'password_hash' => password_hash($p, PASSWORD_DEFAULT),
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'department' => $_POST['department'] ?? ''
    ];

    $users[] = $newUser;
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'reset') {
    $target = $_POST['username'] ?? '';
    $defaultPass = 'qwer1234';
    $found = false;

    foreach ($users as &$u) {
        if ($u['username'] === $target) {
            $u['password_hash'] = password_hash($defaultPass, PASSWORD_DEFAULT);
            $found = true;
            break;
        }
    }

    if ($found) {
        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Password reset to qwer1234']);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit;
}
?>