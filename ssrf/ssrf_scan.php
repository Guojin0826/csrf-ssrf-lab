<?php
/**
 * SSRF内网探测演示 - 通过SSRF探测内网服务
 * 展示如何利用SSRF漏洞探测内网主机、端口和服务
 */
session_start();

// 检查登录
if (!isset($_SESSION['ssrf_demo_user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['ssrf_demo_user'];
$current_mode = isset($_GET['mode']) ? $_GET['mode'] : 'vulnerable';
$scan_result = '';
$error = '';
$success = '';

// 模拟内网主机和服务
$internal_hosts = [
    '192.168.1.1' => ['hostname' => '路由器/网关', 'ports' => [80, 443], 'services' => [80 => ['name' => 'HTTP', 'banner' => 'Router Web Interface'], 443 => ['name' => 'HTTPS', 'banner' => 'Router Admin Panel']]],
    '192.168.1.10' => ['hostname' => 'Web服务器', 'ports' => [80, 443, 8080], 'services' => [80 => ['name' => 'HTTP', 'banner' => 'nginx/1.18.0'], 443 => ['name' => 'HTTPS', 'banner' => 'nginx/1.18.0'], 8080 => ['name' => 'HTTP-Alt', 'banner' => 'Apache Tomcat/9.0.50']]],
    '192.168.1.20' => ['hostname' => '数据库服务器', 'ports' => [3306, 33060], 'services' => [3306 => ['name' => 'MySQL', 'banner' => 'MySQL 5.7.35'], 33060 => ['name' => 'MySQL-X', 'banner' => 'MySQL X Protocol']]],
    '192.168.1.30' => ['hostname' => 'Redis缓存服务器', 'ports' => [6379], 'services' => [6379 => ['name' => 'Redis', 'banner' => 'Redis server v=6.2.6']]],
    '192.168.1.40' => ['hostname' => '文件服务器', 'ports' => [21, 22, 445], 'services' => [21 => ['name' => 'FTP', 'banner' => 'vsftpd 3.0.3'], 22 => ['name' => 'SSH', 'banner' => 'OpenSSH 8.2p1'], 445 => ['name' => 'SMB', 'banner' => 'Samba 4.13.13']]],
    '192.168.1.50' => ['hostname' => '管理后台', 'ports' => [80, 443, 8443], 'services' => [80 => ['name' => 'HTTP', 'banner' => 'Admin Panel'], 443 => ['name' => 'HTTPS', 'banner' => 'Admin Panel SSL'], 8443 => ['name' => 'HTTPS-Alt', 'banner' => 'Management Console']]],
    '192.168.1.100' => ['hostname' => '应用服务器', 'ports' => [80, 443, 3000, 5000], 'services' => [80 => ['name' => 'HTTP', 'banner' => 'nginx/1.18.0'], 443 => ['name' => 'HTTPS', 'banner' => 'nginx/1.18.0'], 3000 => ['name' => 'Node.js', 'banner' => 'Express.js'], 5000 => ['name' => 'Python', 'banner' => 'Flask/2.0.1']]]
];

// 处理端口扫描
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'scan') {
        $target_ip = $_POST['target_ip'] ?? '';
        $start_port = intval($_POST['start_port'] ?? 1);
        $end_port = intval($_POST['end_port'] ?? 1024);
        
        if (empty($target_ip)) {
            $error = '请选择目标IP';
        } else {
            if ($current_mode === 'vulnerable') {
                if (isset($internal_hosts[$target_ip])) {
                    $host_info = $internal_hosts[$target_ip];
                    $scan_result = "=== SSRF内网探测结果 ===\n\n目标主机: {$target_ip} ({$host_info['hostname']})\n扫描端口范围: {$start_port}-{$end_port}\n扫描方法: 通过SSRF漏洞探测\n\n开放端口:\n" . str_repeat("-", 60) . "\n";
                    
                    foreach ($host_info['services'] as $port => $service) {
                        if ($port >= $start_port && $port <= $end_port) {
                            $scan_result .= "端口 {$port}: {$service['name']} - {$service['banner']}\n";
                        }
                    }
                    
                    $scan_result .= str_repeat("-", 60) . "\n💡 发现内网服务，可通过SSRF进一步攻击！\n";
                    $success = "✅ 扫描完成！发现 " . count($host_info['services']) . " 个开放端口";
                }
            } else {
                $error = '❌ 安全版本：已阻止对内网地址的扫描请求';
            }
        }
    } elseif ($_POST['action'] === 'probe') {
        $probe_url = $_POST['probe_url'] ?? '';
        
        if (empty($probe_url)) {
            $error = '请输入探测URL';
        } else {
            if ($current_mode === 'vulnerable') {
                $scan_result = "=== SSRF服务探测 ===\n\n探测URL: {$probe_url}\n\n服务状态: ● 开放\n响应内容: [模拟响应数据]\n";
                $success = "✅ 探测完成！";
            } else {
                $error = '❌ 安全版本：已阻止对内网地址的探测请求';
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
    <title>SSRF内网探测</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .navbar { background: white; padding: 15px 30px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: #11998e; font-size: 1.3em; }
        .nav-links { display: flex; gap: 10px; }
        .nav-links a { padding: 8px 16px; background: #f8f9fa; color: #11998e; text-decoration: none; border-radius: 6px; }
        .content { padding: 30px; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #333; margin-bottom: 15px; font-size: 1.3em; border-left: 4px solid #11998e; padding-left: 10px; }
        .alert { padding: 12px 15px; border-radius: 6px; margin-bottom: 15px; }
        .alert-danger { background: #fef0f0; color: #f56c6c; border: 1px solid #fde2e2; }
        .alert-success { background: #f0f9ff; color: #67c23a; border: 1px solid #c2e7b0; }
        .alert-info { background: #ecf5ff; color: #409eff; border: 1px solid #d9ecff; }
        .mode-tabs { display: flex; gap: 10px; margin-bottom: 20px; padding: 20px 30px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0; }
        .mode-tab { padding: 10px 20px; background: white; border: 2px solid #e0e0e0; border-radius: 6px; cursor: pointer; text-decoration: none; color: #333; }
        .mode-tab.active.vulnerable { background: #f56c6c; color: white; border-color: #f56c6c; }
        .mode-tab.active.safe { background: #67c23a; color: white; border-color: #67c23a; }
        .demo-box { background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .demo-box h3 { color: #11998e; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 1em; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; background: #11998e; color: white; }
        .result-box { background: #2d2d2d; border-radius: 8px; padding: 20px; margin: 20px 0; color: #f8f8f2; font-family: monospace; white-space: pre-wrap; }
        .host-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px; margin: 20px 0; }
        .host-card { background: white; border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; }
        .host-card h4 { color: #11998e; margin-bottom: 10px; }
        .host-card p { color: #666; font-size: 0.9em; margin: 5px 0; }
        .port-badge { display: inline-block; padding: 3px 8px; background: #f0f9ff; color: #11998e; border-radius: 3px; font-size: 0.85em; margin: 2px; }
        .flow-step { display: flex; align-items: flex-start; margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #11998e; }
        .flow-step .step-number { width: 40px; height: 40px; background: #11998e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; flex-shrink: 0; }
        .flow-step .step-content h4 { color: #11998e; margin-bottom: 5px; }
        .flow-step .step-content p { color: #666; font-size: 0.9em; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h1>🔍 SSRF内网探测 - 通过SSRF探测内网服务</h1>
            <div class="nav-links">
                <a href="index.php">📚 返回首页</a>
                <a href="../index.php">🏠 返回主页</a>
            </div>
        </div>
        
        <div class="mode-tabs">
            <a href="?mode=vulnerable" class="mode-tab vulnerable <?php echo $current_mode === 'vulnerable' ? 'active' : ''; ?>">⚠️ 漏洞版本</a>
            <a href="?mode=safe" class="mode-tab safe <?php echo $current_mode === 'safe' ? 'active' : ''; ?>">✅ 安全版本</a>
        </div>
        
        <div class="content">
            <div class="alert alert-info">
                <strong>💡 SSRF与内网探测的关系：</strong><br>
                SSRF（服务器端请求伪造）可以让攻击者以服务器的身份访问内网资源，从而探测内网拓扑、扫描端口、识别服务。这是SSRF最常见的攻击方式之一。
            </div>
            
            <!-- 端口扫描原理说明 -->
            <div class="section">
                <h2>📖 端口扫描如何通过SSRF实现</h2>
                
                <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <h3 style="color: #11998e; margin-bottom: 15px;">🔍 核心原理</h3>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 15px;">
                        传统端口扫描需要攻击者直接连接目标端口，但在内网环境中，攻击者无法直接访问内网主机。
                        <strong style="color: #f56c6c;">SSRF漏洞让攻击者可以利用服务器作为跳板，间接探测内网端口。</strong>
                    </p>
                    
                    <div style="background: white; border-left: 4px solid #11998e; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <h4 style="color: #11998e; margin-bottom: 10px;">攻击路径对比：</h4>
                        <p style="color: #666; margin: 5px 0;"><strong>传统扫描：</strong>攻击者 → 目标端口（直接连接）</p>
                        <p style="color: #666; margin: 5px 0;"><strong>SSRF扫描：</strong>攻击者 → 漏洞服务器 → 内网目标端口（间接探测）</p>
                    </div>
                </div>
                
                <h3 style="color: #11998e; margin: 20px 0 10px 0;">⚙️ 实现方法</h3>
                
                <div style="background: white; border: 2px solid #e0e0e0; border-radius: 8px; padding: 20px; margin: 15px 0;">
                    <h4 style="color: #333; margin-bottom: 10px;">方法1：HTTP请求探测</h4>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 10px;">
                        通过SSRF发送HTTP请求到目标IP的不同端口，根据响应判断端口是否开放：
                    </p>
                    <div style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 0.9em; margin: 10px 0;">
                        <div style="color: #67c23a;"># 探测192.168.1.10的80端口</div>
                        <div>http://192.168.1.10:80/</div>
                        <div style="margin-top: 10px; color: #67c23a;"># 探测192.168.1.10的3306端口</div>
                        <div>http://192.168.1.10:3306/</div>
                        <div style="margin-top: 10px; color: #67c23a;"># 探测192.168.1.10的6379端口</div>
                        <div>http://192.168.1.10:6379/</div>
                    </div>
                    <p style="color: #666; line-height: 1.8; margin-top: 10px;">
                        <strong>判断依据：</strong><br>
                        • 端口开放：返回HTTP响应或错误信息<br>
                        • 端口关闭：连接超时或拒绝连接<br>
                        • 不同服务：响应内容不同（如HTTP返回HTML，MySQL返回错误包）
                    </p>
                </div>
                
                <div style="background: white; border: 2px solid #e0e0e0; border-radius: 8px; padding: 20px; margin: 15px 0;">
                    <h4 style="color: #333; margin-bottom: 10px;">方法2：响应时间判断</h4>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 10px;">
                        通过请求不同端口的响应时间差异判断端口状态：
                    </p>
                    <div style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 0.9em; margin: 10px 0;">
                        <div style="color: #67c23a;"># 开放端口：快速响应（几十毫秒）</div>
                        <div>请求: http://192.168.1.10:80/</div>
                        <div>响应时间: 0.023秒</div>
                        <div style="margin-top: 10px; color: #f56c6c;"># 关闭端口：超时或拒绝（几秒）</div>
                        <div>请求: http://192.168.1.10:9999/</div>
                        <div>响应时间: 10.001秒（超时）</div>
                    </div>
                </div>
                
                <div style="background: white; border: 2px solid #e0e0e0; border-radius: 8px; padding: 20px; margin: 15px 0;">
                    <h4 style="color: #333; margin-bottom: 10px;">方法3：Banner识别</h4>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 10px;">
                        通过服务返回的Banner信息识别服务类型和版本：
                    </p>
                    <div style="background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 0.9em; margin: 10px 0;">
                        <div style="color: #67c23a;"># HTTP服务Banner</div>
                        <div>HTTP/1.1 200 OK</div>
                        <div>Server: nginx/1.18.0</div>
                        <div style="margin-top: 10px; color: #67c23a;"># MySQL服务Banner</div>
                        <div>J\x00\x00\x00\x0a5.7.35-0ubuntu0.20.04.1\x00</div>
                        <div style="margin-top: 10px; color: #67c23a;"># Redis服务Banner</div>
                        <div>-NOAUTH Authentication required</div>
                    </div>
                </div>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 4px;">
                    <h4 style="color: #856404; margin-bottom: 10px;">⚠️ 为什么SSRF扫描更危险？</h4>
                    <ul style="margin-left: 20px; color: #856404; line-height: 1.8;">
                        <li><strong>绕过防火墙：</strong>请求从服务器发出，绕过边界防火墙</li>
                        <li><strong>访问内网：</strong>可以探测内网中的所有主机和服务</li>
                        <li><strong>难以检测：</strong>看起来是正常的服务器请求</li>
                        <li><strong>权限更高：</strong>服务器可能有更高的内网访问权限</li>
                        <li><strong>批量探测：</strong>可以快速扫描整个内网段</li>
                    </ul>
                </div>
            </div>
            
            <div class="section">
                <h2>🎯 SSRF内网探测流程</h2>
                <div class="flow-step"><div class="step-number">1</div><div class="step-content"><h4>发现SSRF漏洞点</h4><p>在Web应用中找到可以控制URL参数的功能点（如图片加载、URL预览、Webhook等）</p></div></div>
                <div class="flow-step"><div class="step-number">2</div><div class="step-content"><h4>探测内网主机</h4><p>构造SSRF请求，探测内网IP段（如192.168.1.0/24），发现存活主机</p></div></div>
                <div class="flow-step"><div class="step-number">3</div><div class="step-content"><h4>扫描开放端口</h4><p>对发现的主机进行端口扫描，识别开放的服务端口（如80、3306、6379等）</p></div></div>
                <div class="flow-step"><div class="step-number">4</div><div class="step-content"><h4>识别服务类型</h4><p>通过Banner信息识别服务类型和版本，为后续攻击做准备</p></div></div>
                <div class="flow-step"><div class="step-number">5</div><div class="step-content"><h4>进一步攻击</h4><p>利用发现的服务漏洞进行攻击，如Redis未授权访问、MySQL弱口令等</p></div></div>
            </div>
            
            <div class="section">
                <h2>🗺️ 模拟内网拓扑</h2>
                <p>以下是模拟的内网环境，攻击者可以通过SSRF探测到这些信息：</p>
                <div class="host-grid">
                    <?php foreach ($internal_hosts as $ip => $host): ?>
                    <div class="host-card">
                        <h4><?php echo $ip; ?> - <?php echo $host['hostname']; ?></h4>
                        <p><strong>开放端口：</strong></p>
                        <div><?php foreach ($host['ports'] as $port): ?><span class="port-badge"><?php echo $port; ?></span><?php endforeach; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="demo-box">
                <h3>🔧 工具1：端口扫描</h3>
                <p>通过SSRF对内网主机进行端口扫描：</p>
                <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                <form method="POST" action="?mode=<?php echo $current_mode; ?>">
                    <input type="hidden" name="action" value="scan">
                    <div class="form-group">
                        <label>目标IP地址</label>
                        <select name="target_ip">
                            <option value="">-- 选择内网主机 --</option>
                            <?php foreach ($internal_hosts as $ip => $host): ?>
                            <option value="<?php echo $ip; ?>" <?php echo ($_POST['target_ip'] ?? '') === $ip ? 'selected' : ''; ?>><?php echo $ip; ?> (<?php echo $host['hostname']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group"><label>起始端口</label><input type="number" name="start_port" value="<?php echo $_POST['start_port'] ?? 1; ?>" min="1" max="65535"></div>
                        <div class="form-group"><label>结束端口</label><input type="number" name="end_port" value="<?php echo $_POST['end_port'] ?? 1024; ?>" min="1" max="65535"></div>
                    </div>
                    <button type="submit" class="btn">开始扫描</button>
                </form>
                <?php if ($scan_result && $_POST['action'] === 'scan'): ?><h4 style="margin-top: 20px; color: #333;">📊 扫描结果：</h4><div class="result-box"><?php echo htmlspecialchars($scan_result); ?></div><?php endif; ?>
            </div>
            
            <div class="demo-box">
                <h3>🔧 工具2：服务探测</h3>
                <p>通过SSRF探测特定服务的详细信息：</p>
                <form method="POST" action="?mode=<?php echo $current_mode; ?>">
                    <input type="hidden" name="action" value="probe">
                    <div class="form-group"><label>探测URL</label><input type="text" name="probe_url" placeholder="http://192.168.1.10:80" value="<?php echo htmlspecialchars($_POST['probe_url'] ?? ''); ?>"></div>
                    <button type="submit" class="btn">探测服务</button>
                </form>
                <?php if ($scan_result && $_POST['action'] === 'probe'): ?><h4 style="margin-top: 20px; color: #333;">📊 探测结果：</h4><div class="result-box"><?php echo htmlspecialchars($scan_result); ?></div><?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>