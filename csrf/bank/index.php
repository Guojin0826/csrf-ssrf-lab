<?php
/**
 * 模拟银行系统 - 首页/登录
 */
session_start();

// 初始化银行数据库
if (!isset($_SESSION['bank_users'])) {
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
}

// 已登录则跳转到账户中心
if (isset($_SESSION['bank_logged_in']) && $_SESSION['bank_logged_in'] === true) {
    header('Location: account.php');
    exit;
}

$error = '';

// 处理登录
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (isset($_SESSION['bank_users'][$username]) && 
        $_SESSION['bank_users'][$username]['password'] === $password) {
        $_SESSION['bank_logged_in'] = true;
        $_SESSION['bank_user'] = $username;
        header('Location: account.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}

function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安全银行 - 登录</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 420px;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header .logo {
            font-size: 3em;
            margin-bottom: 10px;
        }
        .login-header h1 {
            font-size: 1.8em;
            margin-bottom: 5px;
        }
        .login-header p {
            opacity: 0.9;
            font-size: 0.9em;
        }
        .login-body {
            padding: 40px 30px;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95em;
        }
        .form-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42,82,152,0.1);
        }
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(30,60,114,0.4);
        }
        .demo-info {
            margin-top: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.85em;
            color: #666;
        }
        .demo-info h4 {
            color: #2a5298;
            margin-bottom: 8px;
        }
        .demo-info p {
            margin: 3px 0;
        }
        .demo-info strong {
            color: #333;
        }
        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            color: #2a5298;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s;
        }
        .btn-back:hover {
            color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">🏦</div>
            <h1>安全银行</h1>
            <p>Safe Bank - 您的财富守护者</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo h($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>👤 用户名</label>
                    <input type="text" name="username" placeholder="请输入用户名" required autofocus>
                </div>
                <div class="form-group">
                    <label>🔑 密码</label>
                    <input type="password" name="password" placeholder="请输入密码" required>
                </div>
                <button type="submit" class="btn-login">安全登录</button>
            </form>
            
            <a href="../../index.php" class="btn-back">🏠 返回主页</a>
            
            <div class="demo-info">
                <h4>📋 演示账号</h4>
                <p><strong>张三：</strong>zhangsan / 123456（余额：¥50,000）</p>
                <p><strong>李四：</strong>lisi / 123456（余额：¥30,000）</p>
                <p><strong>王五：</strong>wangwu / 123456（余额：¥80,000）</p>
            </div>
        </div>
    </div>
</body>
</html>