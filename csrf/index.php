<?php
/**
 * CSRF基础演示 - 入门教学模块
 * 包含GET型、POST型、Token设置不严等演示
 */
session_start();

// 初始化用户数据
if (!isset($_SESSION['csrf_demo_users'])) {
    $_SESSION['csrf_demo_users'] = [
        'demo' => [
            'password' => 'demo123',
            'email' => 'demo@example.com',
            'nickname' => '演示用户',
            'phone' => '13800138000'
        ]
    ];
}

// 检查登录状态
$is_logged = isset($_SESSION['csrf_demo_user']);
$username = $is_logged ? $_SESSION['csrf_demo_user'] : '';
$user_info = $is_logged ? $_SESSION['csrf_demo_users'][$username] : [];

// 处理登录
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (isset($_SESSION['csrf_demo_users'][$user]) && 
        $_SESSION['csrf_demo_users'][$user]['password'] === $pass) {
        $_SESSION['csrf_demo_user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF基础演示 - 入门教学</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #667eea;
            font-size: 2em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header p {
            color: #666;
            font-size: 1.1em;
        }
        .nav-bar {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .nav-links {
            display: flex;
            gap: 10px;
        }
        .nav-links a {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #667eea;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 0.9em;
        }
        .nav-links a:hover, .nav-links a.active {
            background: #667eea;
            color: white;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info span {
            color: #333;
            font-weight: 500;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #f56c6c; color: white; }
        .btn-success { background: #67c23a; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
        .main-content {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }
        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .sidebar h3 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar li {
            margin-bottom: 10px;
        }
        .sidebar a {
            display: block;
            padding: 10px 15px;
            color: #666;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #667eea;
            color: white;
        }
        .content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        /* 登录框样式 */
        .login-box {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .login-box h2 {
            color: #667eea;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .alert-danger {
            background: #fef0f0;
            color: #f56c6c;
            border: 1px solid #fde2e2;
        }
        .alert-success {
            background: #f0f9ff;
            color: #67c23a;
            border: 1px solid #c2e7b0;
        }
        .alert-info {
            background: #ecf5ff;
            color: #409eff;
            border: 1px solid #d9ecff;
        }
        .alert-warning {
            background: #fdf6ec;
            color: #e6a23c;
            border: 1px solid #faecd8;
        }
        
        /* 欢迎页面样式 */
        .welcome-section {
            margin-bottom: 30px;
        }
        .welcome-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        .welcome-section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        /* 攻击流程图 */
        .flow-diagram {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .flow-step {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .flow-step:last-child {
            margin-bottom: 0;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-content h4 {
            color: #333;
            margin-bottom: 5px;
        }
        .step-content p {
            color: #666;
            font-size: 0.9em;
            margin: 0;
        }
        
        /* 功能卡片 */
        .feature-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .feature-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s;
        }
        .feature-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102,126,234,0.2);
        }
        .feature-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .feature-card p {
            color: #666;
            font-size: 0.9em;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .feature-card .btn {
            width: 100%;
            text-align: center;
        }
        
        /* 代码展示 */
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            overflow-x: auto;
            margin: 15px 0;
        }
        .code-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* 用户信息卡片 */
        .user-card {
            background: white;
            border: 3px solid #667eea;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        .user-card h3 {
            color: #3949ab;
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #e8eaf6;
            padding-bottom: 10px;
        }
        .user-card p {
            margin: 10px 0;
            color: #333;
            font-size: 1.05em;
            line-height: 1.8;
        }
        .user-card p strong {
            color: #1a237e;
            font-weight: 600;
        }
        
        .demo-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        .demo-info h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .demo-info p {
            color: #666;
            font-size: 0.9em;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$is_logged): ?>
        <!-- 未登录状态 - 显示登录框 -->
        <div class="login-box">
            <h2>🔐 CSRF基础演示</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                请登录以开始学习CSRF漏洞
            </p>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>👤 用户名</label>
                    <input type="text" name="username" value="demo" required>
                </div>
                <div class="form-group">
                    <label>🔑 密码</label>
                    <input type="password" name="password" value="demo123" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                    登录学习
                </button>
            </form>
            
            <div class="demo-info">
                <h4>📋 演示账号</h4>
                <p><strong>用户名：</strong>demo</p>
                <p><strong>密码：</strong>demo123</p>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="../index.php" class="btn btn-danger">🏠 返回主页</a>
            </div>
        </div>
        
        <?php else: ?>
        <!-- 已登录状态 - 显示教学内容 -->
        <div class="header">
            <h1>📚 CSRF基础演示 - 入门教学</h1>
            <p>从零开始学习CSRF（跨站请求伪造）漏洞原理与防护</p>
        </div>
        
        <div class="nav-bar">
            <div class="nav-links">
                <a href="index.php" class="active">🏠 首页</a>
                <a href="csrf_get.php">GET型攻击</a>
                <a href="csrf_post.php">POST型攻击</a>
                <a href="csrf_token_weak.php">Token缺陷</a>
                <a href="csrf_defense.php">防护方案</a>
            </div>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="btn btn-danger">退出登录</a>
                <a href="../index.php" class="btn btn-primary">🏠 返回主页</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="sidebar">
                <h3>📖 学习目录</h3>
                <ul>
                    <li><a href="#intro" class="active">1. 什么是CSRF</a></li>
                    <li><a href="#flow">2. 攻击流程</a></li>
                    <li><a href="#types">3. 攻击类型</a></li>
                    <li><a href="#demo">4. 演示场景</a></li>
                    <li><a href="#defense">5. 防护措施</a></li>
                </ul>
            </div>
            
            <div class="content">
                <!-- 什么是CSRF -->
                <div class="welcome-section" id="intro">
                    <h2>🎯 什么是CSRF？</h2>
                    <p>
                        <strong>CSRF（Cross-Site Request Forgery）</strong>，中文名为<strong>跨站请求伪造</strong>，
                        是一种常见的Web安全漏洞。
                    </p>
                    <div class="alert alert-info">
                        <strong>💡 核心原理：</strong>攻击者诱导已登录用户在不知情的情况下，向目标网站发送恶意请求，
                        利用用户的登录状态执行非预期操作。
                    </div>
                    <p>
                        例如：用户登录了银行网站，攻击者诱导用户点击恶意链接，在用户不知情的情况下完成转账操作。
                    </p>
                </div>
                
                <!-- 攻击流程图 -->
                <div class="welcome-section" id="flow">
                    <h2>🔄 CSRF攻击流程</h2>
                    <div class="flow-diagram">
                        <div class="flow-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>用户登录目标网站</h4>
                                <p>用户正常登录银行、论坛等网站，浏览器保存登录凭证（Cookie/Session）</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>攻击者构造恶意页面</h4>
                                <p>攻击者创建包含隐藏表单或链接的恶意页面，目标指向用户已登录的网站</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>诱导用户访问恶意页面</h4>
                                <p>通过邮件、社交媒体等方式，诱导用户点击恶意链接或访问恶意页面</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>自动发送恶意请求</h4>
                                <p>恶意页面自动提交表单或触发请求，浏览器自动携带用户的登录凭证</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h4>服务器执行恶意操作</h4>
                                <p>目标网站验证用户身份成功，执行转账、修改密码等敏感操作</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 攻击类型 -->
                <div class="welcome-section" id="types">
                    <h2>⚔️ CSRF攻击类型</h2>
                    <div class="feature-cards">
                        <div class="feature-card">
                            <h3>📥 GET型攻击</h3>
                            <p>
                                通过构造恶意URL，诱导用户点击或通过img标签自动触发。
                                简单直接，但容易被发现。
                            </p>
                            <div class="code-block">
                                &lt;img src="http://bank.com/transfer?to=attacker&amp;amount=1000"&gt;
                            </div>
                            <a href="csrf_get.php" class="btn btn-danger">查看演示</a>
                        </div>
                        
                        <div class="feature-card">
                            <h3>📤 POST型攻击</h3>
                            <p>
                                通过隐藏表单自动提交POST请求，更隐蔽，攻击成功率更高。
                                是最常见的CSRF攻击方式。
                            </p>
                            <div class="code-block">
                                &lt;form action="http://bank.com/transfer" method="POST"&gt;<br>
                                &nbsp;&nbsp;&lt;input name="to" value="attacker"&gt;<br>
                                &lt;/form&gt;
                            </div>
                            <a href="csrf_post.php" class="btn btn-danger">查看演示</a>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🔓 Token设置不严</h3>
                            <p>
                                虽然使用了CSRF Token，但Token可预测、可获取或验证不严格，
                                导致防护失效。
                            </p>
                            <div class="code-block">
                                // 弱Token示例<br>
                                token = md5(time()); // 可预测<br>
                                token = substr(md5(rand()), 0, 6); // 太短
                            </div>
                            <a href="csrf_token_weak.php" class="btn btn-danger">查看演示</a>
                        </div>
                    </div>
                </div>
                
                <!-- 演示场景 -->
                <div class="welcome-section" id="demo">
                    <h2>🎮 演示场景</h2>
                    <div class="user-card">
                        <h3>👤 当前用户信息</h3>
                        <p><strong>用户名：</strong><?php echo htmlspecialchars($username); ?></p>
                        <p><strong>邮箱：</strong><?php echo htmlspecialchars($user_info['email'] ?? ''); ?></p>
                        <p><strong>昵称：</strong><?php echo htmlspecialchars($user_info['nickname'] ?? ''); ?></p>
                        <p><strong>电话：</strong><?php echo htmlspecialchars($user_info['phone'] ?? ''); ?></p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>⚠️ 提示：</strong>以下演示场景将修改您的用户信息，请按顺序学习各个攻击类型。
                    </div>
                    
                    <div class="feature-cards">
                        <div class="feature-card">
                            <h3>🎯 场景1：GET型攻击</h3>
                            <p>
                                演示通过GET请求修改用户邮箱。攻击者构造恶意URL，
                                用户点击后邮箱被修改。
                            </p>
                            <a href="csrf_get.php" class="btn btn-primary">开始演示</a>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🎯 场景2：POST型攻击</h3>
                            <p>
                                演示通过POST请求修改用户信息。攻击者构造隐藏表单，
                                自动提交修改用户昵称和电话。
                            </p>
                            <a href="csrf_post.php" class="btn btn-primary">开始演示</a>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🎯 场景3：Token缺陷</h3>
                            <p>
                                演示Token设置不严导致的CSRF攻击。虽然使用了Token，
                                但Token可预测或验证不严格。
                            </p>
                            <a href="csrf_token_weak.php" class="btn btn-primary">开始演示</a>
                        </div>
                    </div>
                </div>
                
                <!-- 防护措施 -->
                <div class="welcome-section" id="defense">
                    <h2>🛡️ CSRF防护措施</h2>
                    <div class="feature-cards">
                        <div class="feature-card">
                            <h3>✅ 使用CSRF Token</h3>
                            <p>
                                为每个表单生成唯一的随机Token，服务器验证Token的有效性。
                                Token应不可预测、一次性使用。
                            </p>
                            <div class="code-block">
                                // 生成Token<br>
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));<br><br>
                                // 验证Token<br>
                                if ($_POST['token'] !== $_SESSION['csrf_token']) {<br>
                                &nbsp;&nbsp;die('CSRF验证失败');<br>
                                }
                            </div>
                        </div>
                        
                        <div class="feature-card">
                            <h3>✅ 验证Referer头</h3>
                            <p>
                                检查HTTP请求头中的Referer字段，确保请求来源于合法页面。
                                但注意Referer可能被篡改或禁用。
                            </p>
                            <div class="code-block">
                                $referer = $_SERVER['HTTP_REFERER'] ?? '';<br>
                                if (strpos($referer, 'https://example.com') !== 0) {<br>
                                &nbsp;&nbsp;die('非法请求来源');<br>
                                }
                            </div>
                        </div>
                        
                        <div class="feature-card">
                            <h3>✅ SameSite Cookie</h3>
                            <p>
                                设置Cookie的SameSite属性，限制第三方Cookie的发送。
                                可有效防止CSRF攻击。
                            </p>
                            <div class="code-block">
                                // PHP设置SameSite<br>
                                setcookie('session', $value, [<br>
                                &nbsp;&nbsp;'httponly' => true,<br>
                                &nbsp;&nbsp;'samesite' => 'Strict'<br>
                                ]);
                            </div>
                        </div>
                        
                        <div class="feature-card">
                            <h3>✅ 二次验证</h3>
                            <p>
                                对敏感操作（如转账、修改密码）要求二次验证，
                                如输入密码、验证码等。
                            </p>
                            <div class="code-block">
                                // 修改密码时要求输入旧密码<br>
                                if (!password_verify($_POST['old_password'], $user['password'])) {<br>
                                &nbsp;&nbsp;die('密码错误');<br>
                                }
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="csrf_defense.php" class="btn btn-success" style="width: 100%; text-align: center; padding: 15px;">
                            📖 查看完整防护方案
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>