<?php
/**
 * SSRF模拟数据 - 内部用户信息
 * 模拟内网用户数据库
 */

header('Content-Type: application/json');

$users = [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@internal.com',
        'role' => 'Administrator',
        'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'api_key' => 'sk-admin-1234567890abcdef',
        'created_at' => '2024-01-01 00:00:00'
    ],
    [
        'id' => 2,
        'username' => 'zhangsan',
        'email' => 'zhangsan@internal.com',
        'role' => 'User',
        'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'api_key' => 'sk-user-abcdef1234567890',
        'created_at' => '2024-01-15 10:30:00'
    ],
    [
        'id' => 3,
        'username' => 'lisi',
        'email' => 'lisi@internal.com',
        'role' => 'Manager',
        'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'api_key' => 'sk-manager-xyz789abc456',
        'created_at' => '2024-02-01 14:20:00'
    ]
];

echo json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>