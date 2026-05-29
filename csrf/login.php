<?php
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 初始化用户数据
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        'admin' => ['password' => '123456', 'email' => 'admin@test.com', 'phone' => '13800138000']
    ];
}

// 如果不是POST请求，重定向到首页
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 验证登录
if (isset($_SESSION['users'][$username]) && $_SESSION['users'][$username]['password'] === $password) {
    $_SESSION['username'] = $username;
    header('Location: index.php');
} else {
    echo '<script>alert("登录失败！用户名或密码错误"); location.href="index.php";</script>';
}