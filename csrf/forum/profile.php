<?php
/**
 * 模拟论坛系统 - 个人中心
 */
session_start();

// 检查登录状态
if (!isset($_SESSION['forum_logged_in']) || $_SESSION['forum_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$dataFile = __DIR__ . '/data/forum_users.json';
$users = json_decode(file_get_contents($dataFile), true);
$username = $_SESSION['forum_user'];

// 生成CSRF Token
if (!isset($_SESSION['forum_csrf_token'])) {
    $_SESSION['forum_csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['forum_csrf_token'];

$message = '';
$error = '';

// 获取当前模式
$mode = $_COOKIE['forum_mode'] ?? 'vulnerable';

// 处理密码修改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // 根据模式决定是否验证CSRF Token
    if ($mode === 'secure') {
        $token = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['forum_csrf_token']) || $token !== $_SESSION['forum_csrf_token']) {
            $error = '⚠️ CSRF Token验证失败！检测到跨站请求伪造攻击！';
        }
    }
    
    if (empty($error)) {
        if (strlen($new_password) < 6) {
            $error = '密码长度至少6位';
        } elseif ($new_password !== $confirm_password) {
            $error = '两次输入的密码不一致';
        } else {
            $users[$username]['password'] = $new_password;
            file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $_SESSION['forum_user_data']['password'] = $new_password;
            
            $modeText = $mode === 'secure' ? '安全版本' : '漏洞版本';
            $message = "【{$modeText}】密码修改成功！";
            
            if ($mode === 'secure') {
                $_SESSION['forum_csrf_token'] = bin2hex(random_bytes(32));
                $csrf_token = $_SESSION['forum_csrf_token'];
            }
        }
    }
}

// 处理个人信息修改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nickname = trim($_POST['nickname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // 根据模式决定是否验证CSRF Token
    if ($mode === 'secure') {
        $token = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['forum_csrf_token']) || $token !== $_SESSION['forum_csrf_token']) {
            $error = '⚠️ CSRF Token验证失败！检测到跨站请求伪造攻击！';
        }
    }
    
    if (empty($error)) {
        if (empty($nickname)) {
            $error = '昵称不能为空';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '邮箱格式不正确';
        } else {
            $users[$username]['nickname'] = $nickname;
            $users[$username]['email'] = $email;
            file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $_SESSION['forum_user_data']['nickname'] = $nickname;
            $_SESSION['forum_user_data']['email'] = $email;
            
            $modeText = $mode === 'secure' ? '安全版本' : '漏洞版本';
            $message = "【{$modeText}】个人信息修改成功！";
            
            if ($mode === 'secure') {
                $_SESSION['forum_csrf_token'] = bin2hex(random_bytes(32));
                $csrf_token = $_SESSION['forum_csrf_token'];
            }
        }
    }
}

$userData = $users[$username];

function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技术交流论坛 - 个人中心</title>
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
            padding: 20px;
            padding-top: 90px;
        }
        .nav-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.98);
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
            font-size: 1.3em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-bar .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
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
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            color: white;
        }
        .profile-info h1 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 5px;
        }
        .profile-info .level {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            margin-bottom: 10px;
        }
        .profile-info .stats {
            color: #666;
            font-size: 0.95em;
        }
        .profile-info .stats span {
            margin-right: 20px;
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .card-header {
            padding: 20px 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header.vulnerable {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .card-header.secure {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-header h2 {
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mode-switch {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .mode-switch label {
            font-size: 0.9em;
            font-weight: normal;
        }
        .switch {
            position: relative;
            width: 60px;
            height: 30px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #f5576c;
            transition: .4s;
            border-radius: 30px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #667eea;
        }
        input:checked + .slider:before {
            transform: translateX(30px);
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
        .info-box {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #0c5460;
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
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
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
        <div class="logo">
            <span>💬</span>
            <span>技术交流论坛</span>
        </div>
        <div class="nav-links">
            <a href="../../index.php">🏠 返回主页</a>
            <a href="logout.php">🚪 退出登录</a>
        </div>
    </div>
    
    <div class="container">
        <div class="profile-card">
            <div class="avatar">👤</div>
            <div class="profile-info">
                <h1><?php echo h($userData['nickname']); ?></h1>
                <span class="level"><?php echo h($userData['level']); ?></span>
                <div class="stats">
                    <span>📧 <?php echo h($userData['email']); ?></span>
                    <span>📝 发帖 <?php echo h($userData['posts']); ?> 篇</span>
                    <span>👤 用户名：<?php echo h($username); ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>
        
        <!-- 修改密码 -->
        <div class="card">
            <div class="card-header <?php echo $mode === 'secure' ? 'secure' : 'vulnerable'; ?>">
                <h2>
                    <?php if ($mode === 'secure'): ?>
                    ✅ 修改密码（安全版本 - 有CSRF防护）
                    <?php else: ?>
                    ⚠️ 修改密码（漏洞版本 - 无CSRF防护）
                    <?php endif; ?>
                </h2>
                <div class="mode-switch">
                    <label>漏洞</label>
                    <label class="switch">
                        <input type="checkbox" id="modeSwitch" <?php echo $mode === 'secure' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                    <label>安全</label>
                </div>
            </div>
            <div class="card-body">
                <?php if ($mode === 'secure'): ?>
                <div class="info-box">
                    <strong>✅ 安全提示：</strong>此表单包含CSRF Token验证，可以有效防止跨站请求伪造攻击。
                </div>
                <?php else: ?>
                <div class="warning-box">
                    <strong>⚠️ 安全警告：</strong>此表单没有CSRF Token保护，攻击者可以构造恶意页面诱导用户修改密码！
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">
                    <?php if ($mode === 'secure'): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>🔑 新密码</label>
                        <input type="password" name="new_password" placeholder="请输入新密码（至少6位）" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label>🔑 确认新密码</label>
                        <input type="password" name="confirm_password" placeholder="请再次输入新密码" required>
                    </div>
                    
                    <button type="submit" class="btn <?php echo $mode === 'secure' ? 'btn-primary' : 'btn-danger'; ?>">修改密码</button>
                </form>
                
                <?php if ($mode === 'secure'): ?>
                <div class="token-display">
                    <strong>当前CSRF Token：</strong><br>
                    <?php echo h($csrf_token); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 修改个人信息 -->
        <div class="card">
            <div class="card-header <?php echo $mode === 'secure' ? 'secure' : 'vulnerable'; ?>">
                <h2>
                    <?php if ($mode === 'secure'): ?>
                    ✅ 修改个人信息（安全版本 - 有CSRF防护）
                    <?php else: ?>
                    ⚠️ 修改个人信息（漏洞版本 - 无CSRF防护）
                    <?php endif; ?>
                </h2>
            </div>
            <div class="card-body">
                <?php if ($mode === 'secure'): ?>
                <div class="info-box">
                    <strong>✅ 安全提示：</strong>此表单包含CSRF Token验证，可以有效防止跨站请求伪造攻击。
                </div>
                <?php else: ?>
                <div class="warning-box">
                    <strong>⚠️ 安全警告：</strong>此表单没有CSRF Token保护，攻击者可以构造恶意页面诱导用户修改个人信息！
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <?php if ($mode === 'secure'): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>👤 昵称</label>
                        <input type="text" name="nickname" value="<?php echo h($userData['nickname']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>📧 邮箱</label>
                        <input type="email" name="email" value="<?php echo h($userData['email']); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn <?php echo $mode === 'secure' ? 'btn-primary' : 'btn-danger'; ?>">保存修改</button>
                </form>
                
                <?php if ($mode === 'secure'): ?>
                <div class="token-display">
                    <strong>当前CSRF Token：</strong><br>
                    <?php echo h($csrf_token); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // 模式切换
        document.getElementById('modeSwitch').addEventListener('change', function() {
            const mode = this.checked ? 'secure' : 'vulnerable';
            document.cookie = 'forum_mode=' + mode + ';path=/;max-age=86400';
            location.reload();
        });
    </script>
</body>
</html>