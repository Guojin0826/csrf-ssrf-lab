<?php
/**
 * SSRF基础演示 - 无防护的URL请求
 */
session_start();

// 检查登录
if (!isset($_SESSION['ssrf_demo_user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['ssrf_demo_user'];

// 初始化结果变量
$result = '';
$error = '';
$success = '';
$current_mode = isset($_GET['mode']) ? $_GET['mode'] : 'vulnerable';

// 安全的URL验证函数
function isValidUrl($url) {
    // 白名单域名
    $allowedDomains = ['example.com', 'httpbin.org', 'picsum.photos'];
    
    // 解析URL
    $parsedUrl = parse_url($url);
    
    if (!$parsedUrl || !isset($parsedUrl['host'])) {
        return false;
    }
    
    $host = $parsedUrl['host'];
    
    // 检查协议
    $scheme = $parsedUrl['scheme'] ?? '';
    if (!in_array($scheme, ['http', 'https'])) {
        return false;
    }
    
    // 检查是否是内网IP
    $ip = gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    
    // 检查是否是本地回环地址
    if (in_array($ip, ['127.0.0.1', 'localhost', '0.0.0.0'])) {
        return false;
    }
    
    // 检查是否在白名单中
    foreach ($allowedDomains as $domain) {
        if (strpos($host, $domain) !== false) {
            return true;
        }
    }
    
    return false;
}

// 处理URL请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    
    if (empty($url)) {
        $error = '请输入URL地址';
    } else {
        if ($current_mode === 'vulnerable') {
            // ⚠️ 漏洞版本：直接使用用户输入的URL，没有任何验证
            try {
                // 使用file_get_contents替代curl
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'follow_location' => true,
                        'ignore_errors' => true
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                
                if ($response === false) {
                    $error = '请求失败: 无法访问该URL';
                } else {
                    $result = $response;
                    $success = "请求成功！";
                    
                    // 记录攻击日志
                    if (strpos($url, '127.0.0.1') !== false || 
                        strpos($url, 'localhost') !== false ||
                        strpos($url, 'file://') !== false ||
                        strpos($url, 'mock_data') !== false) {
                        $success .= " - ⚠️ SSRF攻击成功！";
                    }
                }
            } catch (Exception $e) {
                $error = '请求异常: ' . $e->getMessage();
            }
        } else {
            // ✅ 安全版本：验证URL
            if (!isValidUrl($url)) {
                $error = 'URL验证失败：不允许访问该地址（内网IP、本地文件或不在白名单中）';
            } else {
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 10,
                            'follow_location' => true,
                            'ignore_errors' => true
                        ],
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ]
                    ]);
                    
                    $response = @file_get_contents($url, false, $context);
                    
                    if ($response === false) {
                        $error = '请求失败: 无法访问该URL';
                    } else {
                        $result = $response;
                        $success = "请求成功！ - ✅ URL验证通过";
                    }
                } catch (Exception $e) {
                    $error = '请求异常: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSRF基础演示 - 无防护的URL请求</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
            font-size: 1.3em;
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
        .nav-links a:hover {
            background: #11998e;
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
        .btn-primary { background: #11998e; color: white; }
        .btn-success { background: #67c23a; color: white; }
        .btn-danger { background: #f56c6c; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
        /* Tab切换 */
        .tab-container {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
        }
        .tab-buttons {
            display: flex;
            gap: 10px;
        }
        .tab-btn {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        .tab-btn.active {
            background: #11998e;
            color: white;
            border-color: #11998e;
        }
        .tab-btn.vulnerable.active {
            background: #f56c6c;
            border-color: #f56c6c;
        }
        .tab-btn.safe.active {
            background: #67c23a;
            border-color: #67c23a;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        .section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        
        /* 演示框 */
        .demo-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .demo-box h3 {
            color: #11998e;
            margin-bottom: 15px;
        }
        
        /* 用户信息 */
        .user-info {
            background: white;
            border: 3px solid #11998e;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.2);
        }
        .user-info h3 {
            color: #0d7377;
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }
        .user-info p {
            margin: 10px 0;
            color: #333;
            font-size: 1.05em;
            line-height: 1.8;
        }
        .user-info p strong {
            color: #075e5e;
            font-weight: 600;
        }
        
        /* 表单 */
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
        
        /* 结果显示 */
        .result-box {
            background: #2d2d2d;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #f8f8f2;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        /* Payload列表 */
        .payload-list {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .payload-item {
            padding: 10px;
            margin: 8px 0;
            background: #f8f9fa;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }
        .payload-item:hover {
            background: #11998e;
            color: white;
            border-color: #11998e;
        }
        .payload-item code {
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
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
        
        /* 代码块 */
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            margin: 15px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>🎯 SSRF基础演示 - 无防护的URL请求</h1>
            <div class="nav-links">
                <a href="index.php">📚 返回首页</a>
                <a href="../index.php">🏠 返回主页</a>
            </div>
        </div>
        
        <!-- Tab切换 -->
        <div class="tab-container">
            <div class="tab-buttons">
                <a href="?mode=vulnerable" class="tab-btn vulnerable <?php echo $current_mode === 'vulnerable' ? 'active' : ''; ?>">
                    ⚠️ 漏洞版本
                </a>
                <a href="?mode=safe" class="tab-btn safe <?php echo $current_mode === 'safe' ? 'active' : ''; ?>">
                    ✅ 安全版本
                </a>
            </div>
        </div>
        
        <div class="content">
            <!-- 用户信息 -->
            <div class="user-info">
                <h3>👤 当前用户信息</h3>
                <p><strong>用户名：</strong><?php echo htmlspecialchars($username); ?></p>
                <p><strong>当前模式：</strong>
                    <?php if ($current_mode === 'vulnerable'): ?>
                        <span style="color: #f56c6c;">⚠️ 漏洞版本 - 无任何防护</span>
                    <?php else: ?>
                        <span style="color: #67c23a;">✅ 安全版本 - URL白名单验证</span>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- 攻击原理 -->
            <div class="section">
                <h2>📖 攻击原理</h2>
                <?php if ($current_mode === 'vulnerable'): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ 漏洞版本说明：</strong><br>
                    服务器直接使用用户输入的URL发起请求，没有任何验证和过滤。<br>
                    攻击者可以构造恶意URL访问内网服务、读取本地文件、获取云元数据等。
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                    <strong>✅ 安全版本说明：</strong><br>
                    服务器验证用户输入的URL，只允许访问白名单域名。<br>
                    拒绝访问内网IP、本地回环地址、危险协议等。
                </div>
                <?php endif; ?>
            </div>
            
            <!-- 演示区域 -->
            <div class="demo-box">
                <h3>🔧 URL请求演示</h3>
                <p>输入URL地址，服务器将发起请求并返回结果：</p>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>🌐 URL地址</label>
                        <input type="text" name="url" placeholder="http://example.com" 
                               value="<?php echo htmlspecialchars($_POST['url'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">发送请求</button>
                </form>
                
                <?php if ($result): ?>
                <h4 style="margin-top: 20px; color: #333;">📄 响应结果：</h4>
                <div class="result-box"><?php echo htmlspecialchars($result); ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Payload示例 -->
            <div class="section">
                <h2>🎯 攻击Payload示例</h2>
                <p>点击以下Payload快速测试（仅漏洞版本有效）：</p>
                
                <div class="payload-list">
                    <h4 style="color: #11998e; margin-bottom: 10px;">📂 访问本地文件</h4>
                    <div class="payload-item" onclick="fillUrl('mock_data/config.txt')">
                        <code>mock_data/config.txt</code> - 读取配置文件（包含数据库密码）
                    </div>
                    <div class="payload-item" onclick="fillUrl('mock_data/logs.txt')">
                        <code>mock_data/logs.txt</code> - 读取系统日志
                    </div>
                </div>
                
                <div class="payload-list">
                    <h4 style="color: #11998e; margin-bottom: 10px;">🔐 访问内网API</h4>
                    <div class="payload-item" onclick="fillUrl('mock_data/users.php')">
                        <code>mock_data/users.php</code> - 获取用户数据和API密钥
                    </div>
                    <div class="payload-item" onclick="fillUrl('mock_data/config.php')">
                        <code>mock_data/config.php</code> - 获取系统配置和密码
                    </div>
                    <div class="payload-item" onclick="fillUrl('mock_data/api.php?endpoint=secrets')">
                        <code>mock_data/api.php?endpoint=secrets</code> - 获取AWS、Stripe密钥
                    </div>
                </div>
                
                <div class="payload-list">
                    <h4 style="color: #11998e; margin-bottom: 10px;">☁️ 云元数据服务</h4>
                    <div class="payload-item" onclick="fillUrl('mock_data/metadata.php?type=iam')">
                        <code>mock_data/metadata.php?type=iam</code> - AWS IAM角色和凭证
                    </div>
                    <div class="payload-item" onclick="fillUrl('mock_data/metadata.php?type=security')">
                        <code>mock_data/metadata.php?type=security</code> - AWS临时安全凭证
                    </div>
                </div>
                
                <div class="payload-list">
                    <h4 style="color: #11998e; margin-bottom: 10px;">🔍 内网服务探测</h4>
                    <div class="payload-item" onclick="fillUrl('mock_data/redis.php?cmd=info')">
                        <code>mock_data/redis.php?cmd=info</code> - Redis服务信息
                    </div>
                    <div class="payload-item" onclick="fillUrl('mock_data/redis.php?cmd=get&key=secret:api_key')">
                        <code>mock_data/redis.php?cmd=get&key=secret:api_key</code> - Redis中的API密钥
                    </div>
                </div>
            </div>
            
            <!-- 防护说明 -->
            <div class="section">
                <h2>🛡️ 防护措施</h2>
                <div class="code-block">
                    <strong style="color: #67c23a;">// 安全版本防护代码</strong><br>
                    function isValidUrl($url) {<br>
                    &nbsp;&nbsp;// 1. 白名单域名验证<br>
                    &nbsp;&nbsp;$allowedDomains = ['example.com', 'httpbin.org'];<br><br>
                    &nbsp;&nbsp;// 2. 解析URL<br>
                    &nbsp;&nbsp;$parsedUrl = parse_url($url);<br><br>
                    &nbsp;&nbsp;// 3. 检查协议（只允许http/https）<br>
                    &nbsp;&nbsp;if (!in_array($parsedUrl['scheme'], ['http', 'https'])) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;return false;<br>
                    &nbsp;&nbsp;}<br><br>
                    &nbsp;&nbsp;// 4. 检查是否是内网IP<br>
                    &nbsp;&nbsp;$ip = gethostbyname($parsedUrl['host']);<br>
                    &nbsp;&nbsp;if (filter_var($ip, FILTER_VALIDATE_IP, <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;return false;<br>
                    &nbsp;&nbsp;}<br><br>
                    &nbsp;&nbsp;// 5. 检查白名单<br>
                    &nbsp;&nbsp;foreach ($allowedDomains as $domain) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;if (strpos($host, $domain) !== false) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return true;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                    &nbsp;&nbsp;}<br><br>
                    &nbsp;&nbsp;return false;<br>
                    }
                </div>
                
                <div class="alert alert-info">
                    <strong>💡 防护要点：</strong><br>
                    1. 使用URL白名单而非黑名单<br>
                    2. 验证解析后的IP地址，防止DNS重绑定<br>
                    3. 禁用file://、dict://、gopher://等危险协议<br>
                    4. 拒绝访问私有IP段和本地回环地址<br>
                    5. 对响应内容进行过滤和验证
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function fillUrl(url) {
            document.querySelector('input[name="url"]').value = url;
        }
    </script>
</body>
</html>