<?php
/**
 * CSRF GET型攻击演示
 */
session_start();

// 检查登录
if (!isset($_SESSION['csrf_demo_user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['csrf_demo_user'];

// 处理GET请求修改邮箱（漏洞版本）
if (isset($_GET['action']) && $_GET['action'] === 'update_email') {
    $new_email = $_GET['email'] ?? '';
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['csrf_demo_users'][$username]['email'] = $new_email;
        $success = '邮箱修改成功（漏洞版本 - 无CSRF防护）';
    }
}

// 处理GET请求修改邮箱（安全版本）
if (isset($_GET['action']) && $_GET['action'] === 'update_email_safe') {
    $token = $_GET['token'] ?? '';
    $new_email = $_GET['email'] ?? '';
    
    // 验证Token
    if (isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token']) {
        if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['csrf_demo_users'][$username]['email'] = $new_email;
            $success = '邮箱修改成功（安全版本 - CSRF Token验证通过）';
        }
    } else {
        $error = 'CSRF Token验证失败，请求被拦截！';
    }
}

// 生成CSRF Token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$user_info = $_SESSION['csrf_demo_users'][$username];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GET型CSRF攻击演示</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #f5576c;
            font-size: 1.8em;
            margin-bottom: 10px;
        }
        .header p { color: #666; }
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
        .nav-links { display: flex; gap: 10px; }
        .nav-links a {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #f5576c;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 0.9em;
        }
        .nav-links a:hover, .nav-links a.active {
            background: #f5576c;
            color: white;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-primary { background: #f5576c; color: white; }
        .btn-success { background: #67c23a; color: white; }
        .btn-danger { background: #e6a23c; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
        .content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .section { margin-bottom: 30px; }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f5576c;
        }
        .section p { color: #666; line-height: 1.8; margin-bottom: 15px; }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #f0f9ff; color: #67c23a; border: 1px solid #c2e7b0; }
        .alert-danger { background: #fef0f0; color: #f56c6c; border: 1px solid #fde2e2; }
        .alert-info { background: #ecf5ff; color: #409eff; border: 1px solid #d9ecff; }
        .alert-warning { background: #fdf6ec; color: #e6a23c; border: 1px solid #faecd8; }
        
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .demo-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .demo-box h3 {
            color: #f5576c;
            margin-bottom: 15px;
        }
        
        .user-info {
            background: white;
            border: 3px solid #f5576c;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.2);
        }
        .user-info h3 {
            color: #c62828;
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #fce4ec;
            padding-bottom: 10px;
        }
        .user-info p {
            margin: 10px 0;
            color: #333;
            font-size: 1.05em;
            line-height: 1.8;
        }
        .user-info p strong {
            color: #b71c1c;
            font-weight: 600;
        }
        
        .attack-link {
            display: inline-block;
            background: #f5576c;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin: 10px 5px 10px 0;
            transition: all 0.3s;
        }
        .attack-link:hover {
            background: #f093fb;
            transform: translateY(-2px);
        }
        
        .flow-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #f5576c;
        }
        .step-number {
            width: 35px;
            height: 35px;
            background: #f5576c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-content h4 { color: #333; margin-bottom: 5px; }
        .step-content p { color: #666; font-size: 0.9em; margin: 0; }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
        }
        .tab {
            padding: 12px 20px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95em;
            color: #666;
        }
        .tab:hover { background: #f5576c; color: white; }
        .tab.active { background: #f5576c; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📥 GET型CSRF攻击演示</h1>
            <p>通过URL参数传递攻击数据，诱导用户点击恶意链接</p>
        </div>
        
        <div class="nav-bar">
            <div class="nav-links">
                <a href="index.php">🏠 首页</a>
                <a href="csrf_get.php" class="active">GET型攻击</a>
                <a href="csrf_post.php">POST型攻击</a>
                <a href="csrf_token_weak.php">Token缺陷</a>
                <a href="csrf_defense.php">防护方案</a>
            </div>
            <div>
                <a href="index.php" class="btn btn-primary">返回首页</a>
                <a href="../index.php" class="btn btn-danger">🏠 返回主页</a>
            </div>
        </div>
        
        <div class="content">
            <?php if (isset($success)): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- 用户信息 -->
            <div class="user-info">
                <h3>👤 当前用户信息</h3>
                <p><strong>用户名：</strong><?php echo htmlspecialchars($username); ?></p>
                <p><strong>邮箱：</strong><?php echo htmlspecialchars($user_info['email']); ?></p>
                <p><strong>昵称：</strong><?php echo htmlspecialchars($user_info['nickname']); ?></p>
            </div>
            
            <!-- 攻击原理 -->
            <div class="section">
                <h2>📚 攻击原理</h2>
                <p>
                    GET型CSRF攻击是最简单直接的攻击方式。攻击者构造包含恶意参数的URL，
                    诱导用户点击或在页面中嵌入自动触发的元素（如img标签）。
                </p>
                
                <div class="code-block">
                    <strong style="color: #f5576c;">// 漏洞代码示例</strong><br>
                    // 直接使用GET参数修改数据，无任何验证<br>
                    if (isset($_GET['action']) && $_GET['action'] === 'update_email') {<br>
                    &nbsp;&nbsp;$email = $_GET['email'];<br>
                    &nbsp;&nbsp;updateUserEmail($email); // 直接更新，无CSRF防护<br>
                    }
                </div>
                
                <div class="alert alert-warning">
                    <strong>⚠️ 危险性：</strong>GET请求容易被记录（浏览器历史、服务器日志），且可通过多种方式自动触发。
                </div>
            </div>
            
            <!-- 攻击流程 -->
            <div class="section">
                <h2>🔄 攻击流程</h2>
                <div class="flow-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>用户登录目标网站</h4>
                        <p>用户正常登录，浏览器保存登录凭证（Cookie）</p>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>攻击者构造恶意URL</h4>
                        <p>构造包含攻击参数的URL：http://example.com/update?email=attacker@evil.com</p>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>诱导用户点击</h4>
                        <p>通过邮件、社交媒体等方式诱导用户点击恶意链接</p>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>自动发送请求</h4>
                        <p>用户点击后，浏览器自动携带Cookie发送GET请求</p>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h4>服务器执行操作</h4>
                        <p>服务器验证用户身份成功，执行修改邮箱操作</p>
                    </div>
                </div>
            </div>
            
            <!-- 演示区域 -->
            <div class="section">
                <h2>🎮 演示操作</h2>
                
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('vulnerable')">⚠️ 漏洞版本</button>
                    <button class="tab" onclick="switchTab('secure')">✅ 安全版本</button>
                    <button class="tab" onclick="switchTab('attack')">🔥 攻击演示</button>
                </div>
                
                <!-- 漏洞版本 -->
                <div id="vulnerable" class="tab-content active">
                    <div class="demo-box">
                        <h3>⚠️ 漏洞版本 - 无CSRF防护</h3>
                        <p>以下链接直接修改邮箱，无任何防护措施：</p>
                        
                        <div class="code-block">
                            <strong style="color: #f5576c;">// 漏洞代码</strong><br>
                            // 直接接受GET参数，无Token验证<br>
                            $email = $_GET['email'];<br>
                            $_SESSION['user']['email'] = $email;
                        </div>
                        
                        <p><strong>点击以下链接测试：</strong></p>
                        <a href="?action=update_email&email=attacker@evil.com" class="attack-link">
                            🔗 修改邮箱为 attacker@evil.com
                        </a>
                        <a href="?action=update_email&email=hacked@csrf.com" class="attack-link">
                            🔗 修改邮箱为 hacked@csrf.com
                        </a>
                        
                        <div class="alert alert-info" style="margin-top: 15px;">
                            <strong>💡 提示：</strong>点击链接后，邮箱将被修改，刷新页面查看结果。
                        </div>
                    </div>
                </div>
                
                <!-- 安全版本 -->
                <div id="secure" class="tab-content">
                    <div class="demo-box">
                        <h3>✅ 安全版本 - 有CSRF Token防护</h3>
                        <p>使用CSRF Token验证，防止恶意请求：</p>
                        
                        <div class="code-block">
                            <strong style="color: #67c23a;">// 安全代码</strong><br>
                            // 生成Token<br>
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));<br><br>
                            // 验证Token<br>
                            if ($_GET['token'] !== $_SESSION['csrf_token']) {<br>
                            &nbsp;&nbsp;die('CSRF验证失败');<br>
                            }
                        </div>
                        
                        <p><strong>当前CSRF Token：</strong></p>
                        <div class="code-block"><?php echo $_SESSION['csrf_token']; ?></div>
                        
                        <p><strong>使用Token的安全链接：</strong></p>
                        <a href="?action=update_email_safe&email=safe@example.com&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-success">
                            ✅ 安全修改邮箱为 safe@example.com
                        </a>
                        
                        <p style="margin-top: 15px;"><strong>无Token的恶意链接：</strong></p>
                        <a href="?action=update_email_safe&email=attacker@evil.com&token=invalid_token" class="attack-link">
                            🔗 尝试攻击（Token无效）
                        </a>
                        
                        <div class="alert alert-success" style="margin-top: 15px;">
                            <strong>✅ 防护效果：</strong>无Token或Token错误的请求将被拦截，邮箱不会被修改。
                        </div>
                    </div>
                </div>
                
                <!-- 攻击演示 -->
                <div id="attack" class="tab-content">
                    <div class="demo-box">
                        <h3>🔥 攻击演示页面</h3>
                        <p>点击下方按钮打开攻击页面，体验真实的CSRF攻击：</p>
                        
                        <a href="attack_get.html" target="_blank" class="btn btn-danger" style="margin-right: 10px;">
                            🔥 打开攻击页面
                        </a>
                        
                        <div class="alert alert-warning" style="margin-top: 20px;">
                            <strong>⚠️ 攻击页面说明：</strong><br>
                            攻击页面伪装成"领取优惠券"活动，实际会自动修改您的邮箱。<br>
                            请在新标签页打开，然后返回本页面查看邮箱变化。
                        </div>
                        
                        <div class="code-block">
                            <strong style="color: #f5576c;">// 攻击页面代码</strong><br>
                            &lt;!-- 伪装成优惠券领取页面 --&gt;<br>
                            &lt;img src="http://example.com/csrf_get.php?action=update_email&email=attacker@evil.com" style="display:none"&gt;<br><br>
                            &lt;!-- 用户看到的是 --&gt;<br>
                            &lt;h1&gt;恭喜您获得100元优惠券！&lt;/h1&gt;<br>
                            &lt;p&gt;优惠券已自动发放到您的账户&lt;/p&gt;
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 防护建议 -->
            <div class="section">
                <h2>🛡️ 防护建议</h2>
                <div class="alert alert-success">
                    <strong>✅ 防护措施：</strong>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li>使用CSRF Token验证</li>
                        <li>避免使用GET请求执行敏感操作</li>
                        <li>验证HTTP Referer头</li>
                        <li>设置Cookie的SameSite属性</li>
                        <li>关键操作要求二次验证</li>
                    </ul>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="csrf_post.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1em;">
                    下一课：POST型CSRF攻击 →
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tabId) {
            // 隐藏所有tab内容
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            // 移除所有tab的active状态
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            // 显示选中的tab
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>