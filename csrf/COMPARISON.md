# CSRF 漏洞对比说明

## 🔍 两个版本的区别

### ⚠️ 漏洞版本（无 CSRF Token）

**表单代码：**
```html
<form method="POST" action="">
    <input type="hidden" name="action" value="update_vulnerable">
    <!-- 没有 CSRF Token 字段 -->
    <input type="email" name="email" value="user@example.com">
    <input type="text" name="nickname" value="用户昵称">
    <input type="text" name="phone" value="13800138000">
    <button type="submit">提交修改</button>
</form>
```

**处理逻辑：**
```php
if ($action === 'update_vulnerable') {
    // 直接处理请求，不验证任何Token
    $user['email'] = $_POST['email'];
    $user['nickname'] = $_POST['nickname'];
    $user['phone'] = $_POST['phone'];
    // 保存修改...
}
```

**攻击方式：**
攻击者可以构造恶意页面：
```html
<!-- 攻击者的恶意页面 -->
<form id="csrf_form" method="POST" action="http://target.com/csrf/dashboard.php">
    <input type="hidden" name="action" value="update_vulnerable">
    <input type="hidden" name="email" value="hacked@evil.com">
    <input type="hidden" name="nickname" value="已被攻击">
    <input type="hidden" name="phone" value="00000000000">
</form>
<script>
    // 自动提交表单
    document.getElementById('csrf_form').submit();
</script>
```

**为什么会被攻击？**
1. 用户已在目标网站登录（有有效的 Session）
2. 用户访问了攻击者的恶意页面
3. 恶意页面自动向目标网站发送 POST 请求
4. 浏览器会自动带上用户的 Cookie（包含 Session ID）
5. 服务器无法区分这是用户主动提交还是被诱导提交的
6. 用户信息被修改，但用户完全不知情

---

### ✅ 安全版本（有 CSRF Token）

**表单代码：**
```html
<form method="POST" action="">
    <input type="hidden" name="action" value="update_secure">
    <!-- 包含 CSRF Token -->
    <input type="hidden" name="csrf_token" value="a1b2c3d4e5f6...">
    <input type="email" name="email" value="user@example.com">
    <input type="text" name="nickname" value="用户昵称">
    <input type="text" name="phone" value="13800138000">
    <button type="submit">提交修改</button>
</form>
```

**处理逻辑：**
```php
if ($action === 'update_secure') {
    // 验证 CSRF Token
    if (!isset($_POST['csrf_token']) || 
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF Token 验证失败！');
    }
    
    // Token 验证通过，处理请求
    $user['email'] = $_POST['email'];
    $user['nickname'] = $_POST['nickname'];
    $user['phone'] = $_POST['phone'];
    // 保存修改...
}
```

**为什么能防御攻击？**
1. Token 存储在服务器 Session 中，攻击者无法获取
2. 表单提交时必须携带正确的 Token
3. 攻击者构造的恶意页面无法获取到正确的 Token
4. 即使请求被发送，Token 验证也会失败
5. 服务器拒绝处理，攻击失败

---

## 🧪 演示步骤

### 测试漏洞版本

1. 使用 `admin/admin123` 登系统
2. 记下当前的邮箱、昵称、电话
3. 在新标签页打开 `attack.html`
4. 点击"执行CSRF攻击演示"
5. 返回用户中心，查看信息已被修改

### 测试安全版本

1. 重新登录，恢复原始信息
2. 尝试构造攻击页面攻击安全版本
3. 会收到"CSRF Token 验证失败"的错误
4. 信息不会被修改

---

## 📊 对比总结

| 特性 | 漏洞版本 | 安全版本 |
|------|---------|---------|
| CSRF Token | ❌ 无 | ✅ 有 |
| Token 验证 | ❌ 无 | ✅ 有 |
| 可被攻击 | ✅ 是 | ❌ 否 |
| 攻击难度 | 🟢 低 | 🔴 高（几乎不可能） |
| 安全性 | 🔴 低 | 🟢 高 |

---

## 🛡️ 最佳实践

1. **所有状态改变操作都应使用 CSRF Token**
   - POST、PUT、DELETE 请求
   - 修改用户信息、密码、转账等

2. **Token 生成要求**
   - 使用加密安全的随机数生成器
   - 足够长（至少 32 字节）
   - 每个会话唯一

3. **Token 验证要求**
   - 比对 Session 中的 Token 和提交的 Token
   - 使用恒定时间比较（防止时序攻击）
   - 验证失败应拒绝请求

4. **额外防护措施**
   - 设置 SameSite Cookie 属性
   - 检查 Referer 头
   - 关键操作要求二次验证
