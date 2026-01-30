<?php
session_start();

$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';

if (!file_exists('data/users.json')) {
    header("Location: login.php?error=System Error: Database not found");
    exit;
}

$users = json_decode(file_get_contents('data/users.json'), true);

foreach ($users as $user) {
    if ($user['username'] === $u) {
        // Verify password
        if (password_verify($p, $user['password_hash'])) {
            $_SESSION['user'] = $u;
            
            // Priority 1: Use the role stored in the JSON file
            // Priority 2: If no role exists but user is 'admin', set as Admin
            // Priority 3: Default to 'User'
            $_SESSION['role'] = $user['role'] ?? (($u === 'admin') ? 'Admin' : 'User');
            
            header("Location: index.php");
            exit();
        }
    }
}

header("Location: login.php?error=Invalid Username or Password");
?>