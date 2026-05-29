<?php
/**
 * SSRF拟真场景首页
 * 模拟真实环境中可能出现SSRF漏洞的6种场景
 */
session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSRF拟真场景 - 真实环境演示</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: white; padding: 20px 30px; border-bottom: 1px solid #e0e0e0; }
        .header h1 { color: #667eea; font-size: 1.8em; margin-bottom: 10px; }
        .header p { color: #666; font-size: 1em; }
        .nav-links { display: flex; gap: 10px; margin-top: 15px; }
        .nav-links a { padding: 8px 16px; background: #f8f9fa; color: #667eea; text-decoration: none; border-radius: 6px; transition: all 0.3s; }
        .nav-links a:hover { background: #667eea; color: white; }
        .content { padding: 30px; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #333; margin-bottom: 15px; font-size: 1.3em; border-left: 4px solid #667eea; padding-left: 10px; }
        .section p { color: #666; line-height: 1.8; margin-bottom: 10px; }
        .scenario-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        .scenario-card { background: white; border: 2px solid #e0e0e0; border-radius: 12px; padding: 20px; transition: all 0.3s; }
        .scenario-card:hover { border-color: #667eea; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2); transform: translateY(-2px); }
        .scenario-card h3 { color: #667eea; margin-bottom: 10px; font-size: 1.2em; }
        .scenario-card p { color: #666; font-size: 0.9em; margin-bottom: 15px; }
        .scenario-card .real-case { background: #fff3cd; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .scenario-card .real-case small { color: #856404; font-size: 0.85em; }
        .scenario-card .btn { display: block; width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; border-radius: 6px; text-decoration: none; transition: all 0.3s; }
        .scenario-card .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 SSRF拟真场景演示</h1>
            <p>模拟真实环境中可能出现SSRF漏洞的网页和功能，每个场景都基于真实案例设计</p>
            <div class="nav-links">
                <a href="../index.php">📚 返回SSRF入门</a>
                <a href="../../index.php">🏠 返回主页</a>
            </div>
        </div>
        
        <div class="content">
            <div class="alert alert-warning">
                <strong>⚠️ 教学提示：</strong>以下场景模拟真实环境中的SSRF漏洞，每个场景都有漏洞版本和安全版本，可用于对比学习。
            </div>
            
            <div class="section">
                <h2>🎮 拟真场景列表</h2>
                <p>点击下方卡片进入对应的拟真场景，体验真实环境中的SSRF漏洞：</p>
                
                <div class="scenario-grid">
                    <!-- 场景1：图片加载 -->
                    <div class="scenario-card">
                        <h3>📷 场景1：图片上传平台</h3>
                        <p>模拟WordPress、Discuz等CMS的远程图片上传功能，攻击者可通过图片URL读取本地文件或访问内网服务。</p>
                        <div class="real-case">
                            <small>真实案例：WordPress远程图片上传、Discuz远程附件、各类CMS系统</small>
                        </div>
                        <a href="image.php" class="btn">进入场景</a>
                    </div>
                    
                    <!-- 场景2：Webhook -->
                    <div class="scenario-card">
                        <h3>🔗 场景2：Webhook回调服务</h3>
                        <p>模拟GitHub、GitLab、Jenkins的Webhook功能，攻击者可探测内网Git服务、CI/CD系统等。</p>
                        <div class="real-case">
                            <small>真实案例：GitHub Webhooks、GitLab Webhooks、Jenkins触发器</small>
                        </div>
                        <a href="webhook.php" class="btn">进入场景</a>
                    </div>
                    
                    <!-- 场景3：PDF导出 -->
                    <div class="scenario-card">
                        <h3>📄 场景3：PDF导出服务</h3>
                        <p>模拟wkhtmltopdf、PhantomJS等PDF生成工具，攻击者可利用file协议读取本地敏感文件。</p>
                        <div class="real-case">
                            <small>真实案例：wkhtmltopdf、PhantomJS、Puppeteer、各类在线PDF工具</small>
                        </div>
                        <a href="pdf.php" class="btn">进入场景</a>
                    </div>
                    
                    <!-- 场景4：URL代理 -->
                    <div class="scenario-card">
                        <h3>🌐 场景4：URL代理服务</h3>
                        <p>模拟Facebook、Twitter、Slack的URL预览功能，攻击者可探测内网服务、获取云元数据。</p>
                        <div class="real-case">
                            <small>真实案例：Facebook链接预览、Twitter卡片、Slack URL展开</small>
                        </div>
                        <a href="proxy.php" class="btn">进入场景</a>
                    </div>
                    
                    <!-- 场景5：API转发 -->
                    <div class="scenario-card">
                        <h3>🔌 场景5：API请求转发</h3>
                        <p>模拟API Gateway、BFF层的请求转发功能，攻击者可访问内网微服务、读取服务配置。</p>
                        <div class="real-case">
                            <small>真实案例：API Gateway、BFF层、微服务间调用</small>
                        </div>
                        <a href="api.php" class="btn">进入场景</a>
                    </div>
                    
                    <!-- 场景6：缓存预加载 -->
                    <div class="scenario-card">
                        <h3>💾 场景6：缓存预加载服务</h3>
                        <p>模拟CDN缓存预热、Redis预加载功能，攻击者可探测内网拓扑、读取配置文件。</p>
                        <div class="real-case">
                            <small>真实案例：CDN缓存预热、Redis缓存预加载、页面预渲染</small>
                        </div>
                        <a href="cache.php" class="btn">进入场景</a>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <strong>💡 使用说明：</strong><br>
                1. 每个场景都有"漏洞版本"和"安全版本"两个模式，可切换对比<br>
                2. 漏洞版本可测试各种SSRF攻击Payload<br>
                3. 安全版本会验证URL，阻止危险请求<br>
                4. 建议先测试漏洞版本，再切换到安全版本对比效果
            </div>
        </div>
    </div>
</body>
</html>