<?php
/**
 * 初始化/重置演示环境
 * 将所有数据恢复到初始状态
 */
session_start();

// 清除所有session数据
$_SESSION = array();

// 删除session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 销毁session
session_destroy();

// 重新开始session
session_start();

// 重置银行系统数据
$_SESSION['bank_users'] = [
    'zhangsan' => [
        'password' => '123456',
        'name' => '张三',
        'balance' => 50000.00,
        'account' => '6222021234567890123'
    ],
    'lisi' => [
        'password' => '123456',
        'name' => '李四',
        'balance' => 30000.00,
        'account' => '6222021234567890456'
    ],
    'wangwu' => [
        'password' => '123456',
        'name' => '王五',
        'balance' => 80000.00,
        'account' => '6222021234567890789'
    ]
];

// 重置论坛用户数据
$forumDataFile = __DIR__ . '/csrf/forum/data/forum_users.json';
$forumDataDir = dirname($forumDataFile);

// 确保数据目录存在
if (!is_dir($forumDataDir)) {
    mkdir($forumDataDir, 0755, true);
}

$forumUsers = [
    'admin' => [
        'password' => 'admin123',
        'nickname' => '管理员',
        'email' => 'admin@forum.com',
        'posts' => 15,
        'level' => '版主'
    ],
    'xiaoming' => [
        'password' => '123456',
        'nickname' => '小明同学',
        'email' => 'xiaoming@forum.com',
        'posts' => 8,
        'level' => '活跃用户'
    ],
    'zhangsan' => [
        'password' => '123456',
        'nickname' => '张三丰',
        'email' => 'zhangsan@forum.com',
        'posts' => 23,
        'level' => '资深会员'
    ]
];
file_put_contents($forumDataFile, json_encode($forumUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 重置原有CSRF演示用户数据
$_SESSION['users'] = [
    'admin' => [
        'password' => '123456',
        'email' => 'admin@test.com',
        'phone' => '13800138000'
    ]
];

// 重置原有CSRF演示用户数据（config.php使用的文件）
$csrfDataFile = __DIR__ . '/csrf/data/users.json';
$csrfUsers = [
    'admin' => [
        'password' => 'admin123',
        'email' => 'admin@example.com',
        'nickname' => '管理员',
        'phone' => '13800138000'
    ],
    'user1' => [
        'password' => 'user123',
        'email' => 'user1@example.com',
        'nickname' => '测试用户',
        'phone' => '13900139000'
    ]
];

// 确保目录存在
if (!is_dir(dirname($csrfDataFile))) {
    mkdir(dirname($csrfDataFile), 0777, true);
}

file_put_contents($csrfDataFile, json_encode($csrfUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 返回成功信息
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => '演示环境已重置到初始状态',
    'data' => [
        'bank_users' => count($_SESSION['bank_users']),
        'forum_users' => count($forumUsers),
        'csrf_users' => count($csrfUsers)
    ]
]);
?>