<?php
session_start();

// 1. 获取输入
$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';

// 2. 检查数据库文件是否存在
if (!file_exists('data/users.json')) {
    header("Location: login.php?error=System Error: Database not found");
    exit;
}

// 3. 读取用户数据
$users = json_decode(file_get_contents('data/users.json'), true);

// 4. 验证账号密码
foreach ($users as $user) {
    if ($user['username'] === $u) {
        // 验证哈希密码
        if (password_verify($p, $user['password_hash'])) {
            $_SESSION['user'] = $u; // 登录成功，保存Session
            header("Location: index.php"); // 跳回主页
            exit();
        }
    }
}

// 5. 失败，跳回登录页
header("Location: login.php?error=Invalid Username or Password");
?>