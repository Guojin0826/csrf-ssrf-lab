<?php
/**
 * SSRF云元数据攻击演示 - AWS/阿里云/GCP元数据获取
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
$current_cloud = isset($_GET['cloud']) ? $_GET['cloud'] : 'aws';

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
    
    // 检查是否是云元数据地址
    $metadata_ips = ['169.254.169.254', '100.100.100.200', 'metadata.google.internal'];
    if (in_array($ip, $metadata_ips) || in_array($host, $metadata_ips)) {
        return false;
    }
    
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
                $context = stream_context_create([
                    'http' => ['timeout' => 5, 'ignore_errors' => true],
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                
                if ($response === false) {
                    $error = '请求失败: 无法访问该URL';
                } else {
                    $result = $response;
                    $success = "✅ 云元数据获取成功！";
                }
            } catch (Exception $e) {
                $error = '请求异常: ' . $e->getMessage();
            }
        } else {
            if (!isValidUrl($url)) {
                $error = '❌ URL验证失败：禁止访问云元数据服务';
            } else {
                $error = '✅ 安全版本：已阻止对云元数据的访问';
            }
        }
    }
}

// 云服务商元数据知识
$cloud_knowledge = [
    'aws' => [
        'name' => 'AWS EC2 元数据服务',
        'address' => '169.254.169.254',
        'description' => 'AWS EC2实例可通过169.254.169.254访问元数据服务，获取IAM角色、临时凭证等敏感信息',
        'endpoints' => [
            'IAM角色' => '/latest/meta-data/iam/security-credentials/',
            '临时凭证' => '/latest/meta-data/iam/security-credentials/{role-name}',
            '实例ID' => '/latest/meta-data/instance-id',
            '实例类型' => '/latest/meta-data/instance-type',
            '私有IP' => '/latest/meta-data/local-ipv4',
            '公有IP' => '/latest/meta-data/public-ipv4',
            '用户数据' => '/latest/user-data',
            'SSH公钥' => '/latest/meta-data/public-keys/0/openssh-key',
        ],
        'danger' => '极高危 - 可获取AWS临时凭证，接管整个AWS账户',
        'defense' => [
            '使用IMDSv2（需要Token）',
            '限制IAM角色权限（最小权限原则）',
            '阻止对169.254.169.254的访问',
            '监控异常的元数据访问'
        ]
    ],
    'aliyun' => [
        'name' => '阿里云 ECS 元数据服务',
        'address' => '100.100.100.200',
        'description' => '阿里云ECS实例可通过100.100.100.200访问元数据服务',
        'endpoints' => [
            '实例ID' => '/latest/meta-data/instance-id',
            '实例类型' => '/latest/meta-data/instance/instance-type',
            '区域ID' => '/latest/meta-data/region-id',
            '私有IP' => '/latest/meta-data/network/interfaces/macs/[mac]/primary-ip-address',
            'RAM角色' => '/latest/meta-data/ram/security-credentials/',
            '临时凭证' => '/latest/meta-data/ram/security-credentials/[role-name]',
            '镜像ID' => '/latest/meta-data/image-id',
            '主机名' => '/latest/meta-data/hostname',
        ],
        'danger' => '极高危 - 可获取RAM临时凭证，接管阿里云账户',
        'defense' => [
            '使用实例RAM角色而非AccessKey',
            '限制RAM角色权限',
            '阻止对100.100.100.200的访问',
            '启用安全组规则限制'
        ]
    ],
    'gcp' => [
        'name' => 'GCP Compute Engine 元数据服务',
        'address' => 'metadata.google.internal',
        'description' => 'GCP实例可通过metadata.google.internal访问元数据服务',
        'endpoints' => [
            '访问令牌' => '/computeMetadata/v1/instance/service-accounts/default/token',
            '服务账户邮箱' => '/computeMetadata/v1/instance/service-accounts/default/email',
            '项目ID' => '/computeMetadata/v1/project/project-id',
            '实例名称' => '/computeMetadata/v1/instance/name',
            '区域' => '/computeMetadata/v1/instance/zone',
            'SSH公钥' => '/computeMetadata/v1/project/attributes/ssh-keys',
            '启动脚本' => '/computeMetadata/v1/instance/attributes/startup-script',
        ],
        'danger' => '极高危 - 可获取GCP访问令牌，接管GCP项目',
        'defense' => [
            '使用服务账户而非用户账户',
            '限制服务账户权限',
            '阻止对metadata.google.internal的访问',
            '启用VPC防火墙规则'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSRF云元数据攻击演示 - AWS/阿里云/GCP</title>
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
        
        .cloud-tabs {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        .cloud-tab {
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
        .cloud-tab.active {
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
        
        .cloud-info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
        }
        .cloud-info-box h3 {
            color: white;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .cloud-info-box p {
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
        
        .endpoint-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .endpoint-table th {
            background: #11998e;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .endpoint-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .endpoint-table tr:hover {
            background: #f8f9fa;
        }
        .endpoint-table code {
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
            border-left: 4px solid #11998e;
        }
        .flow-step .step-number {
            width: 40px;
            height: 40px;
            background: #11998e;
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
            color: #11998e;
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
            <h1>☁️ SSRF云元数据攻击演示 - AWS/阿里云/GCP</h1>
            <div class="nav-links">
                <a href="index.php">📚 返回首页</a>
                <a href="../index.php">🏠 返回主页</a>
            </div>
        </div>
        
        <!-- 云服务商选择 -->
        <div class="cloud-tabs">
            <a href="?cloud=aws&mode=<?php echo $current_mode; ?>" 
               class="cloud-tab <?php echo $current_cloud === 'aws' ? 'active' : ''; ?>">
                ☁️ AWS EC2
            </a>
            <a href="?cloud=aliyun&mode=<?php echo $current_mode; ?>" 
               class="cloud-tab <?php echo $current_cloud === 'aliyun' ? 'active' : ''; ?>">
                ☁️ 阿里云 ECS
            </a>
            <a href="?cloud=gcp&mode=<?php echo $current_mode; ?>" 
               class="cloud-tab <?php echo $current_cloud === 'gcp' ? 'active' : ''; ?>">
                ☁️ GCP Compute
            </a>
        </div>
        
        <!-- 模式切换 -->
        <div class="mode-tabs">
            <a href="?cloud=<?php echo $current_cloud; ?>&mode=vulnerable" 
               class="mode-tab vulnerable <?php echo $current_mode === 'vulnerable' ? 'active' : ''; ?>">
                ⚠️ 漏洞版本
            </a>
            <a href="?cloud=<?php echo $current_cloud; ?>&mode=safe" 
               class="mode-tab safe <?php echo $current_mode === 'safe' ? 'active' : ''; ?>">
                ✅ 安全版本
            </a>
        </div>
        
        <div class="content">
            <!-- 云服务商信息 -->
            <?php $cloud = $cloud_knowledge[$current_cloud]; ?>
            <div class="cloud-info-box">
                <h3>📖 <?php echo $cloud['name']; ?> - 知识讲解</h3>
                <p><strong>元数据地址：</strong><?php echo $cloud['address']; ?></p>
                <p><strong>服务说明：</strong><?php echo $cloud['description']; ?></p>
                <p><strong>危害等级：</strong><?php echo $cloud['danger']; ?></p>
            </div>
            
            <!-- 攻击原理 -->
            <div class="section">
                <h2>🎯 云元数据攻击原理</h2>
                
                <div class="alert alert-info">
                    <strong>核心原理：</strong><br>
                    云服务商为实例提供元数据服务，实例可通过特定IP地址访问。攻击者利用SSRF漏洞，通过服务器访问元数据服务，获取IAM角色、临时凭证等敏感信息。
                </div>
                
                <h3>🔄 攻击流程</h3>
                <div class="attack-flow">
                    <div class="flow-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>发现SSRF漏洞</h4>
                            <p>找到可以发起HTTP请求的功能点，如图片加载、URL预览等</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>构造元数据URL</h4>
                            <p>构造指向云元数据服务的URL（如169.254.169.254）</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>获取IAM角色名</h4>
                            <p>访问元数据服务获取实例绑定的IAM角色名称</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>获取临时凭证</h4>
                            <p>使用角色名获取临时访问凭证（AccessKey、SecretKey、Token）</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4>接管云账户</h4>
                            <p>使用临时凭证访问云服务API，接管整个云账户</p>
                        </div>
                    </div>
                </div>
                
                <h3>📋 元数据端点列表</h3>
                <table class="endpoint-table">
                    <thead>
                        <tr>
                            <th>信息类型</th>
                            <th>API端点</th>
                            <th>危害说明</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cloud['endpoints'] as $name => $endpoint): ?>
                        <tr>
                            <td><strong><?php echo $name; ?></strong></td>
                            <td><code><?php echo $endpoint; ?></code></td>
                            <td>
                                <?php 
                                if (strpos($name, '凭证') !== false || strpos($name, '令牌') !== false || strpos($name, '角色') !== false) {
                                    echo '<span style="color: #f56c6c;">🔴 极高危</span>';
                                } elseif (strpos($name, 'IP') !== false || strpos($name, 'ID') !== false) {
                                    echo '<span style="color: #e6a23c;">🟡 中危</span>';
                                } else {
                                    echo '<span style="color: #409eff;">🔵 低危</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 演示区域 -->
            <div class="demo-box">
                <h3>🔧 云元数据获取演示</h3>
                <p>输入元数据URL进行攻击测试（仅漏洞版本有效）：</p>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="?cloud=<?php echo $current_cloud; ?>&mode=<?php echo $current_mode; ?>">
                    <div class="form-group">
                        <label>🌐 元数据URL</label>
                        <input type="text" name="url" placeholder="http://169.254.169.254/latest/meta-data/" 
                               value="<?php echo htmlspecialchars($_POST['url'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">获取元数据</button>
                </form>
                
                <?php if ($result): ?>
                <h4 style="margin-top: 20px; color: #333;">📄 元数据内容：</h4>
                <div class="result-box"><?php echo htmlspecialchars($result); ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Payload示例 -->
            <div class="section">
                <h2>🎯 攻击Payload示例</h2>
                <p>点击以下Payload快速测试：</p>
                
                <?php if ($current_cloud === 'aws'): ?>
                <table class="endpoint-table">
                    <thead>
                        <tr>
                            <th>攻击目标</th>
                            <th>Payload</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>IAM角色列表</td>
                            <td><code>mock_data/metadata.php?type=iam</code></td>
                            <td><button class="btn btn-primary" onclick="fillUrl('mock_data/metadata.php?type=iam')">测试</button></td>
                        </tr>
                        <tr>
                            <td>安全凭证</td>
                            <td><code>mock_data/metadata.php?type=security</code></td>
                            <td><button class="btn btn-danger" onclick="fillUrl('mock_data/metadata.php?type=security')">测试</button></td>
                        </tr>
                        <tr>
                            <td>实例信息</td>
                            <td><code>mock_data/metadata.php?type=instance</code></td>
                            <td><button class="btn btn-primary" onclick="fillUrl('mock_data/metadata.php?type=instance')">测试</button></td>
                        </tr>
                        <tr>
                            <td>网络配置</td>
                            <td><code>mock_data/metadata.php?type=network</code></td>
                            <td><button class="btn btn-primary" onclick="fillUrl('mock_data/metadata.php?type=network')">测试</button></td>
                        </tr>
                    </tbody>
                </table>
                
                <?php elseif ($current_cloud === 'aliyun'): ?>
                <table class="endpoint-table">
                    <thead>
                        <tr>
                            <th>攻击目标</th>
                            <th>Payload</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>RAM角色信息</td>
                            <td><code>mock_data/api.php?endpoint=secrets</code></td>
                            <td><button class="btn btn-danger" onclick="fillUrl('mock_data/api.php?endpoint=secrets')">测试</button></td>
                        </tr>
                        <tr>
                            <td>实例配置</td>
                            <td><code>mock_data/config.php</code></td>
                            <td><button class="btn btn-primary" onclick="fillUrl('mock_data/config.php')">测试</button></td>
                        </tr>
                    </tbody>
                </table>
                
                <?php elseif ($current_cloud === 'gcp'): ?>
                <table class="endpoint-table">
                    <thead>
                        <tr>
                            <th>攻击目标</th>
                            <th>Payload</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>访问令牌</td>
                            <td><code>mock_data/api.php?endpoint=secrets</code></td>
                            <td><button class="btn btn-danger" onclick="fillUrl('mock_data/api.php?endpoint=secrets')">测试</button></td>
                        </tr>
                        <tr>
                            <td>服务账户</td>
                            <td><code>mock_data/users.php</code></td>
                            <td><button class="btn btn-primary" onclick="fillUrl('mock_data/users.php')">测试</button></td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <!-- 真实攻击案例 -->
            <div class="section">
                <h2>🔥 真实攻击案例</h2>
                
                <div class="code-block">
<strong>Capital One数据泄露事件（2019）</strong>

攻击者通过SSRF漏洞访问AWS EC2元数据服务：
1. 发现Web应用存在SSRF漏洞
2. 构造URL访问：http://169.254.169.254/latest/meta-data/iam/security-credentials/
3. 获取IAM角色名：WAF-Role
4. 获取临时凭证：AccessKey + SecretKey + SessionToken
5. 使用临时凭证访问S3存储桶
6. 窃取1亿用户的敏感数据

<strong>影响：</strong>
- 1.06亿用户数据泄露
- 损失超过1.9亿美元
- 攻击者被判5年监禁
                </div>
            </div>
            
            <!-- 防护措施 -->
            <div class="section">
                <h2>🛡️ 防护措施详解</h2>
                
                <div class="defense-list">
                    <h4>✅ 防护方案</h4>
                    <ul>
                        <?php foreach ($cloud['defense'] as $defense): ?>
                        <li><?php echo $defense; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="code-block">
<strong>AWS IMDSv2防护示例：</strong>

# 启用IMDSv2（需要Token）
aws ec2 modify-instance-metadata-options \
    --instance-id i-1234567890abcdef0 \
    --http-tokens required \
    --http-endpoint enabled

# 访问元数据需要先获取Token
TOKEN=`curl -X PUT "http://169.254.169.254/latest/api/token" -H "X-aws-ec2-metadata-token-ttl-seconds: 21600"`
curl -H "X-aws-ec2-metadata-token: $TOKEN" http://169.254.169.254/latest/meta-data/

<strong>阻止元数据访问（安全组规则）：</strong>

# 阻止所有对元数据服务的访问
iptables -A OUTPUT -d 169.254.169.254 -j DROP
iptables -A OUTPUT -d 100.100.100.200 -j DROP
                </div>
            </div>
            
            <!-- 危害说明 -->
            <div class="danger-box">
                <h4>⚠️ 攻击危害</h4>
                <ul style="margin-left: 20px; color: #333;">
                    <li><strong>云账户接管：</strong>获取临时凭证后可完全控制云账户</li>
                    <li><strong>数据泄露：</strong>访问S3、OSS等存储服务窃取数据</li>
                    <li><strong>权限提升：</strong>创建新的IAM用户或角色</li>
                    <li><strong>横向渗透：</strong>访问其他云服务和资源</li>
                    <li><strong>持久化控制：</strong>创建后门账户或启动脚本</li>
                </ul>
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