<?php
/**
 * 拟真场景3：PDF导出服务
 * 模拟wkhtmltopdf、PhantomJS等PDF生成工具
 */
session_start();

$current_mode = isset($_GET['mode']) ? $_GET['mode'] : 'vulnerable';
$result = '';
$error = '';
$success = '';

// 安全URL验证
function isSafeUrl($url) {
    $parsedUrl = parse_url($url);
    if (!$parsedUrl || !isset($parsedUrl['scheme'])) {
        return false;
    }
    
    $scheme = strtolower($parsedUrl['scheme']);
    if (!in_array($scheme, ['http', 'https'])) {
        return false;
    }
    
    $host = $parsedUrl['host'] ?? '';
    $dangerous_hosts = ['localhost', '127.0.0.1', '0.0.0.0', '169.254.169.254'];
    if (in_array($host, $dangerous_hosts)) {
        return false;
    }
    
    $ip = gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    
    return true;
}

// 处理请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['html_url'])) {
    $url = trim($_POST['html_url']);
    
    if (empty($url)) {
        $error = '请输入HTML URL';
    } else {
        if ($current_mode === 'vulnerable') {
            // 漏洞版本
            $parsedUrl = parse_url($url);
            $scheme = strtolower($parsedUrl['scheme'] ?? 'http');
            
            if ($scheme === 'file') {
                $file_path = $parsedUrl['path'] ?? '';
                $result = "模拟执行: wkhtmltopdf $file_path output.pdf\n\n读取文件内容:\n[敏感文件内容]\n\nPDF生成成功！\n";
                $success = "✅ PDF生成成功！";
            } else {
                $result = "模拟执行: wkhtmltopdf $url output.pdf\n\n访问URL并转换为PDF...\n\nPDF生成成功！\n文件大小: 1.2MB\n";
                $success = "✅ PDF生成成功！";
            }
        } else {
            // 安全版本
            if (!isSafeUrl($url)) {
                $error = '❌ URL验证失败：禁止访问内网地址或使用file协议';
            } else {
                $error = '✅ 安全版本：URL已通过验证';
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
    <title>PDF Generator - 在线PDF导出服务</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .header { background: white; border-bottom: 1px solid #e0e0e0; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: #333; font-size: 1.5em; }
        .nav-links { display: flex; gap: 10px; }
        .nav-links a { padding: 8px 16px; background: #f8f9fa; color: #667eea; text-decoration: none; border-radius: 6px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .card-body { padding: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 1em; }
        .btn { padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .result-box { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 6px; font-family: monospace; margin-top: 20px; white-space: pre-wrap; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; }
        .alert-danger { background: #fef0f0; color: #f56c6c; border: 1px solid #fde2e2; }
        .alert-success { background: #f0f9ff; color: #67c23a; border: 1px solid #c2e7b0; }
        .tips-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .tips-box h4 { color: #856404; margin-bottom: 10px; }
        .tips-box ul { margin-left: 20px; color: #856404; }
        .mode-toggle { display: flex; gap: 10px; margin-bottom: 20px; }
        .mode-toggle button { flex: 1; padding: 10px; border: 2px solid #e0e0e0; background: white; border-radius: 6px; cursor: pointer; }
        .mode-toggle button.active { background: #667eea; color: white; border-color: #667eea; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📄 PDF Generator - 在线PDF导出服务</h1>
        <div class="nav-links">
            <a href="index.php">🏠 场景列表</a>
            <a href="../../index.php">🏠 返回主页</a>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>📑 HTML转PDF</h2>
                <p>输入HTML页面URL，自动转换为PDF文档</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <div class="mode-toggle">
                    <button onclick="location.href='?mode=vulnerable'" class="<?php echo $current_mode === 'vulnerable' ? 'active' : ''; ?>">⚠️ 漏洞版本</button>
                    <button onclick="location.href='?mode=safe'" class="<?php echo $current_mode === 'safe' ? 'active' : ''; ?>">✅ 安全版本</button>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label>HTML页面URL</label>
                        <input type="text" name="html_url" placeholder="https://example.com/page.html" value="<?php echo htmlspecialchars($_POST['html_url'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn">生成PDF</button>
                </form>
                
                <?php if ($result): ?>
                <div class="result-box"><?php echo htmlspecialchars($result); ?></div>
                <?php endif; ?>
                
                <div class="tips-box">
                    <h4>💡 SSRF攻击Payload</h4>
                    <ul>
                        <li>file:///etc/passwd - 读取系统文件</li>
                        <li>file:///var/www/html/config.php - 读取应用配置</li>
                        <li>http://127.0.0.1:8080/admin - 访问内网管理后台</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>