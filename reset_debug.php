<?php
/**
 * 初始化/重置演示环境 - 调试版本
 * 将所有数据恢复到初始状态
 */

// 开启错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h2>初始化调试信息</h2>";

try {
    // 清除所有session数据
    $_SESSION = array();
    echo "✅ Session已清除<br>";

    // 删除session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    echo "✅ Session Cookie已删除<br>";

    // 销毁session
    session_destroy();
    echo "✅ Session已销毁<br>";

    // 重新开始session
    session_start();
    echo "✅ 新Session已启动<br>";

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
    echo "✅ 银行用户数据已重置<br>";

    // 重置论坛用户数据
    $forumDataFile = __DIR__ . '/csrf/forum/data/forum_users.json';
    $forumDataDir = dirname($forumDataFile);
    
    echo "论坛数据文件路径: " . $forumDataFile . "<br>";
    echo "论坛数据目录路径: " . $forumDataDir . "<br>";

    // 确保数据目录存在
    if (!is_dir($forumDataDir)) {
        if (mkdir($forumDataDir, 0777, true)) {
            echo "✅ 论坛数据目录已创建<br>";
        } else {
            throw new Exception("无法创建目录: " . $forumDataDir);
        }
    } else {
        echo "✅ 论坛数据目录已存在<br>";
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

    // 写入论坛用户数据
    $result = file_put_contents($forumDataFile, json_encode($forumUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($result === false) {
        throw new Exception("无法写入论坛用户数据文件");
    }
    echo "✅ 论坛用户数据已写入 (" . $result . " 字节)<br>";

    // 重置原有CSRF演示用户数据
    $_SESSION['users'] = [
        'admin' => [
            'password' => '123456',
            'email' => 'admin@test.com',
            'phone' => '13800138000'
        ]
    ];
    echo "✅ CSRF Session用户数据已重置<br>";

    // 重置原有CSRF演示用户数据（config.php使用的文件）
    $csrfDataFile = __DIR__ . '/csrf/data/users.json';
    $csrfDataDir = dirname($csrfDataFile);
    
    echo "CSRF数据文件路径: " . $csrfDataFile . "<br>";
    echo "CSRF数据目录路径: " . $csrfDataDir . "<br>";

    // 确保目录存在
    if (!is_dir($csrfDataDir)) {
        if (mkdir($csrfDataDir, 0777, true)) {
            echo "✅ CSRF数据目录已创建<br>";
        } else {
            throw new Exception("无法创建目录: " . $csrfDataDir);
        }
    } else {
        echo "✅ CSRF数据目录已存在<br>";
    }

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

    // 写入CSRF用户数据
    $result = file_put_contents($csrfDataFile, json_encode($csrfUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($result === false) {
        throw new Exception("无法写入CSRF用户数据文件");
    }
    echo "✅ CSRF用户数据已写入 (" . $result . " 字节)<br>";

    echo "<h2 style='color: green;'>✅ 初始化成功！</h2>";
    echo "<p><a href='index.php'>返回主页</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ 初始化失败</h2>";
    echo "<p style='color: red;'>错误信息: " . $e->getMessage() . "</p>";
    echo "<p>文件路径: " . $e->getFile() . "</p>";
    echo "<p>行号: " . $e->getLine() . "</p>";
}
?>