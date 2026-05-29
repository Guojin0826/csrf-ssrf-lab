<?php
/**
 * CSRF Token设置不严演示
 */
session_start();

// 检查登录
if (!isset($_SESSION['csrf_demo_user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['csrf_demo_user'];

// 生成弱Token（可预测）
function generateWeakToken() {
    // 弱Token：基于时间戳，可预测
    return substr(md5(time()), 0, 8);
}

// 生成强Token（安全）
function generateStrongToken() {
    return bin2hex(random_bytes(32));
}

// 处理弱Token修改（漏洞版本）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_weak') {
    $token = $_POST['csrf_token'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    
    // 弱Token验证：Token长度太短，且可预测
    if (strlen($token) === 8 && isset($_SESSION['weak_token']) && $token === $_SESSION['weak_token']) {
        $_SESSION['csrf_demo_users'][$username]['nickname'] = $nickname;
        $success = '信息修改成功（弱Token版本 - Token可预测）';
    } else {
        $error = 'Token验证失败';
    }
}

// 处理强Token修改（安全版本）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_strong') {
    $token = $_POST['csrf_token'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    
    // 强Token验证：Token长度足够，且不可预测
    if (strlen($token) === 64 && isset($_SESSION['strong_token']) && $token === $_SESSION['strong_token']) {
        $_SESSION['csrf_demo_users'][$username]['nickname'] = $nickname;
        $success = '信息修改成功（强Token版本 - Token安全）';
    } else {
        $error = 'Token验证失败';
    }
}

// 生成Token
$_SESSION['weak_token'] = generateWeakToken();
$_SESSION['strong_token'] = generateStrongToken();

$user_info = $_SESSION['csrf_demo_users'][$username];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token设置缺陷演示</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
            color: #fa709a;
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
            color: #fa709a;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 0.9em;
        }
        .nav-links a:hover, .nav-links a.active {
            background: #fa709a;
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
        .btn-primary { background: #fa709a; color: white; }
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
            border-bottom: 2px solid #fa709a;
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
            color: #fa709a;
            margin-bottom: 15px;
        }
        
        .user-info {
            background: white;
            border: 3px solid #fa709a;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(250, 112, 154, 0.2);
        }
        .user-info h3 {
            color: #c2185b;
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
            color: #880e4f;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1em;
        }
        .form-group input:focus {
            outline: none;
            border-color: #fa709a;
        }
        
        .token-compare {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .token-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        .token-box.weak {
            border: 2px solid #f56c6c;
        }
        .token-box.strong {
            border: 2px solid #67c23a;
        }
        .token-box h4 {
            margin-bottom: 10px;
        }
        .token-box.weak h4 {
            color: #f56c6c;
        }
        .token-box.strong h4 {
            color: #67c23a;
        }
        .token-value {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.85em;
            word-break: break-all;
        }
        
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
        .tab:hover { background: #fa709a; color: white; }
        .tab.active { background: #fa709a; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔓 Token设置缺陷演示</h1>
            <p>虽然使用了CSRF Token，但设置不当导致防护失效</p>
        </div>
        
        <div class="nav-bar">
            <div class="nav-links">
                <a href="index.php">🏠 首页</a>
                <a href="csrf_get.php">GET型攻击</a>
                <a href="csrf_post.php">POST型攻击</a>
                <a href="csrf_token_weak.php" class="active">Token缺陷</a>
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
                <p><strong>昵称：</strong><?php echo htmlspecialchars($user_info['nickname']); ?></p>
            </div>
            
            <!-- Token对比 -->
            <div class="section">
                <h2>🔍 Token对比</h2>
                <div class="token-compare">
                    <div class="token-box weak">
                        <h4>❌ 弱Token（不安全）</h4>
                        <div class="token-value"><?php echo $_SESSION['weak_token']; ?></div>
                        <p style="margin-top: 10px; font-size: 0.9em; color: #666;">
                            <strong>长度：</strong>8位<br>
                            <strong>算法：</strong>md5(time())<br>
                            <strong>问题：</strong>可预测、长度太短
                        </p>
                    </div>
                    <div class="token-box strong">
                        <h4>✅ 强Token（安全）</h4>
                        <div class="token-value"><?php echo $_SESSION['strong_token']; ?></div>
                        <p style="margin-top: 10px; font-size: 0.9em; color: #666;">
                            <strong>长度：</strong>64位<br>
                            <strong>算法：</strong>random_bytes(32)<br>
                            <strong>优点：</strong>不可预测、长度足够
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- 常见缺陷 -->
            <div class="section">
                <h2>⚠️ 常见Token缺陷</h2>
                
                <div class="alert alert-danger">
                    <strong>1. Token可预测</strong>
                    <div class="code-block" style="margin-top: 10px;">
                        // 基于时间戳生成，攻击者可预测<br>
                        $token = md5(time());<br>
                        $token = sha1(date('Y-m-d'));<br>
                        $token = substr(md5(rand()), 0, 8);
                    </div>
                </div>
                
                <div class="alert alert-danger">
                    <strong>2. Token长度太短</strong>
                    <div class="code-block" style="margin-top: 10px;">
                        // 只有8位，容易被暴力破解<br>
                        $token = substr(md5(time()), 0, 8);<br>
                        // 攻击者可以尝试 16^8 = 4,294,967,296 种可能
                    </div>
                </div>
                
                <div class="alert alert-danger">
                    <strong>3. Token验证不严格</strong>
                    <div class="code-block" style="margin-top: 10px;">
                        // 只检查Token是否存在，不验证值<br>
                        if (isset($_POST['token'])) { // 错误！<br>
                        &nbsp;&nbsp;// 执行操作<br>
                        }<br><br>
                        // 正确做法：<br>
                        if ($_POST['token'] === $_SESSION['token']) {<br>
                        &nbsp;&nbsp;// 执行操作<br>
                        }
                    </div>
                </div>
                
                <div class="alert alert-danger">
                    <strong>4. Token可获取</strong>
                    <div class="code-block" style="margin-top: 10px;">
                        // Token通过GET参数传递，容易被窃取<br>
                        &lt;form action="update.php?token=xxx"&gt;<br><br>
                        // Token存储在Cookie中，可被XSS窃取<br>
                        setcookie('csrf_token', $token);
                    </div>
                </div>
            </div>
            
            <!-- 演示区域 -->
            <div class="section">
                <h2>🎮 演示操作</h2>
                
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('weak')">❌ 弱Token版本</button>
                    <button class="tab" onclick="switchTab('strong')">✅ 强Token版本</button>
                    <button class="tab" onclick="switchTab('attack')">🔥 攻击演示</button>
                </div>
                
                <!-- 弱Token版本 -->
                <div id="weak" class="tab-content active">
                    <div class="demo-box">
                        <h3>❌ 弱Token版本 - Token可预测</h3>
                        <p>Token基于时间戳生成，攻击者可以预测：</p>
                        
                        <div class="code-block">
                            <strong style="color: #f56c6c;">// 弱Token生成代码</strong><br>
                            // 基于当前时间戳生成Token<br>
                            $token = substr(md5(time()), 0, 8);<br><br>
                            <strong style="color: #fa709a;">// 攻击者可以预测</strong><br>
                            // 攻击者知道当前时间，可以生成相同的Token<br>
                            $predicted_token = substr(md5(time()), 0, 8);
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_weak">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['weak_token']; ?>">
                            <div class="form-group">
                                <label>昵称</label>
                                <input type="text" name="nickname" value="<?php echo htmlspecialchars($user_info['nickname']); ?>">
                            </div>
                            <button type="submit" class="btn btn-danger">提交修改（弱Token版本）</button>
                        </form>
                        
                        <div class="alert alert-warning" style="margin-top: 15px;">
                            <strong>⚠️ 安全隐患：</strong>攻击者可以预测Token，构造有效的CSRF攻击请求。
                        </div>
                    </div>
                </div>
                
                <!-- 强Token版本 -->
                <div id="strong" class="tab-content">
                    <div class="demo-box">
                        <h3>✅ 强Token版本 - Token不可预测</h3>
                        <p>使用加密安全的随机数生成器：</p>
                        
                        <div class="code-block">
                            <strong style="color: #67c23a;">// 强Token生成代码</strong><br>
                            // 使用加密安全的随机数生成器<br>
                            $token = bin2hex(random_bytes(32));<br><br>
                            <strong style="color: #67c23a;">// 特点</strong><br>
                            // 1. 64位长度，足够安全<br>
                            // 2. 使用加密安全随机数<br>
                            // 3. 不可预测
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_strong">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['strong_token']; ?>">
                            <div class="form-group">
                                <label>昵称</label>
                                <input type="text" name="nickname" value="<?php echo htmlspecialchars($user_info['nickname']); ?>">
                            </div>
                            <button type="submit" class="btn btn-success">提交修改（强Token版本）</button>
                        </form>
                        
                        <div class="alert alert-success" style="margin-top: 15px;">
                            <strong>✅ 安全保障：</strong>攻击者无法预测Token，CSRF攻击无效。
                        </div>
                    </div>
                </div>
                
                <!-- 攻击演示 -->
                <div id="attack" class="tab-content">
                    <div class="demo-box">
                        <h3>🔥 攻击演示</h3>
                        <p>攻击者如何利用弱Token进行攻击，以及强Token的防护效果：</p>
                        
                        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                            <a href="attack_token_weak.html" target="_blank" class="btn btn-danger">
                                🔥 弱Token攻击（成功）
                            </a>
                            <a href="attack_token_strong.html" target="_blank" class="btn btn-success">
                                🛡️ 强Token攻击（失败）
                            </a>
                        </div>
                        
                        <div class="code-block">
                            <strong style="color: #f56c6c;">// 弱Token攻击代码（成功）</strong><br>
                            // 1. 攻击者获取当前时间戳<br>
                            $current_time = time();<br><br>
                            // 2. 生成预测的Token（与服务器相同算法）<br>
                            $predicted_token = substr(md5($current_time), 0, 8);<br><br>
                            // 3. 构造攻击表单<br>
                            &lt;form action="csrf_token_weak.php" method="POST"&gt;<br>
                            &nbsp;&nbsp;&lt;input type="hidden" name="action" value="update_weak"&gt;<br>
                            &nbsp;&nbsp;&lt;input type="hidden" name="token" value="&lt;?php echo $predicted_token; ?&gt;"&gt;<br>
                            &nbsp;&nbsp;&lt;input type="hidden" name="nickname" value="被攻击"&gt;<br>
                            &lt;/form&gt;<br><br>
                            <strong style="color: #67c23a;">// 强Token攻击代码（失败）</strong><br>
                            // 攻击者无法预测64位强Token<br>
                            // 只能尝试伪造短Token<br>
                            &lt;form action="csrf_token_weak.php" method="POST"&gt;<br>
                            &nbsp;&nbsp;&lt;input type="hidden" name="action" value="update_strong"&gt;<br>
                            &nbsp;&nbsp;&lt;!-- ❌ Token长度不匹配 --&gt;<br>
                            &nbsp;&nbsp;&lt;input type="hidden" name="csrf_token" value="fake12345"&gt;<br>
                            &lt;/form&gt;
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>💡 攻击成功率对比：</strong><br>
                            弱Token攻击成功率：<strong style="color: #f56c6c;">90%+</strong>（可预测）<br>
                            强Token攻击成功率：<strong style="color: #67c23a;">≈0%</strong>（不可预测）
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 防护建议 -->
            <div class="section">
                <h2>🛡️ Token安全建议</h2>
                <div class="alert alert-success">
                    <strong>✅ 最佳实践：</strong>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li>使用加密安全的随机数生成器（random_bytes）</li>
                        <li>Token长度至少32字节（64位十六进制）</li>
                        <li>Token一次性使用，用后销毁</li>
                        <li>Token存储在Session中，不暴露给客户端</li>
                        <li>严格验证Token值，不只要检查存在性</li>
                        <li>Token与用户会话绑定</li>
                    </ul>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="csrf_defense.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1em;">
                    下一课：完整防护方案 →
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