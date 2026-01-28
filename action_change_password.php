<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$current_pass = $_POST['current_password'] ?? '';
$new_pass = $_POST['new_password'] ?? '';
$confirm_pass = $_POST['confirm_password'] ?? '';

// Basic validation
if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
    header("Location: index.php?error=All fields are required");
    exit;
}

if ($new_pass !== $confirm_pass) {
    header("Location: index.php?error=New passwords do not match");
    exit;
}

// Load users
$json_file = 'data/users.json';
if (!file_exists($json_file)) {
    header("Location: index.php?error=Database error");
    exit;
}

$users = json_decode(file_get_contents($json_file), true);
$user_found = false;
$password_changed = false;

// Loop through users to find the current user
foreach ($users as &$u) {
    if ($u['username'] === $user) {
        $user_found = true;
        // Verify old password
        if (password_verify($current_pass, $u['password_hash'])) {
            // Update password
            $u['password_hash'] = password_hash($new_pass, PASSWORD_DEFAULT);
            $password_changed = true;
        } else {
            header("Location: index.php?error=Incorrect current password");
            exit;
        }
        break;
    }
}

if ($user_found && $password_changed) {
    file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));
    header("Location: index.php?msg=Password changed successfully");
} else {
    header("Location: index.php?error=User not found or error occurred");
}
?>