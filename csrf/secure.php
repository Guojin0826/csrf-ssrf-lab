<?php
/**
 * CSRF漏洞演示 - 安全版本
 * 此页面有CSRF Token防护，可以防御攻击
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

// 获取CSRF Token
$csrf_token = getCSRFToken();

// 处理表单提交（有CSRF验证）
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ 安全代码：验证CSRF Token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = '🚫 CSRF Token 验证失败！检测到跨站请求伪造攻击！';
    } else {
        // Token验证通过，处理请求
        $user['email'] = $_POST['email'] ?? $user['email'];
        $user['nickname'] = $_POST['nickname'] ?? $user['nickname'];
        $user['phone'] = $_POST['phone'] ?? $user['phone'];
        
        // 保存用户数据
        $users[$user_id] = $user;
        saveUsers($users);
        
        $message = '✅ 信息修改成功！您的数据是安全的。';
        
        // 生成新的Token（可选，增加安全性）
        $csrf_token = generateCSRFToken();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF漏洞演示 - 安全版本</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .secure-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
        }
        .safe-box {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .safe-box h3 {
            color: #155724;
            margin-top: 0;
        }
        .safe-box ul {
            color: #155724;
            margin: 10px 0;
            padding-left: 20px;
        }
        .safe-box li {
            margin: 5px 0;
        }
        .token-display {
            background: #e7f3ff;
            border: 2px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .token-display code {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 3px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            color: #d63384;
        }
        .success-msg {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .error-msg {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="secure-banner">
        ✅ 这是安全版本 - 有CSRF Token防护，可以防御攻击！
    </div>
    
    <div class="nav-bar">
        <div class="logo">🔐 Web安全漏洞演示平台</div>
        <div class="nav-links">
            <a href="../index.php">🏠 返回主页</a>
            <a href="vulnerable.php">⚠️ 漏洞版本</a>
            <a href="secure.php" class="active">✅ 安全版本</a>
            <a href="attack.html">🔥 攻击演示</a>
            <a href="logout.php">🚪 退出</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>✅ 安全版本 - 有CSRF防护</h1>
            <p>此页面演示使用CSRF Token保护的表单，可以有效防御跨站请求伪造攻击</p>
        </div>

        <div class="safe-box">
            <h3>🛡️ 安全措施</h3>
            <ul>
                <li><strong>CSRF Token：</strong>每个表单都包含唯一的验证令牌</li>
                <li><strong>Token验证：</strong>服务器验证提交的Token是否与Session中的匹配</li>
                <li><strong>无法伪造：</strong>攻击者无法获取用户的Token，无法构造有效请求</li>
                <li><strong>攻击失败：</strong>即使攻击者诱导用户点击恶意链接，请求也会被拒绝</li>
            </ul>
        </div>

        <?php if ($message): ?>
        <div class="success-msg">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error-msg">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
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
                    <!-- ✅ CSRF Token 字段 -->
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                    
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
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        ✅ 提交修改（安全）
                    </button>
                </form>

                <div class="token-display">
                    <strong>🔐 当前CSRF Token：</strong><br>
                    <code><?php echo h($csrf_token); ?></code>
                    <p style="margin: 10px 0 0 0; color: #155724; font-size: 0.9em;">
                        💡 此Token存储在您的Session中，攻击者无法获取。每次提交表单时都会验证此Token。
                    </p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>🔍 安全代码分析</h2>
            </div>
            <div class="card-body">
                <div class="code-block">
                    <h4>✅ 安全代码（前端）：</h4>
                    <pre>&lt;form method="POST" action=""&gt;
    <span style="color: #28a745;">&lt;!-- 包含 CSRF Token 字段 --&gt;</span>
    &lt;input type="hidden" name="csrf_token" value="<span style="color: #d63384;">&lt;?php echo $csrf_token; ?&gt;</span>"&gt;
    &lt;input type="email" name="email" value="..."&gt;
    &lt;input type="text" name="nickname" value="..."&gt;
    &lt;input type="text" name="phone" value="..."&gt;
    &lt;button type="submit"&gt;提交&lt;/button&gt;
&lt;/form&gt;</pre>
                </div>

                <div class="code-block">
                    <h4>✅ 安全代码（后端）：</h4>
                    <pre>if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    <span style="color: #28a745;">// 验证 CSRF Token</span>
    if (!isset($_POST['csrf_token']) || 
        !verifyCSRFToken($_POST['csrf_token'])) {
        <span style="color: #dc3545;">die('CSRF Token 验证失败！');</span>
    }
    
    <span style="color: #28a745;">// Token 验证通过，处理请求</span>
    $user['email'] = $_POST['email'];
    $user['nickname'] = $_POST['nickname'];
    $user['phone'] = $_POST['phone'];
    saveUsers($users);
}</pre>
                </div>

                <div class="warning-box" style="margin-top: 20px; background: #d4edda; border-color: #28a745; color: #155724;">
                    <strong>💡 提示：</strong>请访问 <a href="attack.html" style="color: #155724; font-weight: bold;">攻击演示页面</a> 
                    尝试攻击此页面，您会发现攻击会被成功拦截！
                </div>
            </div>
        </div>
    </div>
</body>
</html>