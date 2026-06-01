<?php
/**
 * Web安全漏洞演示平台
 * 作者: guojin
 * 说明: 本平台仅用于安全教学目的，请勿用于非法用途
 */
session_start();

// 检查是否已登录CSRF模块
$is_logged_in = isset($_SESSION['user']) && !empty($_SESSION['user']);
$current_user = $is_logged_in ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF & SSRF 漏洞演示平台</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            color: #856404;
        }
        .warning-box h3 {
            margin-bottom: 10px;
        }
        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        .module-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        .module-card:hover {
            transform: translateY(-5px);
        }
        .module-header {
            padding: 25px;
            color: white;
        }
        .csrf-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .ssrf-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .module-header h2 {
            font-size: 1.8em;
            margin-bottom: 10px;
        }
        .module-header p {
            opacity: 0.9;
        }
        .module-content {
            padding: 25px;
        }
        .module-content h3 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .module-content ul {
            list-style: none;
            margin-bottom: 20px;
        }
        .module-content li {
            padding: 8px 0;
            color: #666;
        }
        .module-content li:before {
            content: "▸ ";
            color: #667eea;
            font-weight: bold;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .btn-danger:hover {
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }
        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .btn-info:hover {
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            opacity: 0.8;
        }
        .btn-reset {
            padding: 15px 30px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-reset:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        .btn-reset:active {
            transform: translateY(-1px);
        }
        .reset-success {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            color: #155724;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 CSRF & SSRF 漏洞演示平台</h1>
            <p>了解常见Web安全漏洞原理，学习如何防范攻击</p>
        </div>

        <div class="warning-box">
            <h3>⚠️ 重要提示</h3>
            <p>本平台仅用于安全教学和研究目的。请勿将所学知识用于非法攻击行为。未经授权的渗透测试是违法行为。</p>
        </div>

        <div style="text-align: center; margin-bottom: 30px;">
            <button onclick="resetEnvironment()" class="btn-reset">🔄 初始化演示环境</button>
            <p style="color: white; margin-top: 10px; font-size: 0.9em; opacity: 0.9;">
                点击此按钮将重置所有演示数据到初始状态（包括银行余额、论坛用户、密码等）
            </p>
        </div>

        <div class="modules">
            <!-- CSRF 入门教学 -->
            <div class="module-card">
                <div class="module-header csrf-header">
                    <h2>🎓 CSRF - 入门教学</h2>
                    <p>跨站请求伪造基础演示</p>
                </div>
                <div class="module-content">
                    <h3>漏洞说明</h3>
                    <p style="color: #666; margin-bottom: 15px;">
                        CSRF攻击者诱导用户在已登录的网站上执行非预期操作，如修改个人信息等。
                    </p>
                    <h3>演示场景</h3>
                    <ul>
                        <li>用户登录系统</li>
                        <li>漏洞版本：无CSRF Token保护</li>
                        <li>安全版本：有CSRF Token保护</li>
                        <li>攻击演示：对比攻击效果</li>
                    </ul>
                    <div class="btn-group">
                        <a href="csrf/login.php" class="btn btn-danger">开始演示</a>
                        <a href="csrf/attack.html" class="btn btn-primary">攻击演示</a>
                    </div>
                </div>
            </div>

            <!-- SSRF 入门教学 -->
            <div class="module-card">
                <div class="module-header ssrf-header">
                    <h2>🎓 SSRF - 入门教学</h2>
                    <p>服务端请求伪造基础演示</p>
                </div>
                <div class="module-content">
                    <h3>漏洞说明</h3>
                    <p style="color: #666; margin-bottom: 15px;">
                        SSRF攻击者利用服务器发起请求，访问内部资源或外部系统，可能导致敏感信息泄露。
                    </p>
                    <h3>演示场景</h3>
                    <ul>
                        <li>URL图片获取功能</li>
                        <li>访问内网资源</li>
                        <li>读取本地文件</li>
                        <li>端口扫描探测</li>
                    </ul>
                    <div class="btn-group">
                        <a href="ssrf/index.php" class="btn btn-info">进入演示</a>
                    </div>
                </div>
            </div>

            <!-- CSRF 银行转账场景 -->
            <div class="module-card">
                <div class="module-header csrf-header">
                    <h2>🏦 CSRF - 银行转账</h2>
                    <p>拟真场景：模拟银行系统转账攻击</p>
                </div>
                <div class="module-content">
                    <h3>场景说明</h3>
                    <p style="color: #666; margin-bottom: 15px;">
                        攻击者构造恶意页面，诱导已登录用户点击"领取红包"按钮，实际执行转账操作。
                    </p>
                    <h3>演示流程</h3>
                    <ul>
                        <li>登录银行系统（张三/123456）</li>
                        <li>查看漏洞版本转账功能</li>
                        <li>打开攻击页面体验攻击</li>
                        <li>对比安全版本的防护效果</li>
                    </ul>
                    <div class="btn-group">
                        <a href="csrf/bank/index.php" class="btn btn-danger">银行系统</a>
                        <a href="csrf/bank/attack.html" class="btn btn-primary">攻击页面</a>
                    </div>
                </div>
            </div>

            <!-- CSRF 论坛密码修改场景 -->
            <div class="module-card">
                <div class="module-header csrf-header">
                    <h2>💬 CSRF - 论坛密码修改</h2>
                    <p>拟真场景：模拟论坛系统修改密码攻击</p>
                </div>
                <div class="module-content">
                    <h3>场景说明</h3>
                    <p style="color: #666; margin-bottom: 15px;">
                        攻击者伪装成"系统安全升级通知"，诱导用户点击按钮，实际修改用户密码。
                    </p>
                    <h3>演示流程</h3>
                    <ul>
                        <li>登录论坛系统（xiaoming/123456）</li>
                        <li>查看漏洞版本密码修改</li>
                        <li>打开攻击页面体验攻击</li>
                        <li>对比安全版本的防护效果</li>
                    </ul>
                    <div class="btn-group">
                        <a href="csrf/forum/index.php" class="btn btn-danger">论坛系统</a>
                        <a href="csrf/forum/attack.html" class="btn btn-primary">攻击页面</a>
                    </div>
                </div>
            </div>

            <!-- SSRF 拟真场景 -->
            <div class="module-card">
                <div class="module-header ssrf-header">
                    <h2>🖼️ SSRF - 图片代理服务</h2>
                    <p>拟真场景：模拟图片代理/URL预览功能</p>
                </div>
                <div class="module-content">
                    <h3>场景说明</h3>
                    <p style="color: #666; margin-bottom: 15px;">
                        模拟社交平台的URL预览功能，攻击者可利用SSRF访问内网服务、读取本地文件。
                    </p>
                    <h3>演示场景</h3>
                    <ul>
                        <li>图片代理服务</li>
                        <li>访问内网资源（127.0.0.1）</li>
                        <li>读取本地文件（file://）</li>
                        <li>探测内网端口服务</li>
                    </ul>
                    <div class="btn-group">
                        <a href="ssrf/demo.php" class="btn btn-info">进入演示</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>© 2026 CSRF & SSRF 漏洞演示平台 | 仅供安全教学使用</p>
        </div>
    </div>

    <script>
        function resetEnvironment() {
            if (confirm('确定要重置演示环境吗？这将清除所有数据并恢复到初始状态。')) {
                fetch('reset.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 显示成功消息
                            const successDiv = document.createElement('div');
                            successDiv.className = 'reset-success';
                            successDiv.innerHTML = `
                                <h3 style="margin-bottom: 10px;">✅ 初始化成功！</h3>
                                <p>演示环境已重置到初始状态</p>
                                <p style="margin-top: 10px; font-size: 0.9em;">
                                    银行用户：${data.data.bank_users} 个<br>
                                    论坛用户：${data.data.forum_users} 个<br>
                                    CSRF用户：${data.data.csrf_users} 个
                                </p>
                            `;
                            
                            // 插入到warning-box后面
                            const warningBox = document.querySelector('.warning-box');
                            warningBox.insertAdjacentElement('afterend', successDiv);
                            
                            // 3秒后自动消失
                            setTimeout(() => {
                                successDiv.style.opacity = '0';
                                setTimeout(() => successDiv.remove(), 500);
                            }, 3000);
                        } else {
                            alert('初始化失败：' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('初始化失败，请稍后重试');
                        console.error('Error:', error);
                    });
            }
        }
    </script>
</body>
</html>