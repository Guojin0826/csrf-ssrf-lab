<?php
/**
 * CSRF防护方案完整指南
 */
session_start();

// 检查登录
if (!isset($_SESSION['csrf_demo_user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['csrf_demo_user'];
$user_info = $_SESSION['csrf_demo_users'][$username];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF防护方案</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            color: #67c23a;
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
            color: #67c23a;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 0.9em;
        }
        .nav-links a:hover, .nav-links a.active {
            background: #67c23a;
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
        .btn-primary { background: #67c23a; color: white; }
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
            border-bottom: 2px solid #67c23a;
        }
        .section p { color: #666; line-height: 1.8; margin-bottom: 15px; }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #f0f9ff; color: #67c23a; border: 1px solid #c2e7b0; }
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
        
        .defense-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #67c23a;
        }
        .defense-card h3 {
            color: #67c23a;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .defense-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .comparison-table th {
            background: #67c23a;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .comparison-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .comparison-table tr:hover {
            background: #f8f9fa;
        }
        .comparison-table .good {
            color: #67c23a;
        }
        .comparison-table .bad {
            color: #f56c6c;
        }
        
        .checklist {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .checklist h3 {
            color: #67c23a;
            margin-bottom: 15px;
        }
        .checklist ul {
            list-style: none;
        }
        .checklist li {
            padding: 10px;
            margin-bottom: 10px;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checklist li::before {
            content: '✅';
            color: #67c23a;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ CSRF防护方案完整指南</h1>
            <p>学习如何有效防御CSRF攻击，保护Web应用安全</p>
        </div>
        
        <div class="nav-bar">
            <div class="nav-links">
                <a href="index.php">🏠 首页</a>
                <a href="csrf_get.php">GET型攻击</a>
                <a href="csrf_post.php">POST型攻击</a>
                <a href="csrf_token_weak.php">Token缺陷</a>
                <a href="csrf_defense.php" class="active">防护方案</a>
            </div>
            <div>
                <a href="index.php" class="btn btn-primary">返回首页</a>
                <a href="../index.php" class="btn btn-primary">🏠 返回主页</a>
            </div>
        </div>
        
        <div class="content">
            <!-- 防护概述 -->
            <div class="section">
                <h2>🎯 防护策略概述</h2>
                <p>
                    CSRF攻击的核心在于利用用户的登录状态，因此防护的关键是<strong>验证请求的真实来源</strong>。
                    以下是业界公认的有效防护措施。
                </p>
                
                <div class="alert alert-info">
                    <strong>💡 核心思想：</strong>确保请求是由用户主动发起，而非第三方恶意构造。
                </div>
            </div>
            
            <!-- 防护方法对比 -->
            <div class="section">
                <h2>📊 防护方法对比</h2>
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>防护方法</th>
                            <th>安全性</th>
                            <th>适用场景</th>
                            <th>优缺点</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>CSRF Token</strong></td>
                            <td class="good">⭐⭐⭐⭐⭐</td>
                            <td>所有POST请求</td>
                            <td class="good">最有效，推荐使用</td>
                        </tr>
                        <tr>
                            <td><strong>Referer验证</strong></td>
                            <td class="good">⭐⭐⭐⭐</td>
                            <td>辅助防护</td>
                            <td class="bad">Referer可能被篡改</td>
                        </tr>
                        <tr>
                            <td><strong>SameSite Cookie</strong></td>
                            <td class="good">⭐⭐⭐⭐⭐</td>
                            <td>现代浏览器</td>
                            <td class="good">浏览器原生支持</td>
                        </tr>
                        <tr>
                            <td><strong>二次验证</strong></td>
                            <td class="good">⭐⭐⭐⭐⭐</td>
                            <td>关键操作</td>
                            <td class="good">最安全，但用户体验差</td>
                        </tr>
                        <tr>
                            <td><strong>避免GET操作</strong></td>
                            <td class="good">⭐⭐⭐</td>
                            <td>所有敏感操作</td>
                            <td class="good">基础防护，必要但不够</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- 方法1：CSRF Token -->
            <div class="section">
                <h2>1️⃣ CSRF Token（推荐）</h2>
                
                <div class="defense-card">
                    <h3>✅ 最有效的防护方法</h3>
                    <p>
                        为每个表单生成唯一的、不可预测的Token，服务器验证Token的有效性。
                        Token应存储在Session中，通过表单传递。
                    </p>
                    
                    <div class="code-block">
                        <strong style="color: #67c23a;">// 生成CSRF Token</strong><br>
                        // 使用加密安全的随机数生成器<br>
                        function generateCSRFToken() {<br>
                        &nbsp;&nbsp;return bin2hex(random_bytes(32)); // 64位十六进制<br>
                        }<br><br>
                        // 存储到Session<br>
                        $_SESSION['csrf_token'] = generateCSRFToken();<br><br>
                        <strong style="color: #67c23a;">// 表单中嵌入Token</strong><br>
                        &lt;input type="hidden" name="csrf_token" value="&lt;?php echo $_SESSION['csrf_token']; ?&gt;"&gt;<br><br>
                        <strong style="color: #67c23a;">// 验证Token</strong><br>
                        function verifyCSRFToken($token) {<br>
                        &nbsp;&nbsp;if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;throw new Exception('CSRF验证失败');<br>
                        &nbsp;&nbsp;}<br>
                        &nbsp;&nbsp;// 验证成功后销毁Token（一次性使用）<br>
                        &nbsp;&nbsp;unset($_SESSION['csrf_token']);<br>
                        }
                    </div>
                    
                    <div class="alert alert-success">
                        <strong>✅ 优点：</strong>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>最有效的防护方法</li>
                            <li>攻击者无法获取Token</li>
                            <li>兼容性好，适用于所有浏览器</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 方法2：Referer验证 -->
            <div class="section">
                <h2>2️⃣ Referer验证</h2>
                
                <div class="defense-card">
                    <h3>✅ 辅助防护方法</h3>
                    <p>
                        检查HTTP请求头中的Referer字段，确保请求来源于合法页面。
                        注意：Referer可能被篡改或用户禁用。
                    </p>
                    
                    <div class="code-block">
                        <strong style="color: #67c23a;">// 验证Referer</strong><br>
                        function verifyReferer() {<br>
                        &nbsp;&nbsp;$referer = $_SERVER['HTTP_REFERER'] ?? '';<br>
                        &nbsp;&nbsp;$allowed_domains = ['example.com', 'www.example.com'];<br><br>
                        &nbsp;&nbsp;// 检查Referer是否来自合法域名<br>
                        &nbsp;&nbsp;$referer_host = parse_url($referer, PHP_URL_HOST);<br>
                        &nbsp;&nbsp;if (!in_array($referer_host, $allowed_domains)) {<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;throw new Exception('非法请求来源');<br>
                        &nbsp;&nbsp;}<br>
                        }
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>⚠️ 注意事项：</strong>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>Referer可能被用户禁用</li>
                            <li>某些浏览器不发送Referer</li>
                            <li>HTTPS到HTTP不发送Referer</li>
                            <li>建议作为辅助防护，不单独使用</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 方法3：SameSite Cookie -->
            <div class="section">
                <h2>3️⃣ SameSite Cookie属性</h2>
                
                <div class="defense-card">
                    <h3>✅ 浏览器原生防护</h3>
                    <p>
                        设置Cookie的SameSite属性，限制第三方Cookie的发送。
                        现代浏览器支持，是最简单的防护方法。
                    </p>
                    
                    <div class="code-block">
                        <strong style="color: #67c23a;">// 设置SameSite属性</strong><br>
                        // PHP 7.3+<br>
                        setcookie('session_id', $value, [<br>
                        &nbsp;&nbsp;'httponly' => true,<br>
                        &nbsp;&nbsp;'samesite' => 'Strict', // 或 'Lax'<br>
                        &nbsp;&nbsp;'secure' => true // HTTPS必须<br>
                        ]);<br><br>
                        <strong style="color: #67c23a;">// SameSite属性说明</strong><br>
                        // Strict：完全禁止第三方Cookie<br>
                        // Lax：允许安全的第三方请求（GET链接）<br>
                        // None：允许所有第三方请求（需要Secure）
                    </div>
                    
                    <div class="alert alert-success">
                        <strong>✅ 优点：</strong>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>浏览器原生支持，无需额外代码</li>
                            <li>配置简单</li>
                            <li>现代浏览器广泛支持</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 方法4：二次验证 -->
            <div class="section">
                <h2>4️⃣ 二次验证</h2>
                
                <div class="defense-card">
                    <h3>✅ 关键操作防护</h3>
                    <p>
                        对关键操作（如转账、修改密码）要求二次验证，
                        如输入密码、验证码、短信验证等。
                    </p>
                    
                    <div class="code-block">
                        <strong style="color: #67c23a;">// 修改密码时要求输入旧密码</strong><br>
                        function changePassword($old_password, $new_password) {<br>
                        &nbsp;&nbsp;// 验证旧密码<br>
                        &nbsp;&nbsp;if (!password_verify($old_password, $_SESSION['user']['password'])) {<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;throw new Exception('密码错误');<br>
                        &nbsp;&nbsp;}<br>
                        &nbsp;&nbsp;// 更新密码<br>
                        &nbsp;&nbsp;updatePassword($new_password);<br>
                        }<br><br>
                        <strong style="color: #67c23a;">// 转账时要求短信验证</strong><br>
                        function transferMoney($amount, $target, $sms_code) {<br>
                        &nbsp;&nbsp;// 验证短信验证码<br>
                        &nbsp;&nbsp;if ($sms_code !== $_SESSION['sms_code']) {<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;throw new Exception('验证码错误');<br>
                        &nbsp;&nbsp;}<br>
                        &nbsp;&nbsp;// 执行转账<br>
                        &nbsp;&nbsp;executeTransfer($amount, $target);<br>
                        }
                    </div>
                    
                    <div class="alert alert-success">
                        <strong>✅ 优点：</strong>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>最安全的防护方法</li>
                            <li>即使CSRF成功，也需要用户主动输入</li>
                            <li>适用于关键敏感操作</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 最佳实践清单 -->
            <div class="section">
                <h2>📋 最佳实践清单</h2>
                
                <div class="checklist">
                    <h3>✅ CSRF防护检查清单</h3>
                    <ul>
                        <li>所有POST请求使用CSRF Token验证</li>
                        <li>Token使用加密安全的随机数生成器</li>
                        <li>Token长度至少32字节（64位十六进制）</li>
                        <li>Token一次性使用，验证后销毁</li>
                        <li>Token存储在Session中，不暴露给客户端</li>
                        <li>设置Cookie的SameSite属性为Strict或Lax</li>
                        <li>关键操作要求二次验证（密码、验证码）</li>
                        <li>避免使用GET请求执行敏感操作</li>
                        <li>验证HTTP Referer作为辅助防护</li>
                        <li>定期审查和更新防护策略</li>
                    </ul>
                </div>
            </div>
            
            <!-- 完整示例 -->
            <div class="section">
                <h2>💡 完整防护示例</h2>
                
                <div class="code-block">
                    <strong style="color: #67c23a;">// 完整的CSRF防护类</strong><br>
                    class CSRFProtection {<br>
                    &nbsp;&nbsp;// 生成Token<br>
                    &nbsp;&nbsp;public static function generateToken() {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;if (!isset($_SESSION['csrf_token'])) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$_SESSION['csrf_token'] = bin2hex(random_bytes(32));<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;return $_SESSION['csrf_token'];<br>
                    &nbsp;&nbsp;}<br><br>
                    &nbsp;&nbsp;// 验证Token<br>
                    &nbsp;&nbsp;public static function verifyToken($token) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return false;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;unset($_SESSION['csrf_token']); // 一次性使用<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;return true;<br>
                    &nbsp;&nbsp;}<br><br>
                    &nbsp;&nbsp;// 验证Referer<br>
                    &nbsp;&nbsp;public static function verifyReferer() {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;$referer = $_SERVER['HTTP_REFERER'] ?? '';<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;$host = parse_url($referer, PHP_URL_HOST);<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;return $host === $_SERVER['HTTP_HOST'];<br>
                    &nbsp;&nbsp;}<br><br>
                    &nbsp;&nbsp;// 综合验证<br>
                    &nbsp;&nbsp;public static function verify($token) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;// Token验证（必须）<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;if (!self::verifyToken($token)) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;throw new Exception('CSRF验证失败');<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;// Referer验证（辅助）<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;if (!self::verifyReferer()) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;throw new Exception('非法请求来源');<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                    &nbsp;&nbsp;}<br>
                    }
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1em;">
                    🎓 返回首页重新学习
                </a>
            </div>
        </div>
    </div>
</body>
</html>