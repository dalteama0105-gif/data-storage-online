<?php
// 创建 admin / 123456 账号
$data = [
    [
        "username" => "admin",
        "password_hash" => password_hash("123456", PASSWORD_DEFAULT)
    ]
];

if (!file_exists('data')) { mkdir('data'); }
file_put_contents('data/users.json', json_encode($data));
echo "账号已重置: admin / 123456";
?>