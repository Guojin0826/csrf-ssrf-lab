# CSRF 演示模块使用指南

## 📖 演示流程

### 第一步：登录系统
1. 访问 `login.php` 登录页面
2. 使用测试账号登录：
   - **管理员账号**：admin / admin123
   - **普通用户账号**：user1 / user123

### 第二步：查看两个版本
登录成功后，你会看到两个不同的页面：

#### ⚠️ 漏洞版本 (vulnerable.php)
- **特点**：没有 CSRF Token 保护
- **风险**：攻击者可以伪造请求修改用户信息
- **表单**：只有普通的输入字段，没有任何防护

#### ✅ 安全版本 (secure.php)
- **特点**：有 CSRF Token 保护
- **安全**：每个表单都有唯一的 Token，服务器会验证
- **表单**：包含隐藏字段 `<input type="hidden" name="csrf_token" value="...">`

### 第三步：体验攻击演示
1. 在两个标签页分别打开：
   - 漏洞版本：`vulnerable.php`
   - 安全版本：`secure.php`
   
2. 记录两个页面的当前信息（邮箱、昵称、电话）

3. 打开攻击演示页面：`attack.html`

4. 点击攻击按钮：
   - **攻击漏洞版本**：会成功修改用户信息
   - **攻击安全版本**：会被服务器拦截，显示 Token 验证失败

5. 返回两个页面查看结果：
   - 漏洞版本：信息已被修改为攻击者指定的值
   - 安全版本：信息保持不变

## 🔍 核心区别对比

| 特性 | 漏洞版本 | 安全版本 |
|------|---------|---------|
| **表单字段** | 只有业务字段 | 额外包含 csrf_token 字段 |
| **服务器验证** | 直接处理请求 | 先验证 Token 是否正确 |
| **攻击结果** | ✅ 攻击成功 | ❌ 攻击失败 |
| **用户信息** | 被修改 | 保持不变 |

## 💡 技术原理

### 漏洞版本代码
```php
// 没有任何验证，直接处理请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $nickname = $_POST['nickname'];
    $phone = $_POST['phone'];
    // 直接保存...
}
```

### 安全版本代码
```php
// 先验证 CSRF Token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证 Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token验证失败！可能是CSRF攻击！');
    }
    
    // Token 正确，处理请求
    $email = $_POST['email'];
    $nickname = $_POST['nickname'];
    $phone = $_POST['phone'];
    // 保存...
}
```

## 🛡️ 防御措施

### 1. 使用 CSRF Token
- 每个表单生成唯一的 Token
- Token 存储在用户 Session 中
- 提交时验证 Token 是否匹配

### 2. 验证 Referer 头
```php
if (!isset($_SERVER['HTTP_REFERER']) || 
    strpos($_SERVER['HTTP_REFERER'], 'yourdomain.com') === false) {
    die('非法请求来源！');
}
```

### 3. 使用 SameSite Cookie
```php
session_set_cookie_params([
    'samesite' => 'Strict'  // 或 'Lax'
]);
```

### 4. 双重 Cookie 验证
- 在 Cookie 和表单中同时放置 Token
- 服务器验证两者是否一致

## 🎯 学习要点

1. **理解 CSRF 攻击原理**
   - 利用用户已登录的身份
   - 伪造用户请求
   - 用户不知情的情况下执行操作

2. **掌握防御方法**
   - CSRF Token 是最有效的防御方式
   - 结合多种防御措施更安全

3. **实际应用**
   - 所有状态改变的操作都要防护
   - 修改密码、转账、发帖等
   - 不要只依赖 Cookie 验证

## ⚠️ 安全提示

- 本演示系统仅用于教学目的
- 密码使用明文存储是为了演示方便
- 生产环境必须使用 bcrypt/Argon2 等安全哈希
- 生产环境必须使用 HTTPS
- Token 要有有效期，定期更换

## 📂 文件说明

```
csrf/
├── config.php        # 配置文件（Session、Token生成）
├── login.php         # 登录页面
├── logout.php        # 登出功能
├── vulnerable.php    # 漏洞版本演示
├── secure.php        # 安全版本演示
├── attack.html       # 攻击演示页面
├── data/
│   └── users.json    # 用户数据
└── GUIDE.md          # 本文档
```