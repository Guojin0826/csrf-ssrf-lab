<?php
/**
 * SSRF协议利用演示 - file/dict/gopher协议攻击
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
$current_protocol = isset($_GET['protocol']) ? $_GET['protocol'] : 'file';

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
            try {
                // 解析协议
                $parsedUrl = parse_url($url);
                $scheme = strtolower($parsedUrl['scheme'] ?? 'http');
                
                // 根据不同协议处理
                if ($scheme === 'file') {
                    // file://协议 - 读取本地文件
                    $file_path = $parsedUrl['path'] ?? '';
                    if (file_exists($file_path)) {
                        $result = file_get_contents($file_path);
                        $success = "✅ 文件读取成功！";
                    } else {
                        $error = '文件不存在或无法访问';
                    }
                } elseif ($scheme === 'dict') {
                    // dict://协议 - 探测服务
                    $host = $parsedUrl['host'] ?? '127.0.0.1';
                    $port = $parsedUrl['port'] ?? 2628;
                    
                    // 模拟dict协议响应
                    $result = simulateDictResponse($host, $port);
                    $success = "✅ Dict协议探测成功！";
                } elseif ($scheme === 'gopher') {
                    // gopher://协议 - 构造任意请求
                    $result = simulateGopherResponse($url);
                    $success = "✅ Gopher协议请求成功！";
                } else {
                    // http/https协议
                    $context = stream_context_create([
                        'http' => ['timeout' => 5, 'ignore_errors' => true],
                        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                    ]);
                    
                    $response = @file_get_contents($url, false, $context);
                    
                    if ($response === false) {
                        $error = '请求失败: 无法访问该URL';
                    } else {
                        $result = $response;
                        $success = "✅ 请求成功！";
                    }
                }
            } catch (Exception $e) {
                $error = '请求异常: ' . $e->getMessage();
            }
        } else {
            if (!isValidUrl($url)) {
                $error = '❌ URL验证失败：禁止使用危险协议或访问内网地址';
            } else {
                $error = '✅ 安全版本：已阻止危险协议请求';
            }
        }
    }
}

// 模拟Dict协议响应
function simulateDictResponse($host, $port) {
    $responses = [
        6379 => "Redis server v=6.2.6\n# Server\nredis_version:6.2.6\nredis_mode:standalone\nos:Linux\narch_bits:64\n",
        11211 => "STAT pid 1234\nSTAT uptime 12345\nSTAT version 1.6.9\n",
        3306 => "MySQL Server 5.7.35\nProtocol version: 10\n",
        27017 => "MongoDB shell version v5.0.0\n",
        9200 => "{\n  \"name\" : \"node-1\",\n  \"cluster_name\" : \"elasticsearch\",\n  \"version\" : {\n    \"number\" : \"7.10.0\"\n  }\n}\n"
    ];
    
    return $responses[$port] ?? "Connection to $host:$port successful\nService detected on port $port\n";
}

// 模拟Gopher协议响应
function simulateGopherResponse($url) {
    // 解析gopher URL
    if (preg_match('#gopher://([^:]+):(\d+)/_(.+)#', $url, $matches)) {
        $host = $matches[1];
        $port = $matches[2];
        $data = urldecode($matches[3]);
        
        return "Gopher Request Sent:\n" .
               "Target: $host:$port\n" .
               "Data: $data\n\n" .
               "Response:\n" .
               "OK - Command executed successfully\n";
    }
    
    return "Invalid gopher URL format";
}

// 协议知识
$protocol_knowledge = [
    'file' => [
        'name' => 'file:// 协议',
        'description' => 'file协议用于读取本地文件系统中的文件，是SSRF攻击中最常用的协议之一',
        'format' => 'file:///path/to/file',
        'danger' => '高危 - 可读取系统敏感文件、配置文件、源代码等',
        'examples' => [
            'Linux系统文件' => [
                '/etc/passwd' => '用户账户信息',
                '/etc/shadow' => '用户密码哈希',
                '/etc/hosts' => '主机名解析',
                '/proc/self/environ' => '当前进程环境变量',
                '/proc/self/cmdline' => '当前进程命令行',
                '/var/log/auth.log' => 'SSH登录日志',
            ],
            'Windows系统文件' => [
                'C:/windows/win.ini' => 'Windows配置文件',
                'C:/windows/system32/config/sam' => '用户账户数据库',
                'C:/xampp/apache/conf/httpd.conf' => 'Apache配置文件',
                'C:/xampp/mysql/data/mysql/user.MYD' => 'MySQL用户数据',
            ],
            '应用配置文件' => [
                './config.php' => '应用配置文件',
                './.env' => '环境变量配置',
                './database.yml' => '数据库配置',
                './config/database.php' => '数据库连接配置',
            ]
        ],
        'defense' => [
            '禁用file://协议',
            '使用白名单验证URL协议',
            '检查URL是否包含file://',
            '使用open_basedir限制文件访问范围',
            '避免用户可控的文件路径'
        ]
    ],
    'dict' => [
        'name' => 'dict:// 协议',
        'description' => 'dict协议用于访问字典服务，可被用于探测内网服务、端口扫描、获取服务Banner信息',
        'format' => 'dict://host:port/command',
        'danger' => '中危 - 可探测内网服务、端口开放情况、服务版本信息',
        'examples' => [
            'Redis探测' => [
                'dict://127.0.0.1:6379/info' => '获取Redis信息',
                'dict://127.0.0.1:6379/keys *' => '列出所有key',
                'dict://127.0.0.1:6379/get secret_key' => '获取特定key的值',
            ],
            'Memcached探测' => [
                'dict://127.0.0.1:11211/stats' => '获取Memcached状态',
                'dict://127.0.0.1:11211/stats items' => '获取items统计',
            ],
            '其他服务探测' => [
                'dict://192.168.1.1:80' => '探测Web服务',
                'dict://192.168.1.1:3306' => '探测MySQL服务',
                'dict://192.168.1.1:27017' => '探测MongoDB服务',
            ]
        ],
        'defense' => [
            '禁用dict://协议',
            '限制可访问的端口范围',
            '使用白名单验证目标地址',
            '监控异常的dict协议请求',
            '使用网络防火墙限制内网访问'
        ]
    ],
    'gopher' => [
        'name' => 'gopher:// 协议',
        'description' => 'gopher协议是最强大的SSRF攻击协议，可以构造任意TCP数据包，实现对内网服务的完全控制',
        'format' => 'gopher://host:port/_<encoded_data>',
        'danger' => '极高危 - 可构造任意TCP请求，攻击Redis、MySQL、FastCGI等服务',
        'examples' => [
            'Redis攻击' => [
                'gopher://127.0.0.1:6379/_*1%0d%0a$8%0d%0aflushall%0d%0a*3%0d%0a$3%0d%0aset%0d%0a$1%0d%0a1%0d%0a$64%0d%0a...' => 'Redis主从复制RCE',
                'gopher://127.0.0.1:6379/_*4%0d%0a$4%0d%0aconfig%0d%0a$3%0d%0adir%0d%0a$11%0d%0a/var/www/html' => '修改Redis目录',
            ],
            'FastCGI攻击' => [
                'gopher://127.0.0.1:9000/_%01%01%00%01%00%08%00%00%00%01%00%00%00%00%00%00...' => 'FastCGI RCE',
            ],
            'MySQL攻击' => [
                'gopher://127.0.0.1:3306/_<mysql_packet>' => 'MySQL未授权访问',
            ]
        ],
        'defense' => [
            '禁用gopher://协议',
            '严格限制可访问的协议类型',
            '使用白名单验证URL',
            '监控异常的gopher协议请求',
            '限制服务器对内网的访问能力',
            '使用WAF检测gopher协议特征'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSRF协议利用演示 - file/dict/gopher</title>
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
        
        .protocol-tabs {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        .protocol-tab {
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
        .protocol-tab.active {
            background: #11998e;
            color: white;
            border-color: #11998e;
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
            border-left: 4px solid #11998e;
            padding-left: 10px;
        }
        .section h3 {
            color: #11998e;
            margin: 20px 0 10px 0;
            font-size: 1.1em;
        }
        .section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        
        .protocol-info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
        }
        .protocol-info-box h3 {
            color: white;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .protocol-info-box p {
            color: rgba(255,255,255,0.9);
            margin-bottom: 10px;
        }
        
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
            border-color: #11998e;
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
        
        .payload-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .payload-table th {
            background: #11998e;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .payload-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .payload-table tr:hover {
            background: #f8f9fa;
        }
        .payload-table code {
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            color: #11998e;
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
        
        .danger-box {
            background: #fef0f0;
            border-left: 4px solid #f56c6c;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .danger-box h4 {
            color: #f56c6c;
            margin-bottom: 10px;
        }
        
        .example-category {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .example-category h4 {
            color: #11998e;
            margin-bottom: 10px;
            font-size: 1em;
        }
        .example-item {
            padding: 10px;
            margin: 8px 0;
            background: #f8f9fa;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .example-item code {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #11998e;
        }
        .example-item small {
            color: #666;
            font-size: 0.85em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>📡 SSRF协议利用演示 - file/dict/gopher</h1>
            <div class="nav-links">
                <a href="index.php">📚 返回首页</a>
                <a href="../index.php">🏠 返回主页</a>
            </div>
        </div>
        
        <!-- 协议选择 -->
        <div class="protocol-tabs">
            <a href="?protocol=file&mode=<?php echo $current_mode; ?>" 
               class="protocol-tab <?php echo $current_protocol === 'file' ? 'active' : ''; ?>">
                📂 file:// 协议
            </a>
            <a href="?protocol=dict&mode=<?php echo $current_mode; ?>" 
               class="protocol-tab <?php echo $current_protocol === 'dict' ? 'active' : ''; ?>">
                🔍 dict:// 协议
            </a>
            <a href="?protocol=gopher&mode=<?php echo $current_mode; ?>" 
               class="protocol-tab <?php echo $current_protocol === 'gopher' ? 'active' : ''; ?>">
                ⚡ gopher:// 协议
            </a>
        </div>
        
        <!-- 模式切换 -->
        <div class="mode-tabs">
            <a href="?protocol=<?php echo $current_protocol; ?>&mode=vulnerable" 
               class="mode-tab vulnerable <?php echo $current_mode === 'vulnerable' ? 'active' : ''; ?>">
                ⚠️ 漏洞版本
            </a>
            <a href="?protocol=<?php echo $current_protocol; ?>&mode=safe" 
               class="mode-tab safe <?php echo $current_mode === 'safe' ? 'active' : ''; ?>">
                ✅ 安全版本
            </a>
        </div>
        
        <div class="content">
            <!-- 协议知识 -->
            <?php $protocol = $protocol_knowledge[$current_protocol]; ?>
            <div class="protocol-info-box">
                <h3>📖 <?php echo $protocol['name']; ?> - 知识讲解</h3>
                <p><strong>协议格式：</strong><code style="background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 4px;"><?php echo $protocol['format']; ?></code></p>
                <p><strong>协议说明：</strong><?php echo $protocol['description']; ?></p>
                <p><strong>危害等级：</strong><?php echo $protocol['danger']; ?></p>
            </div>
            
            <!-- 协议详解 -->
            <div class="section">
                <h2>🎯 <?php echo $protocol['name']; ?>攻击详解</h2>
                
                <?php if ($current_protocol === 'file'): ?>
                <div class="alert alert-info">
                    <strong>file://协议原理：</strong><br>
                    file协议是PHP、Python等语言内置支持的协议，用于读取本地文件系统。<br>
                    攻击者可通过SSRF漏洞，利用file协议读取服务器上的敏感文件，如配置文件、密码文件、源代码等。
                </div>
                
                <h3>📂 常见攻击目标</h3>
                <?php foreach ($protocol['examples'] as $category => $files): ?>
                <div class="example-category">
                    <h4><?php echo $category; ?></h4>
                    <?php foreach ($files as $path => $desc): ?>
                    <div class="example-item">
                        <div>
                            <code><?php echo $path; ?></code>
                            <br><small><?php echo $desc; ?></small>
                        </div>
                        <button class="btn btn-primary" onclick="fillUrl('file://<?php echo $path; ?>')">测试</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                
                <?php elseif ($current_protocol === 'dict'): ?>
                <div class="alert alert-info">
                    <strong>dict://协议原理：</strong><br>
                    dict协议原本用于查询字典服务，但可被用于探测内网服务。<br>
                    攻击者可构造dict://URL，探测内网服务的端口开放情况、服务版本等信息。
                </div>
                
                <h3>🔍 服务探测示例</h3>
                <?php foreach ($protocol['examples'] as $category => $urls): ?>
                <div class="example-category">
                    <h4><?php echo $category; ?></h4>
                    <?php foreach ($urls as $url => $desc): ?>
                    <div class="example-item">
                        <div>
                            <code><?php echo $url; ?></code>
                            <br><small><?php echo $desc; ?></small>
                        </div>
                        <button class="btn btn-primary" onclick="fillUrl('<?php echo $url; ?>')">测试</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                
                <?php elseif ($current_protocol === 'gopher'): ?>
                <div class="alert alert-info">
                    <strong>gopher://协议原理：</strong><br>
                    gopher协议是最强大的SSRF攻击协议，可以构造任意TCP数据包。<br>
                    攻击者可构造Redis、MySQL、FastCGI等服务的攻击请求，实现远程代码执行。
                </div>
                
                <h3>⚡ Gopher协议格式</h3>
                <div class="code-block">
<strong>Gopher URL格式：</strong>
gopher://host:port/_<encoded_data>

<strong>数据编码规则：</strong>
1. 数据前需要加下划线 _
2. 数据需要进行URL编码
3. 换行符需要编码为 %0d%0a
4. 空格需要编码为 %20

<strong>示例：</strong>
原始数据: INFO
编码后: gopher://127.0.0.1:6379/_INFO%0d%0a

原始数据: SET key value
编码后: gopher://127.0.0.1:6379/_*3%0d%0a$3%0d%0aSET%0d%0a$3%0d%0akey%0d%0a$5%0d%0avalue%0d%0a
                </div>
                
                <h3>⚡ 攻击示例</h3>
                <?php foreach ($protocol['examples'] as $category => $urls): ?>
                <div class="example-category">
                    <h4><?php echo $category; ?></h4>
                    <?php foreach ($urls as $url => $desc): ?>
                    <div class="example-item">
                        <div>
                            <code style="font-size: 0.8em;"><?php echo substr($url, 0, 60); ?>...</code>
                            <br><small><?php echo $desc; ?></small>
                        </div>
                        <button class="btn btn-danger" onclick="fillUrl('<?php echo $url; ?>')">测试</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                
                <h3>🔧 Gopher编码工具</h3>
                <div class="demo-box">
                    <p>将原始数据转换为Gopher格式：</p>
                    <div class="form-group">
                        <label>原始数据（Redis命令示例）：</label>
                        <textarea id="raw_data" rows="4" placeholder="INFO
SET key value
GET key">INFO</textarea>
                    </div>
                    <button class="btn btn-primary" onclick="encodeGopher()">编码为Gopher格式</button>
                    <div id="gopher_result" style="margin-top: 15px;"></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- 演示区域 -->
            <div class="demo-box">
                <h3>🔧 协议利用演示</h3>
                <p>输入URL进行攻击测试（仅漏洞版本有效）：</p>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="?protocol=<?php echo $current_protocol; ?>&mode=<?php echo $current_mode; ?>">
                    <div class="form-group">
                        <label>🌐 攻击URL</label>
                        <input type="text" name="url" placeholder="<?php echo $protocol['format']; ?>" 
                               value="<?php echo htmlspecialchars($_POST['url'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">发送请求</button>
                </form>
                
                <?php if ($result): ?>
                <h4 style="margin-top: 20px; color: #333;">📄 响应结果：</h4>
                <div class="result-box"><?php echo htmlspecialchars($result); ?></div>
                <?php endif; ?>
            </div>
            
            <!-- 防护措施 -->
            <div class="section">
                <h2>🛡️ 防护措施详解</h2>
                
                <div class="defense-list">
                    <h4>✅ 防护方案</h4>
                    <ul>
                        <?php foreach ($protocol['defense'] as $defense): ?>
                        <li><?php echo $defense; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="code-block">
<strong>PHP防护代码示例：</strong>

// 1. 检查协议
$parsedUrl = parse_url($url);
$scheme = strtolower($parsedUrl['scheme'] ?? '');

// 只允许http和https协议
if (!in_array($scheme, ['http', 'https'])) {
    die('禁止使用的协议: ' . $scheme);
}

// 2. 检查是否包含危险协议关键字
$dangerous_protocols = ['file://', 'dict://', 'gopher://', 'ftp://', 'php://'];
foreach ($dangerous_protocols as $proto) {
    if (stripos($url, $proto) !== false) {
        die('禁止访问危险协议');
    }
}

// 3. 使用白名单验证
$allowed_schemes = ['http', 'https'];
if (!in_array($scheme, $allowed_schemes)) {
    die('协议不在白名单中');
}
                </div>
            </div>
            
            <!-- 危害说明 -->
            <div class="danger-box">
                <h4>⚠️ 攻击危害</h4>
                <ul style="margin-left: 20px; color: #333;">
                    <?php if ($current_protocol === 'file'): ?>
                    <li><strong>敏感文件泄露：</strong>读取配置文件、密码文件、密钥文件</li>
                    <li><strong>源代码泄露：</strong>读取应用源代码，发现其他漏洞</li>
                    <li><strong>系统信息泄露：</strong>读取系统文件，了解系统配置</li>
                    <li><strong>环境变量泄露：</strong>读取/proc/self/environ获取环境变量</li>
                    <?php elseif ($current_protocol === 'dict'): ?>
                    <li><strong>内网服务探测：</strong>发现内网运行的服务</li>
                    <li><strong>端口扫描：</strong>探测端口开放情况</li>
                    <li><strong>服务识别：</strong>获取服务Banner和版本信息</li>
                    <li><strong>为后续攻击提供信息：</strong>为精确攻击提供情报支持</li>
                    <?php elseif ($current_protocol === 'gopher'): ?>
                    <li><strong>远程代码执行：</strong>攻击Redis、FastCGI等服务实现RCE</li>
                    <li><strong>数据库攻击：</strong>攻击MySQL、MongoDB等数据库</li>
                    <li><strong>内网服务控制：</strong>完全控制内网服务</li>
                    <li><strong>权限提升：</strong>从Web权限提升到系统权限</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        function fillUrl(url) {
            document.querySelector('input[name="url"]').value = url;
        }
        
        function encodeGopher() {
            const rawData = document.getElementById('raw_data').value;
            const lines = rawData.split('\n');
            let encoded = '';
            
            lines.forEach(line => {
                // Redis协议编码
                if (line.trim().toUpperCase() === 'INFO') {
                    encoded += 'INFO%0d%0a';
                } else {
                    // 简单的URL编码
                    encoded += encodeURIComponent(line) + '%0d%0a';
                }
            });
            
            const gopherUrl = 'gopher://127.0.0.1:6379/_' + encoded;
            
            document.getElementById('gopher_result').innerHTML = 
                '<div class="alert alert-success"><strong>Gopher URL：</strong><br><code style="word-break: break-all;">' + 
                gopherUrl + '</code></div>';
            
            // 自动填充到输入框
            document.querySelector('input[name="url"]').value = gopherUrl;
        }
    </script>
</body>
</html>