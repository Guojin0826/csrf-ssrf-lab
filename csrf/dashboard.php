<?php
require_once 'config.php';

// 检查登录状态
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$users = getUsers();
$username = $user['username'];

// 获取CSRF Token用于安全表单（如果已存在则复用）
$csrf_token = getCSRFToken();

$message = '';
$error = '';

// 处理用户信息修改（有CSRF漏洞的版本）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_vulnerable') {
    // ⚠️ 漏洞：没有验证CSRF Token，攻击者可以构造恶意页面诱导用户提交
    $email = $_POST['email'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    $users[$username]['email'] = $email;
    $users[$username]['nickname'] = $nickname;
    $users[$username]['phone'] = $phone;
    saveUsers($users);
    
    // 更新会话信息
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['nickname'] = $nickname;
    $_SESSION['user']['phone'] = $phone;
    
    $message = '【漏洞版本】个人信息修改成功！注意：此接口没有CSRF防护，可被攻击利用。';
}

// 处理用户信息修改（安全的版本）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_secure') {
    $token = $_POST['csrf_token'] ?? '';
    
    // ✅ 安全：验证CSRF Token
    if (!validateCSRFToken($token)) {
        $error = 'CSRF Token验证失败！这可能是跨站请求伪造攻击。';
    } else {
        $email = $_POST['email'] ?? '';
        $nickname = $_POST['nickname'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        $users[$username]['email'] = $email;
        $users[$username]['nickname'] = $nickname;
        $users[$username]['phone'] = $phone;
        saveUsers($users);
        
        // 更新会话信息
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['nickname'] = $nickname;
        $_SESSION['user']['phone'] = $phone;
        
        $message = '【安全版本】个人信息修改成功！CSRF Token验证通过。';
        
        // 生成新的CSRF Token
        $csrf_token = generateCSRFToken();
    }
}

// 刷新用户数据
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户中心 - CSRF演示</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
            padding-top: 90px;
        }
        .nav-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.95);
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }
        .nav-bar .logo {
            font-weight: bold;
            color: #667eea;
            font-size: 1.2em;
        }
        .nav-bar .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-bar .nav-links a {
            color: #666;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .nav-bar .nav-links a:hover {
            background: #667eea;
            color: white;
        }
        .nav-bar .nav-links a.active {
            background: #667eea;
            color: white;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
            font-size: 1.5em;
        }
        .header .user-info {
            color: #666;
        }
        .header .user-info strong {
            color: #667eea;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card-header {
            padding: 20px 25px;
            color: white;
        }
        .vulnerable {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .secure {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-header h2 {
            font-size: 1.3em;
        }
        .card-body {
            padding: 25px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .current-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .current-info h4 {
            color: #333;
            margin-bottom: 10px;
        }
        .current-info p {
            color: #666;
            margin: 5px 0;
        }
        .current-info strong {
            color: #667eea;
        }
        .token-display {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.85em;
            word-break: break-all;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="logo">🔐 Web安全漏洞演示平台</div>
        <div class="nav-links">
            <a href="../index.php">🏠 返回主页</a>
            <a href="logout.php">🚪 退出登录</a>
        </div>
    </div>
    <div class="container">
        <div class="header">
            <h1>🔐 用户中心</h1>
            <div class="user-info">
                欢迎您，<strong><?php echo h($user['nickname']); ?></strong> (<?php echo h($username); ?>)
                <a href="logout.php" class="btn btn-secondary" style="margin-left: 15px; padding: 8px 15px;">退出登录</a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>

        <!-- 当前用户信息 -->
        <div class="current-info">
            <h4>📋 当前用户信息</h4>
            <p>邮箱：<strong><?php echo h($user['email']); ?></strong></p>
            <p>昵称：<strong><?php echo h($user['nickname']); ?></strong></p>
            <p>电话：<strong><?php echo h($user['phone']); ?></strong></p>
        </div>

        <!-- 漏洞版本 -->
        <div class="card">
            <div class="card-header vulnerable">
                <h2>⚠️ 修改个人信息（有CSRF漏洞）</h2>
            </div>
            <div class="card-body">
                <div class="warning-box">
                    <strong>⚠️ 漏洞说明：</strong>此表单没有CSRF Token保护，攻击者可以构造恶意页面，诱导已登录用户点击后自动提交修改请求。
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_vulnerable">
                    <div class="form-group">
                        <label>邮箱</label>
                        <input type="email" name="email" value="<?php echo h($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>昵称</label>
                        <input type="text" name="nickname" value="<?php echo h($user['nickname']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>电话</label>
                        <input type="text" name="phone" value="<?php echo h($user['phone']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-danger">提交修改（漏洞版本）</button>
                    <div class="token-display" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                        <strong>⚠️ 此表单没有CSRF Token保护！</strong><br>
                        攻击者可以伪造此请求，在用户不知情的情况下提交。
                    </div>
                </form>
            </div>
        </div>

        <!-- 安全版本 -->
        <div class="card">
            <div class="card-header secure">
                <h2>✅ 修改个人信息（已修复CSRF漏洞）</h2>
            </div>
            <div class="card-body">
                <div class="warning-box" style="background: #d4edda; border-color: #28a745; color: #155724;">
                    <strong>✅ 安全措施：</strong>此表单包含CSRF Token，每次提交都会验证Token的有效性，防止跨站请求伪造攻击。
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_secure">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                    <div class="form-group">
                        <label>邮箱</label>
                        <input type="email" name="email" value="<?php echo h($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>昵称</label>
                        <input type="text" name="nickname" value="<?php echo h($user['nickname']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>电话</label>
                        <input type="text" name="phone" value="<?php echo h($user['phone']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">提交修改（安全版本）</button>
                    <div class="token-display">
                        <strong>✅ 当前CSRF Token：</strong><br>
                        <code style="background: #f8f9fa; padding: 5px; border-radius: 3px; word-break: break-all;"><?php echo h($csrf_token); ?></code>
                        <br><br>
                        <small style="color: #666;">
                            💡 此Token存储在您的Session中，每次提交表单时都会验证Token是否匹配。<br>
                            攻击者无法获取您的Token，因此无法伪造请求。
                        </small>
                    </div>
                </form>
            </div>
        </div>

        <div class="btn-group">
            <a href="attack.html" class="btn btn-danger">查看CSRF攻击演示页面</a>
            <a href="../index.php" class="btn btn-secondary">返回首页</a>
        </div>
    </div>
</body>
</html>