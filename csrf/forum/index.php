<?php
/**
 * 模拟论坛系统 - 登录页面
 */
session_start();

// 初始化论坛用户数据
$dataFile = __DIR__ . '/data/forum_users.json';
if (!file_exists($dataFile)) {
    $defaultUsers = [
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
    file_put_contents($dataFile, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 已登录则跳转到个人中心
if (isset($_SESSION['forum_logged_in']) && $_SESSION['forum_logged_in'] === true) {
    header('Location: profile.php');
    exit;
}

$error = '';

// 处理登录
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $users = json_decode(file_get_contents($dataFile), true);
    
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['forum_logged_in'] = true;
        $_SESSION['forum_user'] = $username;
        $_SESSION['forum_user_data'] = $users[$username];
        header('Location: profile.php');
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
    <title>技术交流论坛 - 登录</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            width: 450px;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
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
            color: #667eea;
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
            color: #667eea;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s;
        }
        .btn-back:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">💬</div>
            <h1>技术交流论坛</h1>
            <p>Tech Forum - 分享知识，共同成长</p>
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
                <button type="submit" class="btn-login">登录论坛</button>
            </form>
            
            <a href="../../index.php" class="btn-back">🏠 返回主页</a>
            
            <div class="demo-info">
                <h4>📋 演示账号</h4>
                <p><strong>管理员：</strong>admin / admin123（版主）</p>
                <p><strong>小明：</strong>xiaoming / 123456（活跃用户）</p>
                <p><strong>张三：</strong>zhangsan / 123456（资深会员）</p>
            </div>
        </div>
    </div>
</body>
</html>