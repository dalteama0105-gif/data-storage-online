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
        if (password_verify($p, $user['password_hash'])) {
            $_SESSION['user'] = $u;
            // === NEW: Simple Role Logic ===
            // If username is 'admin', they are the Boss. Everyone else is an Employee.
            $_SESSION['role'] = ($u === 'admin') ? 'Admin' : 'User';
            
            header("Location: index.php");
            exit();
        }
    }
}

header("Location: login.php?error=Invalid Username or Password");
?>