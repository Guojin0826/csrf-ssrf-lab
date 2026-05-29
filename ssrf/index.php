<?php
/**
 * SSRF基础演示 - 入门教学模块
 * 包含基础SSRF、协议利用、内网探测等演示
 */
session_start();

// 初始化用户数据
if (!isset($_SESSION['ssrf_demo_users'])) {
    $_SESSION['ssrf_demo_users'] = [
        'demo' => [
            'password' => 'demo123',
            'role' => 'User'
        ]
    ];
}

// 检查登录状态
$is_logged = isset($_SESSION['ssrf_demo_user']);
$username = $is_logged ? $_SESSION['ssrf_demo_user'] : '';

// 处理登录
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (isset($_SESSION['ssrf_demo_users'][$user]) && 
        $_SESSION['ssrf_demo_users'][$user]['password'] === $pass) {
        $_SESSION['ssrf_demo_user'] = $user;
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
    <title>SSRF基础演示 - 入门教学</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        /* 导航栏 */
        .navbar {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            color: #11998e;
            font-size: 1.5em;
        }
        .nav-links { display: flex; gap: 10px; }
        .nav-links a {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #11998e;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 0.9em;
        }
        .nav-links a:hover, .nav-links a.active {
            background: #11998e;
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
            display: inline-block;
        }
        .btn-primary { background: #11998e; color: white; }
        .btn-success { background: #67c23a; color: white; }
        .btn-danger { background: #f56c6c; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
        .main-content {
            display: flex;
            min-height: 800px;
        }
        
        /* 侧边栏 */
        .sidebar {
            width: 250px;
            background: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #e0e0e0;
        }
        .sidebar h3 {
            color: #11998e;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar li {
            margin-bottom: 8px;
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
            background: #11998e;
            color: white;
        }
        
        .content {
            flex: 1;
            padding: 30px;
        }
        
        /* 登录框 */
        .login-box {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .login-box h2 {
            color: #11998e;
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
            border-color: #11998e;
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
        
        /* 欢迎页面 */
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
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border-left: 4px solid #11998e;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #11998e;
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
            color: #11998e;
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
            border-color: #11998e;
            box-shadow: 0 5px 15px rgba(17,153,142,0.2);
        }
        .feature-card h3 {
            color: #11998e;
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
        
        /* 用户信息卡片 */
        .user-card {
            background: white;
            border: 3px solid #11998e;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.2);
        }
        .user-card h3 {
            color: #0d7377;
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }
        .user-card p {
            margin: 10px 0;
            color: #333;
            font-size: 1.05em;
            line-height: 1.8;
        }
        .user-card p strong {
            color: #075e5e;
            font-weight: 600;
        }
        
        .demo-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        .demo-info h4 {
            color: #11998e;
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
            <h2>🔐 SSRF基础演示</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                请登录以开始学习SSRF漏洞
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
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="../index.php" class="btn btn-danger">🏠 返回主页</a>
            </div>
        </div>
        
        <?php else: ?>
        <!-- 已登录状态 - 显示教学内容 -->
        <div class="navbar">
            <h1>🎓 SSRF基础演示 - 入门教学</h1>
            <div class="nav-links">
                <a href="#intro">什么是SSRF</a>
                <a href="#flow">攻击流程</a>
                <a href="#types">攻击类型</a>
                <a href="#demo">演示场景</a>
                <a href="#defense">防护措施</a>
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
                    <li><a href="#intro" class="active">1. 什么是SSRF</a></li>
                    <li><a href="#flow">2. 攻击流程</a></li>
                    <li><a href="#types">3. 攻击类型</a></li>
                    <li><a href="#demo">4. 演示场景</a></li>
                    <li><a href="#defense">5. 防护措施</a></li>
                </ul>
            </div>
            
            <div class="content">
                <!-- 什么是SSRF -->
                <div class="welcome-section" id="intro">
                    <h2>📖 什么是SSRF</h2>
                    <div class="alert alert-info">
                        <strong>SSRF (Server-Side Request Forgery)</strong> 服务端请求伪造，是一种由攻击者构造请求，由服务端发起请求的安全漏洞。
                    </div>
                    <p>
                        SSRF漏洞允许攻击者从易受攻击的应用程序的后端服务器发送精心设计的请求，
                        从而攻击内部系统、访问内部服务、读取本地文件等。
                    </p>
                    <p>
                        <strong>核心原理：</strong>攻击者利用服务器作为代理，访问攻击者无法直接访问的资源，
                        如内网服务、云元数据、本地文件等。
                    </p>
                    
                    <div class="alert alert-warning">
                        <strong>⚠️ 危害等级：</strong>高危漏洞<br>
                        <strong>影响范围：</strong>可导致内网探测、敏感信息泄露、远程代码执行等严重后果
                    </div>
                </div>
                
                <!-- 攻击流程图 -->
                <div class="welcome-section" id="flow">
                    <h2>🔄 SSRF攻击流程</h2>
                    <div class="flow-diagram">
                        <div class="flow-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>发现输入点</h4>
                                <p>攻击者发现应用程序接受URL参数，如图片加载、文件读取、API调用等功能</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>构造恶意URL</h4>
                                <p>攻击者构造指向内网地址、本地文件或云元数据的URL，如 http://127.0.0.1、file:///etc/passwd</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>服务器发起请求</h4>
                                <p>服务器接收用户输入的URL，使用curl、file_get_contents等函数发起请求</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>访问受限资源</h4>
                                <p>服务器作为代理访问内网资源、读取本地文件、获取云元数据等敏感信息</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h4>信息泄露</h4>
                                <p>服务器将敏感信息返回给攻击者，导致内网拓扑泄露、凭证泄露等严重后果</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 攻击类型 -->
                <div class="welcome-section" id="types">
                    <h2>🎯 SSRF攻击类型</h2>
                    <div class="feature-cards">
                        <div class="feature-card">
                            <h3>🌐 基础SSRF</h3>
                            <p>
                                直接使用用户输入的URL，没有任何验证。攻击者可以访问内网服务、
                                本地文件、云元数据等。
                            </p>
                            <div class="alert alert-danger" style="font-size: 0.85em;">
                                <strong>示例：</strong><br>
                                http://127.0.0.1/admin<br>
                                file:///etc/passwd
                            </div>
                        </div>
                        
                        <div class="feature-card">
                            <h3>📡 协议利用</h3>
                            <p>
                                利用不同协议（file://、dict://、gopher://等）进行攻击，
                                可读取文件、探测服务、执行命令。
                            </p>
                            <div class="alert alert-danger" style="font-size: 0.85em;">
                                <strong>示例：</strong><br>
                                file:///etc/passwd<br>
                                dict://127.0.0.1:6379/info
                            </div>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🔍 内网探测</h3>
                            <p>
                                通过SSRF探测内网服务、端口开放情况、内网拓扑结构，
                                为进一步攻击提供信息。
                            </p>
                            <div class="alert alert-danger" style="font-size: 0.85em;">
                                <strong>示例：</strong><br>
                                http://192.168.1.1:80<br>
                                http://10.0.0.1:22
                            </div>
                        </div>
                        
                        <div class="feature-card">
                            <h3>☁️ 云元数据</h3>
                            <p>
                                访问云服务商的元数据服务，获取IAM角色、临时凭证、
                                实例信息等敏感数据。
                            </p>
                            <div class="alert alert-danger" style="font-size: 0.85em;">
                                <strong>示例：</strong><br>
                                AWS: http://169.254.169.254/latest/meta-data/<br>
                                阿里云: http://100.100.100.200/latest/meta-data/
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 演示场景 -->
                <div class="welcome-section" id="demo">
                    <h2>🎮 演示场景</h2>
                    <div class="user-card">
                        <h3>👤 当前用户信息</h3>
                        <p><strong>用户名：</strong><?php echo htmlspecialchars($username); ?></p>
                        <p><strong>角色：</strong><?php echo htmlspecialchars($_SESSION['ssrf_demo_users'][$username]['role'] ?? ''); ?></p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>⚠️ 提示：</strong>以下演示场景将展示SSRF攻击的危害，请按顺序学习各个攻击类型。
                    </div>
                    
                    <div class="feature-cards">
                        <div class="feature-card">
                            <h3>🎯 场景1：基础SSRF</h3>
                            <p>
                                演示无防护的URL请求功能，攻击者可以访问内网服务、
                                读取本地文件、获取敏感信息。
                            </p>
                            <a href="ssrf_basic.php" class="btn btn-primary">开始演示</a>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🎯 场景2：协议利用</h3>
                            <p>
                                演示利用不同协议（file、dict、gopher）进行攻击，
                                读取文件、探测服务、执行命令。
                            </p>
                            <a href="ssrf_protocol.php" class="btn btn-primary">开始演示</a>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🎯 场景3：内网探测</h3>
                            <p>
                                演示通过SSRF探测内网服务、端口开放情况，
                                获取内网拓扑信息。
                            </p>
                            <a href="ssrf_scan.php" class="btn btn-primary">开始演示</a>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🎯 场景4：云元数据</h3>
                            <p>
                                演示访问云元数据服务，获取IAM角色、临时凭证、
                                实例信息等敏感数据。
                            </p>
                            <a href="ssrf_cloud.php" class="btn btn-primary">开始演示</a>
                        </div>
                        
                        <div class="feature-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                            <h3 style="color: white;">🎯 场景5：拟真环境演示</h3>
                            <p style="color: rgba(255,255,255,0.9);">
                                模拟真实环境中的SSRF漏洞场景：图片加载、Webhook回调、PDF导出、
                                URL代理、API转发、缓存预加载等6种真实场景。
                            </p>
                            <a href="real/index.php" class="btn" style="background: white; color: #667eea;">开始演示</a>
                        </div>
                    </div>
                </div>
                
                <!-- 防护措施 -->
                <div class="welcome-section" id="defense">
                    <h2>🛡️ SSRF防护措施</h2>
                    <div class="feature-cards">
                        <div class="feature-card">
                            <h3>✅ URL白名单</h3>
                            <p>
                                只允许访问预定义的白名单域名和IP地址，
                                拒绝所有其他请求。
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <h3>✅ 协议限制</h3>
                            <p>
                                只允许特定的协议（如http、https），
                                禁用file://、dict://、gopher://等危险协议。
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <h3>✅ 内网IP过滤</h3>
                            <p>
                                检查解析后的IP地址，拒绝访问私有IP段
                                （10.0.0.0/8、172.16.0.0/12、192.168.0.0/16）。
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <h3>✅ DNS重绑定防护</h3>
                            <p>
                                验证域名解析后的IP地址，防止DNS重绑定攻击。
                                在请求前后都要检查IP地址。
                            </p>
                        </div>
                    </div>
                    
                    <div class="alert alert-success" style="margin-top: 20px;">
                        <strong>💡 最佳实践：</strong><br>
                        1. 避免用户直接控制URL<br>
                        2. 使用URL白名单而非黑名单<br>
                        3. 验证解析后的IP地址<br>
                        4. 禁用不必要的协议<br>
                        5. 使用网络隔离和最小权限原则
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>