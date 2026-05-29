<?php
/**
 * CSRF模块配置文件
 */

// 启动会话
session_start();

// 数据库配置（使用文件模拟数据库）
define('USERS_FILE', __DIR__ . '/data/users.json');
define('CSRF_TOKENS_FILE', __DIR__ . '/data/csrf_tokens.json');

// 确保数据目录存在
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

// 初始化用户数据
function initUsers() {
    if (!file_exists(USERS_FILE)) {
        $users = [
            'admin' => [
                'password' => 'admin123',  // 演示环境使用明文密码
                'email' => 'admin@example.com',
                'nickname' => '管理员',
                'phone' => '13800138000'
            ],
            'user1' => [
                'password' => 'user123',  // 演示环境使用明文密码
                'email' => 'user1@example.com',
                'nickname' => '测试用户',
                'phone' => '13900139000'
            ]
        ];
        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// 获取用户数据
function getUsers() {
    initUsers();
    $content = file_get_contents(USERS_FILE);
    return json_decode($content, true);
}

// 保存用户数据
function saveUsers($users) {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 检查用户是否登录
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// 获取当前登录用户
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// 获取或生成CSRF Token（如果已存在则复用）
function getCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return generateCSRFToken();
    }
    
    // Token有效期30分钟，过期则重新生成
    if (time() - $_SESSION['csrf_token_time'] > 1800) {
        return generateCSRFToken();
    }
    
    return $_SESSION['csrf_token'];
}

// 生成新的CSRF Token
function generateCSRFToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

// 验证CSRF Token
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Token有效期30分钟
    if (time() - $_SESSION['csrf_token_time'] > 1800) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 安全输出HTML
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>