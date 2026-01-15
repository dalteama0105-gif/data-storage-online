<?php
session_start();

// 1. 获取用户输入
$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';

// 简单的输入检查
if (empty($u) || empty($p)) {
    header("Location: register.php?error=Please fill in all fields");
    exit;
}

// 2. 读取现有的 users.json
$json_file = 'data/users.json';

// 如果文件不存在，创建一个空数组
if (!file_exists($json_file)) {
    $users = [];
} else {
    $users = json_decode(file_get_contents($json_file), true);
}

// 3. 检查用户名是否重复 (User Already Exists?)
foreach ($users as $user) {
    if ($user['username'] === $u) {
        header("Location: register.php?error=Username already exists!");
        exit;
    }
}

// 4. 创建新用户 (加密密码！)
// 注意：一定要用 password_hash，绝对不要直接存明文密码
$new_user = [
    "username" => $u,
    "password_hash" => password_hash($p, PASSWORD_DEFAULT) 
];

// 5. 添加到数组并保存回 JSON
$users[] = $new_user;

if (file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT))) {
    // 注册成功，跳回登录页让用户登录
    header("Location: login.php?error=Registration Successful! Please Login.");
} else {
    header("Location: register.php?error=System Error: Could not save data");
}
?>