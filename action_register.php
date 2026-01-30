<?php
session_start();

// 1. Get user input
$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';

// Simple input check
if (empty($u) || empty($p)) {
    header("Location: register.php?error=Please fill in all fields");
    exit;
}

// 2. Read existing users.json
$json_file = 'data/users.json';

if (!file_exists($json_file)) {
    $users = [];
} else {
    $users = json_decode(file_get_contents($json_file), true);
}

// 3. Check if username already exists
foreach ($users as $user) {
    if ($user['username'] === $u) {
        header("Location: register.php?error=Username already exists!");
        exit;
    }
}

// 4. Create new user with "Developer" role
$new_user = [
    "username" => $u,
    "password_hash" => password_hash($p, PASSWORD_DEFAULT),
    "name" => $u,
    "email" => "",
    "phone" => "",
    "department" => "",
    "role" => "Developer" // Explicitly assign the Developer role
];

// 5. Add to array and save back to JSON
$users[] = $new_user;

if (file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT))) {
    header("Location: login.php?error=Registration Successful! Please Login.");
} else {
    header("Location: register.php?error=System Error: Could not save data");
}
?>