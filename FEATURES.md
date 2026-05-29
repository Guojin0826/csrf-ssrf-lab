# CSRF & SSRF 漏洞演示平台 - 功能说明

## 📋 已完成的优化

### 1. 一键切换功能（漏洞版本 ↔ 安全版本）

所有演示模块都添加了一键切换按钮，可以在漏洞版本和安全版本之间快速切换：

- **银行转账场景**：`csrf/bank/account.php`
  - 漏洞版本：无CSRF Token验证，攻击可成功
  - 安全版本：有CSRF Token验证，攻击被拦截

- **论坛密码修改**：`csrf/forum/profile.php`
  - 漏洞版本：无CSRF Token验证，攻击可成功
  - 安全版本：有CSRF Token验证，攻击被拦截

- **SSRF图片代理**：`ssrf/demo.php`
  - 漏洞版本：无URL验证，可访问内网资源
  - 安全版本：URL白名单验证，禁止访问内网

### 2. 返回主页按钮

所有页面都添加了"返回主页"按钮，方便用户快速返回主页面：

- CSRF演示页面：`csrf/index.php`, `csrf_get.php`, `csrf_post.php`, `dashboard.php`, `vulnerable.php`, `secure.php`
- CSRF银行场景：`csrf/bank/account.php`, `csrf/bank/attack.html`
- CSRF论坛场景：`csrf/forum/profile.php`, `csrf/forum/attack.html`
- SSRF演示页面：`ssrf/index.php`, `ssrf/demo.php`

### 3. 攻击代码可视化编辑

所有攻击页面都添加了核心攻击代码展示区域，用户可以：

- **查看攻击代码**：点击"显示/隐藏"按钮查看完整的攻击代码
- **修改攻击参数**：通过表单实时修改攻击参数（账号、金额、密码等）
- **代码实时更新**：修改参数后，代码区域自动更新显示最新的攻击代码

#### 攻击页面列表：

1. **银行转账攻击页面**：`csrf/bank/attack.html`
   - 伪装成"红包领取"页面
   - 可修改：收款账号、转账金额、转账备注

2. **论坛密码修改攻击页面**：`csrf/forum/attack.html`
   - 伪装成"系统安全升级"页面
   - 可修改：新密码

3. **通用CSRF攻击页面**：`csrf/attack.html`
   - 可修改：邮箱、昵称、电话

## 📁 文件结构

```
csrf_ssrf/
├── index.php              # 主页（包含所有演示场景入口）
├── README.md              # 详细说明文档
├── QUICK_START.md         # 快速启动指南
│
├── csrf/
│   ├── index.php          # 基础CSRF演示
│   ├── csrf_get.php       # GET型CSRF漏洞
│   ├── csrf_post.php      # POST型CSRF漏洞
│   ├── dashboard.php      # 用户中心（原有演示）
│   ├── vulnerable.php     # 漏洞版本（原有演示）
│   ├── secure.php         # 安全版本（原有演示）
│   ├── attack.html        # 通用攻击页面（带代码编辑）
│   │
│   ├── bank/              # 银行转账场景
│   │   ├── index.php      # 银行登录页面
│   │   ├── account.php    # 账户中心（带切换功能）
│   │   └── attack.html    # 红包领取攻击页面（带代码编辑）
│   │
│   └── forum/             # 论坛密码修改场景
│   │   ├── index.php      # 论坛登录页面
│   │   ├── profile.php    # 个人中心（带切换功能）
│   │   ├── logout.php     # 退出登录
│   │   ├── attack.html    # 安全升级攻击页面（带代码编辑）
│   │   └── data/
│   │       └── forum_users.json  # 用户数据
│
└── ssrf/
    ├── index.php          # SSRF基础演示
    └── demo.php           # SSRF拟真场景（带切换功能）
```

## 🎯 使用方法

### CSRF演示流程

#### 银行转账场景：
1. 访问主页，点击"银行系统"
2. 使用测试账号登录（张三/123456）
3. 在账户中心，将模式切换到"漏洞版本"
4. 在新标签页打开攻击页面
5. 点击"立即领取"按钮，观察转账结果
6. 切换到"安全版本"，再次尝试攻击，观察拦截效果

#### 论坛密码修改场景：
1. 访问主页，点击"论坛系统"
2. 使用测试账号登录（admin/admin123）
3. 在个人中心，将模式切换到"漏洞版本"
4. 在新标签页打开攻击页面
5. 点击"立即升级"按钮，观察密码修改结果
6. 尝试使用新密码登录
7. 切换到"安全版本"，再次尝试攻击，观察拦截效果

### SSRF演示流程

1. 访问主页，点击"SSRF拟真场景"
2. 将模式切换到"漏洞版本"
3. 点击测试Payload（如访问本地服务、读取本地文件等）
4. 观察返回结果
5. 切换到"安全版本"，再次尝试相同的Payload
6. 观察拦截效果和错误提示

## 🔧 技术特点

### 一键切换实现原理

使用Cookie存储当前模式：
```php
$mode = $_COOKIE['module_mode'] ?? 'vulnerable';
```

前端切换按钮：
```html
<label class="switch">
    <input type="checkbox" id="modeSwitch" <?php echo $mode === 'secure' ? 'checked' : ''; ?>>
    <span class="slider"></span>
</label>
```

JavaScript切换逻辑：
```javascript
document.getElementById('modeSwitch').addEventListener('change', function() {
    const mode = this.checked ? 'secure' : 'vulnerable';
    document.cookie = 'module_mode=' + mode + ';path=/;max-age=86400';
    location.reload();
});
```

### 攻击代码可视化实现

实时参数编辑：
```javascript
document.getElementById('amount').addEventListener('input', function() {
    document.getElementById('form_amount').value = this.value;
    updateCode();
});
```

代码动态更新：
```javascript
function updateCode() {
    const code = `<!-- 攻击表单 -->
<form method="POST" action="target.php">
    <input type="hidden" name="amount" value="${amount}">
</form>`;
    document.getElementById('attackCode').value = code;
}
```

## 🎨 UI设计特点

- **拟真界面**：攻击页面伪装成红包领取、安全升级等常见场景
- **渐变配色**：漏洞版本使用红色渐变，安全版本使用蓝紫渐变
- **交互友好**：一键切换、参数编辑、代码展示等交互设计
- **视觉提示**：警告框、信息框、成功框等不同状态提示

## ⚠️ 安全提示

本平台仅用于安全教学目的，请勿将所学知识用于非法攻击行为。未经授权的渗透测试是违法行为。