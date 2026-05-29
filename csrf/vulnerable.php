<?php
/**
 * CSRF漏洞演示 - 漏洞版本
 * 此页面没有CSRF防护，容易被攻击
 */

session_start();
require_once 'config.php';

// 检查登录状态
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 加载用户数据
$users = loadUsers();
$user_id = $_SESSION['user_id'];

if (!isset($users[$user_id])) {
    header('Location: login.php');
    exit;
}

$user = $users[$user_id];

// 处理表单提交（无CSRF验证）
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ⚠️ 漏洞代码：直接处理请求，没有任何验证
    $user['email'] = $_POST['email'] ?? $user['email'];
    $user['nickname'] = $_POST['nickname'] ?? $user['nickname'];
    $user['phone'] = $_POST['phone'] ?? $user['phone'];
    
    // 保存用户数据
    $users[$user_id] = $user;
    saveUsers($users);
    
    $message = '✅ 信息修改成功！（但这是不安全的）';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF漏洞演示 - 漏洞版本</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .vulnerable-banner {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
        }
        .danger-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .danger-box h3 {
            color: #856404;
            margin-top: 0;
        }
        .danger-box ul {
            color: #856404;
            margin: 10px 0;
            padding-left: 20px;
        }
        .danger-box li {
            margin: 5px 0;
        }
        .no-token {
            background: #f8d7da;
            border: 2px dashed #dc3545;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
        }
        .no-token strong {
            color: #dc3545;
            font-size: 1.2em;
        }
        .success-msg {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="vulnerable-banner">
        ⚠️ 这是漏洞版本 - 没有CSRF防护，容易被攻击！
    </div>
    
    <div class="nav-bar">
        <div class="logo">🔐 Web安全漏洞演示平台</div>
        <div class="nav-links">
            <a href="../index.php">🏠 返回主页</a>
            <a href="vulnerable.php" class="active">⚠️ 漏洞版本</a>
            <a href="secure.php">✅ 安全版本</a>
            <a href="attack.html">🔥 攻击演示</a>
            <a href="logout.php">🚪 退出</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>⚠️ 漏洞版本 - 无CSRF防护</h1>
            <p>此页面演示没有CSRF Token保护的表单，攻击者可以轻易伪造请求</p>
        </div>

        <div class="danger-box">
            <h3>🚨 漏洞说明</h3>
            <ul>
                <li><strong>没有CSRF Token：</strong>表单中没有包含任何验证令牌</li>
                <li><strong>无来源验证：</strong>服务器不检查请求来源</li>
                <li><strong>可被伪造：</strong>攻击者可以构造恶意页面自动提交表单</li>
                <li><strong>用户无感知：</strong>用户完全不知情的情况下信息被修改</li>
            </ul>
        </div>

        <?php if ($message): ?>
        <div class="success-msg">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                <h2>📝 修改个人信息</h2>
            </div>
            <div class="card-body">
                <div class="user-info">
                    <p>当前用户：<strong><?php echo h($user['username']); ?></strong></p>
                    <p>邮箱：<strong><?php echo h($user['email']); ?></strong></p>
                    <p>昵称：<strong><?php echo h($user['nickname']); ?></strong></p>
                    <p>电话：<strong><?php echo h($user['phone']); ?></strong></p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>📧 邮箱</label>
                        <input type="email" name="email" value="<?php echo h($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>👤 昵称</label>
                        <input type="text" name="nickname" value="<?php echo h($user['nickname']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>📱 电话</label>
                        <input type="text" name="phone" value="<?php echo h($user['phone']); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                        ⚠️ 提交修改（不安全）
                    </button>
                </form>

                <div class="no-token">
                    <strong>⚠️ 此表单没有CSRF Token！</strong>
                    <p style="margin: 10px 0 0 0; color: #721c24;">
                        攻击者可以轻易伪造此请求，在您不知情的情况下修改您的信息
                    </p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>🔍 表单代码分析</h2>
            </div>
            <div class="card-body">
                <div class="code-block">
                    <h4>❌ 漏洞代码（前端）：</h4>
                    <pre>&lt;form method="POST" action=""&gt;
    <span style="color: #dc3545;">&lt;!-- 没有 CSRF Token 字段 --&gt;</span>
    &lt;input type="email" name="email" value="..."&gt;
    &lt;input type="text" name="nickname" value="..."&gt;
    &lt;input type="text" name="phone" value="..."&gt;
    &lt;button type="submit"&gt;提交&lt;/button&gt;
&lt;/form&gt;</pre>
                </div>

                <div class="code-block">
                    <h4>❌ 漏洞代码（后端）：</h4>
                    <pre>if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    <span style="color: #dc3545;">// 直接处理请求，没有任何验证！</span>
    $user['email'] = $_POST['email'];
    $user['nickname'] = $_POST['nickname'];
    $user['phone'] = $_POST['phone'];
    saveUsers($users);
}</pre>
                </div>

                <div class="warning-box" style="margin-top: 20px;">
                    <strong>💡 提示：</strong>请访问 <a href="attack.html" style="color: #667eea; font-weight: bold;">攻击演示页面</a> 
                    查看如何利用此漏洞修改您的信息
                </div>
            </div>
        </div>
    </div>
</body>
</html>