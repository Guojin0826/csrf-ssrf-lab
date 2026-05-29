<?php
/**
 * SSRF演示 - 拟真场景
 * 模拟：图片代理服务、URL预览功能
 */

$message = '';
$error = '';
$result = '';
$resultType = '';

// 获取当前模式
$mode = $_COOKIE['ssrf_mode'] ?? 'vulnerable';

// 安全的URL验证函数
function isValidUrl($url) {
    // 白名单域名
    $allowedDomains = ['example.com', 'httpbin.org', 'picsum.photos', 'via.placeholder.com'];
    
    // 解析URL
    $parsedUrl = parse_url($url);
    
    if (!$parsedUrl || !isset($parsedUrl['host'])) {
        return false;
    }
    
    // 检查协议
    if (isset($parsedUrl['scheme']) && !in_array($parsedUrl['scheme'], ['http', 'https'])) {
        return false;
    }
    
    $host = $parsedUrl['host'];
    
    // 检查是否是内网IP
    $ip = gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
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

// 处理图片代理请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'proxy') {
    $url = trim($_POST['url'] ?? '');
    
    if (empty($url)) {
        $error = '请输入图片URL';
    } else {
        // 根据模式决定是否验证URL
        if ($mode === 'secure') {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $error = 'URL格式不合法';
            } elseif (!isValidUrl($url)) {
                $error = 'URL不在允许的白名单内，或尝试访问内网资源';
            }
        }
        
        if (empty($error)) {
            try {
                // 使用file_get_contents替代curl
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'follow_location' => $mode === 'vulnerable',
                        'ignore_errors' => true
                    ],
                    'ssl' => [
                        'verify_peer' => $mode === 'secure',
                        'verify_peer_name' => $mode === 'secure'
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                
                if ($response === false) {
                    $error = '请求失败：无法访问该URL';
                } else {
                    // 获取响应头信息
                    $httpCode = 200;
                    $contentType = 'text/html';
                    
                    if (isset($http_response_header)) {
                        foreach ($http_response_header as $header) {
                            if (strpos($header, 'HTTP/') !== false) {
                                $parts = explode(' ', $header);
                                $httpCode = intval($parts[1] ?? 200);
                            }
                            if (strpos($header, 'Content-Type:') !== false) {
                                $contentType = trim(str_replace('Content-Type:', '', $header));
                            }
                        }
                    }
                    
                    $result = $response;
                    $resultType = $contentType;
                    $modeText = $mode === 'secure' ? '安全版本' : '漏洞版本';
                    $message = "【{$modeText}】请求成功！HTTP状态码: {$httpCode}，内容类型: {$contentType}";
                }
            } catch (Exception $e) {
                $error = "请求异常: " . $e->getMessage();
            }
        }
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
    <title>SSRF漏洞演示 - 拟真场景</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            color: #4facfe;
            font-size: 1.3em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-bar .nav-links {
            display: flex;
            gap: 15px;
        }
        .nav-bar .nav-links a {
            color: #666;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .nav-bar .nav-links a:hover {
            background: #4facfe;
            color: white;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
        }
        .header h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 1.05em;
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
            font-size: 1.3em;
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #4facfe;
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
        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .result-box {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow: auto;
        }
        .result-box pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #333;
        }
        .payload-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .payload-list h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95em;
        }
        .payload-list ul {
            list-style: none;
            padding: 0;
        }
        .payload-list li {
            padding: 8px 12px;
            margin: 5px 0;
            background: white;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.85em;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }
        .payload-list li:hover {
            background: #4facfe;
            color: white;
            border-color: #4facfe;
        }
        .payload-list li strong {
            color: #4facfe;
        }
        .payload-list li:hover strong {
            color: white;
        }
        .scenario-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .scenario-box h3 {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .scenario-box p {
            opacity: 0.95;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="logo">
            <span>🌐</span>
            <span>SSRF漏洞演示</span>
        </div>
        <div class="nav-links">
            <a href="../index.php">🏠 返回主页</a>
        </div>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>🌐 SSRF漏洞演示平台</h1>
            <p>Server-Side Request Forgery - 服务端请求伪造</p>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>
        
        <!-- 场景说明 -->
        <div class="scenario-box">
            <h3>📌 真实场景模拟</h3>
            <p>
                本演示模拟了一个<strong>图片代理服务</strong>，常见于以下场景：<br>
                • 社交平台的URL预览功能（如Facebook、Twitter的链接预览）<br>
                • 图片代理服务（绕过防盗链、CDN加速）<br>
                • Webhook回调地址验证<br>
                • PDF生成服务的URL参数<br>
                攻击者可以利用SSRF漏洞访问内网服务、读取本地文件、探测内网端口等。
            </p>
        </div>
        
        <!-- 图片代理服务 -->
        <div class="card">
            <div class="card-header <?php echo $mode === 'secure' ? 'secure' : 'vulnerable'; ?>">
                <h2>
                    <?php if ($mode === 'secure'): ?>
                    ✅ 图片代理服务（安全版本 - URL白名单验证）
                    <?php else: ?>
                    ⚠️ 图片代理服务（漏洞版本 - 无URL验证）
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
                    <strong>✅ 安全防护措施：</strong>
                    <br>• URL白名单限制（只允许指定域名）
                    <br>• 禁止访问内网IP和保留IP
                    <br>• 只允许http/https协议
                    <br>• 禁止跟随重定向
                </div>
                <?php else: ?>
                <div class="warning-box">
                    <strong>⚠️ 安全警告：</strong>此接口直接使用用户提供的URL发起请求，没有进行任何验证！攻击者可以访问内网资源、本地文件等。
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="proxy">
                    
                    <div class="form-group">
                        <label>🖼️ 图片URL</label>
                        <input type="text" name="url" id="urlInput" placeholder="请输入图片URL" required>
                    </div>
                    
                    <button type="submit" class="btn <?php echo $mode === 'secure' ? 'btn-primary' : 'btn-danger'; ?>">获取图片</button>
                </form>
                
                <div class="payload-list">
                    <h4>🎯 测试Payload（点击自动填入）</h4>
                    <ul>
                        <li onclick="fillUrl(this)"><strong>访问本地服务：</strong>http://127.0.0.1:80/</li>
                        <li onclick="fillUrl(this)"><strong>探测内网IP：</strong>http://192.168.1.1/</li>
                        <li onclick="fillUrl(this)"><strong>读取本地文件(Linux)：</strong>file:///etc/passwd</li>
                        <li onclick="fillUrl(this)"><strong>读取本地文件(Windows)：</strong>file:///C:/windows/win.ini</li>
                        <li onclick="fillUrl(this)"><strong>探测Redis服务：</strong>dict://127.0.0.1:6379/info</li>
                        <li onclick="fillUrl(this)"><strong>正常图片：</strong>https://picsum.photos/200/300</li>
                        <?php if ($mode === 'secure'): ?>
                        <li onclick="fillUrl(this)"><strong>允许的域名：</strong>https://via.placeholder.com/150</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <?php if ($result): ?>
                <div class="result-box">
                    <pre><?php 
                        if (strpos($resultType, 'image/') !== false) {
                            echo "[返回二进制图片数据，大小: " . strlen($result) . " 字节]";
                        } else {
                            echo h(substr($result, 0, 2000));
                            if (strlen($result) > 2000) echo "\n\n... [内容已截断，总长度: " . strlen($result) . " 字节]";
                        }
                    ?></pre>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // 模式切换
        document.getElementById('modeSwitch').addEventListener('change', function() {
            const mode = this.checked ? 'secure' : 'vulnerable';
            document.cookie = 'ssrf_mode=' + mode + ';path=/;max-age=86400';
            location.reload();
        });
        
        // 填充URL
        function fillUrl(element) {
            const text = element.textContent;
            const url = text.split('：')[1].trim();
            document.getElementById('urlInput').value = url;
            document.getElementById('urlInput').focus();
        }
    </script>
</body>
</html>