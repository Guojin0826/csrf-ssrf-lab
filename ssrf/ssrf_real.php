<?php
/**
 * SSRF拟真演示 - 模拟真实环境场景
 */
session_start();

// 检查登录
if (!isset($_SESSION['ssrf_demo_user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['ssrf_demo_user'];
$result = '';
$error = '';
$success = '';
$current_mode = isset($_GET['mode']) ? $_GET['mode'] : 'vulnerable';
$current_scenario = isset($_GET['scenario']) ? $_GET['scenario'] : 'image';

// 真实场景列表
$scenarios = [
    'image' => [
        'name' => '🖼️ 图片加载服务',
        'description' => '模拟社交平台、博客系统中的远程图片加载功能',
        'real_case' => 'WordPress、Discuz、各类CMS系统的远程图片上传功能',
        'vulnerable_code' => '// WordPress style.css远程加载
$url = $_POST["image_url"];
$image = file_get_contents($url);
file_put_contents("uploads/" . basename($url), $image);',
        'defense_code' => '// 安全版本：验证URL
$url = $_POST["image_url"];
if (!isValidImageUrl($url)) {
    die("非法URL");
}
$image = file_get_contents($url);',
        'payloads' => [
            '读取配置文件' => 'file://./config.php',
            '读取环境变量' => 'file://./.env',
            '访问内网API' => 'http://127.0.0.1:8080/api/users',
            '探测Redis服务' => 'http://127.0.0.1:6379/',
            '获取AWS凭证' => 'http://169.254.169.254/latest/meta-data/iam/security-credentials/',
        ]
    ],
    'webhook' => [
        'name' => '🔗 Webhook回调服务',
        'description' => '模拟Git、Slack、钉钉等平台的Webhook回调功能',
        'real_case' => 'GitHub Webhooks、GitLab Webhooks、Jenkins触发器',
        'vulnerable_code' => '// Webhook回调
$webhook_url = $_POST["webhook_url"];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_exec($ch);',
        'defense_code' => '// 安全版本：白名单验证
$webhook_url = $_POST["webhook_url"];
if (!in_array(parse_url($webhook_url, PHP_URL_HOST), $allowed_hosts)) {
    die("非法域名");
}',
        'payloads' => [
            '探测内网Git服务' => 'http://192.168.1.10:8080/.git/config',
            '访问Jenkins' => 'http://127.0.0.1:8080/job/config.xml',
            '读取内网配置' => 'http://192.168.1.20/config.php',
            '探测数据库服务' => 'http://192.168.1.30:3306/',
            '访问管理后台' => 'http://192.168.1.50/admin/',
        ]
    ],
    'pdf' => [
        'name' => '📄 PDF导出服务',
        'description' => '模拟在线PDF生成、HTML转PDF功能',
        'real_case' => 'wkhtmltopdf、PhantomJS、Puppeteer等PDF生成工具',
        'vulnerable_code' => '// PDF生成
$html_url = $_GET["url"];
$pdf = shell_exec("wkhtmltopdf $html_url output.pdf");',
        'defense_code' => '// 安全版本：验证URL
$html_url = $_GET["url"];
if (!isValidUrl($html_url)) {
    die("非法URL");
}
$pdf = shell_exec("wkhtmltopdf " . escapeshellarg($html_url) . " output.pdf");',
        'payloads' => [
            '读取本地文件' => 'file:///etc/passwd',
            '读取应用配置' => 'file:///var/www/html/config.php',
            '访问内网服务' => 'http://127.0.0.1:8080/admin',
            '读取SSH密钥' => 'file:///home/user/.ssh/id_rsa',
            '读取AWS凭证' => 'http://169.254.169.254/latest/meta-data/iam/security-credentials/',
        ]
    ],
    'proxy' => [
        'name' => '🌐 URL代理服务',
        'description' => '模拟URL预览、短链接展开、代理访问功能',
        'real_case' => 'Facebook链接预览、Twitter卡片、Slack URL展开',
        'vulnerable_code' => '// URL代理
$url = $_GET["url"];
$content = file_get_contents($url);
echo $content;',
        'defense_code' => '// 安全版本：白名单验证
$url = $_GET["url"];
if (!isAllowedDomain($url)) {
    die("域名不在白名单中");
}
$content = file_get_contents($url);',
        'payloads' => [
            '访问内网Web服务' => 'http://192.168.1.10/',
            '读取内网配置' => 'http://192.168.1.20/config.php',
            '探测内网端口' => 'http://127.0.0.1:6379/',
            '访问云元数据' => 'http://169.254.169.254/latest/meta-data/',
            '读取本地文件' => 'file:///etc/passwd',
        ]
    ],
    'api' => [
        'name' => '🔌 API请求转发',
        'description' => '模拟后端API网关、微服务调用场景',
        'real_case' => 'API Gateway、BFF层、服务间调用',
        'vulnerable_code' => '// API转发
$api_url = $_POST["api_endpoint"];
$response = file_get_contents($api_url);
return json_decode($response, true);',
        'defense_code' => '// 安全版本：服务发现
$service_name = $_POST["service"];
$api_url = getServiceUrl($service_name);
$response = file_get_contents($api_url);',
        'payloads' => [
            '访问内网用户服务' => 'http://user-service:8080/api/users',
            '访问内网订单服务' => 'http://order-service:8080/api/orders',
            '访问内网支付服务' => 'http://payment-service:8080/api/payments',
            '读取服务配置' => 'http://config-server:8888/application.yml',
            '访问管理接口' => 'http://admin-service:8080/actuator/env',
        ]
    ],
    'cache' => [
        'name' => '💾 缓存预加载服务',
        'description' => '模拟CDN缓存预热、数据预加载功能',
        'real_case' => 'CDN缓存预热、Redis缓存预加载、页面预渲染',
        'vulnerable_code' => '// 缓存预热
$preload_url = $_POST["url"];
$content = file_get_contents($preload_url);
$redis->set("cache:" . $preload_url, $content);',
        'defense_code' => '// 安全版本：URL验证
$preload_url = $_POST["url"];
if (!isCacheableUrl($preload_url)) {
    die("不可缓存的URL");
}
$content = file_get_contents($preload_url);',
        'payloads' => [
            '读取Redis配置' => 'http://127.0.0.1:6379/INFO',
            '访问内网服务' => 'http://internal-service:8080/',
            '读取应用配置' => 'file://./config/database.yml',
            '探测内网拓扑' => 'http://192.168.1.1/',
            '访问云元数据' => 'http://169.254.169.254/latest/meta-data/',
        ]
    ]
];

// 安全的URL验证函数
function isValidUrl($url) {
    $parsedUrl = parse_url($url);
    if (!$parsedUrl || !isset($parsedUrl['scheme'])) {
        return false;
    }
    
    $scheme = strtolower($parsedUrl['scheme']);
    if (!in_array($scheme, ['http', 'https'])) {
        return false;
    }
    
    $host = $parsedUrl['host'] ?? '';
    
    // 检查危险地址
    $dangerous_hosts = ['localhost', '127.0.0.1', '0.0.0.0', '169.254.169.254', '100.100.100.200'];
    if (in_array($host, $dangerous_hosts)) {
        return false;
    }
    
    // 检查内网IP
    $ip = gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    
    return true;
}

// 处理请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    
    if (empty($url)) {
        $error = '请输入URL地址';
    } else {
        if ($current_mode === 'vulnerable') {
            // 漏洞版本：直接请求
            try {
                $parsedUrl = parse_url($url);
                $scheme = strtolower($parsedUrl['scheme'] ?? 'http');
                
                if ($scheme === 'file') {
                    // file协议
                    $file_path = $parsedUrl['path'] ?? '';
                    if (file_exists($file_path)) {
                        $result = file_get_contents($file_path);
                        $success = "✅ 文件读取成功！";
                    } else {
                        // 模拟读取
                        $result = simulateFileRead($file_path);
                        $success = "✅ 文件读取成功（模拟）！";
                    }
                } else {
                    // http/https协议
                    $result = simulateHttpRequest($url);
                    $success = "✅ 请求成功！";
                }
            } catch (Exception $e) {
                $error = '请求异常: ' . $e->getMessage();
            }
        } else {
            // 安全版本：验证URL
            if (!isValidUrl($url)) {
                $error = '❌ URL验证失败：禁止访问内网地址或使用危险协议';
            } else {
                $error = '✅ 安全版本：已阻止对内网/危险地址的访问';
            }
        }
    }
}

// 模拟文件读取
function simulateFileRead($path) {
    $files = [
        '/etc/passwd' => "root:x:0:0:root:/root:/bin/bash\ndaemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin\nwww-data:x:33:33:www-data:/var/www:/usr/sbin/nologin\n",
        './config.php' => "<?php\n\$db_host = 'localhost';\n\$db_user = 'root';\n\$db_pass = 'P@ssw0rd!';\n\$db_name = 'production';\n?>\n",
        './.env' => "DB_HOST=localhost\nDB_PASSWORD=secret123\nAWS_ACCESS_KEY=AKIAIOSFODNN7EXAMPLE\nAWS_SECRET_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY\n",
        '/var/www/html/config.php' => "<?php\n\$config = [\n    'database' => 'mysql://root:password@localhost/db',\n    'secret_key' => 'super_secret_key_123',\n];\n?>\n",
    ];
    
    return $files[$path] ?? "File: $path\nContent: [模拟文件内容]\n";
}

// 模拟HTTP请求
function simulateHttpRequest($url) {
    // 解析URL
    $parsedUrl = parse_url($url);
    $host = $parsedUrl['host'] ?? 'unknown';
    $port = $parsedUrl['port'] ?? 80;
    $path = $parsedUrl['path'] ?? '/';
    
    // 模拟不同服务的响应
    if (strpos($host, '169.254.169.254') !== false) {
        // AWS元数据
        return "{\n  \"code\": \"success\",\n  \"iam_role\": \"ec2-admin-role\",\n  \"access_key\": \"ASIAIOSFODNN7EXAMPLE\",\n  \"secret_key\": \"wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY\",\n  \"token\": \"FQoGZXIvYXdzEA//////////\"\n}\n";
    } elseif (strpos($host, '127.0.0.1') !== false || strpos($host, 'localhost') !== false) {
        if ($port == 6379) {
            return "Redis server v=6.2.6\n# Server\nredis_version:6.2.6\nredis_mode:standalone\n# Clients\nconnected_clients:5\n";
        } elseif ($port == 3306) {
            return "MySQL Server 5.7.35\nProtocol version: 10\nConnection id: 12345\n";
        } else {
            return "HTTP/1.1 200 OK\nServer: nginx/1.18.0\nContent-Type: text/html\n\n<h1>Internal Service</h1><p>Welcome to internal service</p>\n";
        }
    } elseif (strpos($host, '192.168') !== false) {
        return "HTTP/1.1 200 OK\nServer: Apache/2.4.41\nContent-Type: text/html\n\n<h1>Intranet Service</h1><p>Host: $host</p><p>Path: $path</p>\n";
    } else {
        return "HTTP/1.1 200 OK\nServer: nginx/1.18.0\nContent-Type: application/json\n\n{\"status\": \"success\", \"message\": \"Request completed\"}\n";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSRF拟真演示 - 真实环境场景</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .navbar {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            color: #667eea;
            font-size: 1.3em;
        }
        .nav-links { display: flex; gap: 10px; }
        .nav-links a {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #667eea;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 0.9em;
        }
        .nav-links a:hover {
            background: #667eea;
            color: white;
        }
        
        .scenario-tabs {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .scenario-tab {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            text-decoration: none;
            color: #333;
            font-size: 0.9em;
        }
        .scenario-tab.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .mode-tabs {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        .mode-tab {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            text-decoration: none;
            color: #333;
        }
        .mode-tab.active.vulnerable {
            background: #f56c6c;
            color: white;
            border-color: #f56c6c;
        }
        .mode-tab.active.safe {
            background: #67c23a;
            color: white;
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
            border-left: 4px solid #667eea;
            padding-left: 10px;
        }
        .section h3 {
            color: #667eea;
            margin: 20px 0 10px 0;
            font-size: 1.1em;
        }
        .section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        
        .scenario-info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
        }
        .scenario-info-box h3 {
            color: white;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .scenario-info-box p {
            color: rgba(255,255,255,0.9);
            margin-bottom: 10px;
        }
        
        .real-case-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .real-case-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .demo-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .demo-box h3 {
            color: #667eea;
            margin-bottom: 15px;
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
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1em;
            font-family: 'Courier New', monospace;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
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
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #67c23a; color: white; }
        .btn-danger { background: #f56c6c; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
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
        
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            margin: 15px 0;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        
        .payload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .payload-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s;
        }
        .payload-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        .payload-card h4 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 0.95em;
        }
        .payload-card code {
            display: block;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            color: #333;
            margin-bottom: 10px;
            word-break: break-all;
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
        
        .defense-list {
            background: #f0f9ff;
            border-left: 4px solid #67c23a;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .defense-list h4 {
            color: #67c23a;
            margin-bottom: 10px;
        }
        .defense-list ul {
            margin-left: 20px;
        }
        .defense-list li {
            color: #333;
            margin: 8px 0;
            line-height: 1.6;
        }
        
        .attack-flow {
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
            border-left: 4px solid #667eea;
        }
        .flow-step .step-number {
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
        .flow-step .step-content h4 {
            color: #667eea;
            margin-bottom: 5px;
        }
        .flow-step .step-content p {
            color: #666;
            font-size: 0.9em;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>🎯 SSRF拟真演示 - 真实环境场景</h1>
            <div class="nav-links">
                <a href="index.php">📚 返回首页</a>
                <a href="../index.php">🏠 返回主页</a>
            </div>
        </div>
        
        <!-- 场景选择 -->
        <div class="scenario-tabs">
            <?php foreach ($scenarios as $key => $scenario): ?>
            <a href="?scenario=<?php echo $key; ?>&mode=<?php echo $current_mode; ?>" 
               class="scenario-tab <?php echo $current_scenario === $key ? 'active' : ''; ?>">
                <?php echo $scenario['name']; ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <!-- 模式切换 -->
        <div class="mode-tabs">
            <a href="?scenario=<?php echo $current_scenario; ?>&mode=vulnerable" 
               class="mode-tab vulnerable <?php echo $current_mode === 'vulnerable' ? 'active' : ''; ?>">
                ⚠️ 漏洞版本
            </a>
            <a href="?scenario=<?php echo $current_scenario; ?>&mode=safe" 
               class="mode-tab safe <?php echo $current_mode === 'safe' ? 'active' : ''; ?>">
                ✅ 安全版本
            </a>
        </div>
        
        <div class="content">
            <!-- 场景信息 -->
            <?php $scenario = $scenarios[$current_scenario]; ?>
            <div class="scenario-info-box">
                <h3><?php echo $scenario['name']; ?> - 场景说明</h3>
                <p><strong>场景描述：</strong><?php echo $scenario['description']; ?></p>
                <p><strong>真实案例：</strong><?php echo $scenario['real_case']; ?></p>
            </div>
            
            <!-- 真实案例 -->
            <div class="real-case-box">
                <h4>🏢 真实环境案例</h4>
                <p style="color: #856404; margin: 0;"><?php echo $scenario['real_case']; ?></p>
            </div>
            
            <!-- 漏洞代码 -->
            <div class="section">
                <h2>🔍 漏洞代码分析</h2>
                
                <h3>⚠️ 漏洞版本代码</h3>
                <div class="code-block"><?php echo htmlspecialchars($scenario['vulnerable_code']); ?></div>
                
                <div class="alert alert-danger">
                    <strong>漏洞原因：</strong><br>
                    • 直接使用用户输入的URL，未进行任何验证<br>
                    • 可访问内网服务、读取本地文件、获取云元数据<br>
                    • 攻击者可利用此漏洞探测内网拓扑、窃取敏感信息
                </div>
                
                <h3>✅ 安全版本代码</h3>
                <div class="code-block"><?php echo htmlspecialchars($scenario['defense_code']); ?></div>
                
                <div class="alert alert-success">
                    <strong>防护措施：</strong><br>
                    • 验证URL协议，只允许http/https<br>
                    • 检查目标地址，拒绝内网IP和本地地址<br>
                    • 使用白名单验证域名<br>
                    • 禁用file://等危险协议
                </div>
            </div>
            
            <!-- 攻击流程 -->
            <div class="section">
                <h2>🎯 攻击流程</h2>
                <div class="attack-flow">
                    <div class="flow-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>发现SSRF漏洞点</h4>
                            <p>在<?php echo $scenario['name']; ?>功能中发现用户可控的URL参数</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>构造攻击Payload</h4>
                            <p>构造指向内网服务、本地文件或云元数据的URL</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>发送恶意请求</h4>
                            <p>通过漏洞功能发送构造的URL，服务器代为访问</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>获取敏感信息</h4>
                            <p>读取内网服务响应、配置文件内容或云元数据</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4>进一步攻击</h4>
                            <p>利用获取的信息进行横向渗透、权限提升等后续攻击</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 演示区域 -->
            <div class="demo-box">
                <h3>🔧 漏洞演示</h3>
                <p>模拟<?php echo $scenario['name']; ?>功能的SSRF漏洞：</p>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="?scenario=<?php echo $current_scenario; ?>&mode=<?php echo $current_mode; ?>">
                    <div class="form-group">
                        <label>🌐 输入URL</label>
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
                <p>点击以下Payload快速测试：</p>
                
                <div class="payload-grid">
                    <?php foreach ($scenario['payloads'] as $name => $payload): ?>
                    <div class="payload-card">
                        <h4><?php echo $name; ?></h4>
                        <code><?php echo htmlspecialchars($payload); ?></code>
                        <button class="btn btn-danger" onclick="fillUrl('<?php echo htmlspecialchars($payload, ENT_QUOTES); ?>')">
                            执行攻击
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- 防护建议 -->
            <div class="section">
                <h2>🛡️ 防护措施详解</h2>
                
                <div class="defense-list">
                    <h4>✅ 防护方案</h4>
                    <ul>
                        <li><strong>URL白名单验证：</strong>只允许访问预定义的域名列表</li>
                        <li><strong>协议限制：</strong>只允许http和https协议，禁用file://、dict://、gopher://等</li>
                        <li><strong>内网IP过滤：</strong>拒绝访问私有IP段（10.0.0.0/8、172.16.0.0/12、192.168.0.0/16）</li>
                        <li><strong>本地地址过滤：</strong>拒绝访问localhost、127.0.0.1、0.0.0.0等</li>
                        <li><strong>云元数据过滤：</strong>拒绝访问169.254.169.254、100.100.100.200等元数据地址</li>
                        <li><strong>DNS重绑定防护：</strong>验证解析后的IP地址，防止DNS重绑定攻击</li>
                        <li><strong>请求超时限制：</strong>设置合理的超时时间，防止长时间阻塞</li>
                        <li><strong>响应大小限制：</strong>限制响应内容大小，防止资源耗尽</li>
                    </ul>
                </div>
                
                <div class="code-block">
<strong>完整防护代码示例：</strong>

function isSafeUrl($url) {
    // 1. 解析URL
    $parsedUrl = parse_url($url);
    if (!$parsedUrl || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
        return false;
    }
    
    // 2. 协议白名单
    $scheme = strtolower($parsedUrl['scheme']);
    if (!in_array($scheme, ['http', 'https'])) {
        return false;
    }
    
    // 3. 检查危险地址
    $host = $parsedUrl['host'];
    $dangerous_hosts = ['localhost', '127.0.0.1', '0.0.0.0', 
                        '169.254.169.254', '100.100.100.200', 
                        'metadata.google.internal'];
    if (in_array($host, $dangerous_hosts)) {
        return false;
    }
    
    // 4. 解析IP并检查内网地址
    $ip = gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP, 
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    
    // 5. 域名白名单（可选）
    $allowed_domains = ['example.com', 'api.example.com'];
    $is_allowed = false;
    foreach ($allowed_domains as $domain) {
        if (strpos($host, $domain) !== false) {
            $is_allowed = true;
            break;
        }
    }
    
    return $is_allowed;
}
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