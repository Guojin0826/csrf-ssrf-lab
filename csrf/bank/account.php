<?php
/**
 * 模拟银行系统 - 账户中心
 */
session_start();

// 检查登录状态
if (!isset($_SESSION['bank_logged_in']) || $_SESSION['bank_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['bank_user'];
$user = $_SESSION['bank_users'][$username];

$message = '';
$error = '';

// 生成CSRF Token
if (!isset($_SESSION['bank_csrf_token'])) {
    $_SESSION['bank_csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['bank_csrf_token'];

// 获取当前模式（漏洞版本/安全版本）
$mode = $_COOKIE['bank_mode'] ?? 'vulnerable';

// 处理转账
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'transfer') {
    $to_account = trim($_POST['to_account'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $remark = trim($_POST['remark'] ?? '');
    
    // 根据模式决定是否验证CSRF Token
    if ($mode === 'secure') {
        $token = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['bank_csrf_token']) || $token !== $_SESSION['bank_csrf_token']) {
            $error = '⚠️ CSRF Token验证失败！检测到跨站请求伪造攻击！';
        }
    }
    
    if (empty($error)) {
        // 验证金额
        if ($amount <= 0) {
            $error = '转账金额必须大于0';
        } elseif ($amount > $user['balance']) {
            $error = '余额不足';
        } else {
            // 查找目标账户
            $found = false;
            foreach ($_SESSION['bank_users'] as $uname => $uinfo) {
                if ($uinfo['account'] === $to_account && $uname !== $username) {
                    $found = true;
                    // 执行转账
                    $_SESSION['bank_users'][$username]['balance'] -= $amount;
                    $_SESSION['bank_users'][$uname]['balance'] += $amount;
                    
                    // 记录交易
                    if (!isset($_SESSION['bank_transactions'])) {
                        $_SESSION['bank_transactions'] = [];
                    }
                    $_SESSION['bank_transactions'][] = [
                        'from' => $username,
                        'to' => $uname,
                        'amount' => $amount,
                        'remark' => $remark,
                        'time' => date('Y-m-d H:i:s'),
                        'mode' => $mode
                    ];
                    
                    $modeText = $mode === 'secure' ? '安全版本' : '漏洞版本';
                    $message = "【{$modeText}】转账成功！已向账户 {$to_account} 转账 ¥" . number_format($amount, 2);
                    $user = $_SESSION['bank_users'][$username];
                    
                    if ($mode === 'secure') {
                        // 重新生成CSRF Token
                        $_SESSION['bank_csrf_token'] = bin2hex(random_bytes(32));
                        $csrf_token = $_SESSION['bank_csrf_token'];
                    }
                    break;
                }
            }
            
            if (!$found) {
                $error = '目标账户不存在';
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
    <title>安全银行 - 账户中心</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            color: #1e3c72;
            font-size: 1.3em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-bar .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .nav-bar .nav-links a {
            color: #666;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .nav-bar .nav-links a:hover {
            background: #1e3c72;
            color: white;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .balance-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .balance-card .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .balance-card .avatar {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
        }
        .balance-card .user-name {
            font-size: 1.4em;
            font-weight: bold;
        }
        .balance-card .user-account {
            opacity: 0.9;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .balance-card .balance-section {
            text-align: center;
            padding: 20px 0;
        }
        .balance-card .balance-label {
            font-size: 0.95em;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .balance-card .balance-amount {
            font-size: 3em;
            font-weight: bold;
        }
        .balance-card .balance-amount small {
            font-size: 0.5em;
            opacity: 0.9;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
            border-color: #667eea;
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
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .token-display {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.85em;
            word-break: break-all;
            margin-top: 10px;
        }
        .quick-transfer {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .quick-transfer h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95em;
        }
        .quick-transfer p {
            color: #666;
            font-size: 0.9em;
            margin: 5px 0;
        }
        .quick-transfer code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="logo">
            <span>🏦</span>
            <span>安全银行</span>
        </div>
        <div class="nav-links">
            <a href="../../index.php">🏠 返回主页</a>
            <a href="logout.php">🚪 退出登录</a>
        </div>
    </div>
    
    <div class="container">
        <div class="balance-card">
            <div class="card-header">
                <div class="user-info">
                    <div class="avatar">👤</div>
                    <div>
                        <div class="user-name"><?php echo h($user['name']); ?></div>
                        <div class="user-account">账号：<?php echo h($user['account']); ?></div>
                    </div>
                </div>
            </div>
            <div class="balance-section">
                <div class="balance-label">可用余额</div>
                <div class="balance-amount">
                    <small>¥</small><?php echo number_format($user['balance'], 2); ?>
                </div>
            </div>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header <?php echo $mode === 'secure' ? 'secure' : 'vulnerable'; ?>">
                <h2>
                    <?php if ($mode === 'secure'): ?>
                    ✅ 转账功能（安全版本 - 有CSRF防护）
                    <?php else: ?>
                    ⚠️ 转账功能（漏洞版本 - 无CSRF防护）
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
                    <strong>✅ 安全提示：</strong>此表单包含CSRF Token验证，可以有效防止跨站请求伪造攻击。
                </div>
                <?php else: ?>
                <div class="warning-box">
                    <strong>⚠️ 安全警告：</strong>此表单没有CSRF Token保护，攻击者可以构造恶意页面诱导已登录用户执行转账操作！
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="transfer">
                    <?php if ($mode === 'secure'): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>💳 收款账号</label>
                            <input type="text" name="to_account" placeholder="请输入收款人账号" required>
                        </div>
                        <div class="form-group">
                            <label>💰 转账金额</label>
                            <input type="number" name="amount" step="0.01" min="0.01" placeholder="请输入金额" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 转账备注</label>
                        <input type="text" name="remark" placeholder="可选">
                    </div>
                    
                    <button type="submit" class="btn <?php echo $mode === 'secure' ? 'btn-primary' : 'btn-danger'; ?>">确认转账</button>
                </form>
                
                <?php if ($mode === 'secure'): ?>
                <div class="token-display">
                    <strong>当前CSRF Token：</strong><br>
                    <?php echo h($csrf_token); ?>
                </div>
                <?php endif; ?>
                
                <div class="quick-transfer">
                    <h4>📋 快速测试账号</h4>
                    <p>李四账号：<code>6222021234567890456</code></p>
                    <p>王五账号：<code>6222021234567890789</code></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 模式切换
        document.getElementById('modeSwitch').addEventListener('change', function() {
            const mode = this.checked ? 'secure' : 'vulnerable';
            document.cookie = 'bank_mode=' + mode + ';path=/;max-age=86400';
            location.reload();
        });
    </script>
</body>
</html>