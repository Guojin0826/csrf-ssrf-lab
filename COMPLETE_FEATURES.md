# CSRF & SSRF 演示平台 - 完整功能清单

## ✅ 已完成的所有优化

### 1. 一键切换功能（漏洞版本 ↔ 安全版本）

所有演示模块都添加了一键切换按钮：

- **银行转账场景**：`csrf/bank/account.php`
- **论坛密码修改**：`csrf/forum/profile.php`
- **SSRF图片代理**：`ssrf/demo.php`

### 2. 返回主页按钮

所有页面都已添加"🏠 返回主页"按钮：

#### CSRF 演示系统
- `csrf/index.php` - CSRF基础演示首页
- `csrf/csrf_get.php` - GET型CSRF漏洞
- `csrf/csrf_post.php` - POST型CSRF漏洞
- `csrf/dashboard.php` - 用户中心
- `csrf/vulnerable.php` - 漏洞版本
- `csrf/secure.php` - 安全版本
- `csrf/attack.html` - 通用攻击页面

#### 银行转账场景
- `csrf/bank/index.php` - 银行登录页面 ✅ 新增
- `csrf/bank/account.php` - 账户中心
- `csrf/bank/attack.html` - 攻击页面

#### 论坛密码修改场景
- `csrf/forum/index.php` - 论坛登录页面 ✅ 新增
- `csrf/forum/profile.php` - 个人中心
- `csrf/forum/attack.html` - 攻击页面

#### SSRF 演示系统
- `ssrf/index.php` - SSRF基础演示
- `ssrf/demo.php` - SSRF拟真场景

### 3. 退出登录功能优化

所有退出登录功能都已完善：

- `csrf/logout.php` - CSRF演示系统退出登录 ✅ 优化
- `csrf/bank/logout.php` - 银行系统退出登录 ✅ 新建
- `csrf/forum/logout.php` - 论坛系统退出登录 ✅ 优化

退出登录现在会：
- 清除所有session数据
- 删除session cookie
- 销毁session
- 重定向到登录页面

### 4. 攻击代码可视化编辑

所有攻击页面都添加了核心攻击代码展示区域：

- **银行转账攻击页面** (`csrf/bank/attack.html`)
  - 伪装成"红包领取"页面
  - 可修改：收款账号、转账金额、转账备注
  - 实时显示攻击代码

- **论坛密码修改攻击页面** (`csrf/forum/attack.html`)
  - 伪装成"系统安全升级"页面
  - 可修改：新密码
  - 实时显示攻击代码

- **通用CSRF攻击页面** (`csrf/attack.html`)
  - 可修改：邮箱、昵称、电话
  - 实时显示攻击代码

### 5. UI优化

所有攻击页面UI都已优化：
- 合理的布局和间距
- 渐变背景和按钮
- 清晰的警告框和信息框
- 代码区域使用深色主题
- 参数编辑区域整齐排列
- 响应式设计适配移动端

## 📁 完整文件结构

```
csrf_ssrf/
├── index.php              # 主页（包含所有演示场景入口）
├── README.md              # 详细说明文档
├── QUICK_START.md         # 快速启动指南
├── FEATURES.md            # 功能说明文档
│
├── csrf/
│   ├── index.php          # 基础CSRF演示（带返回主页按钮）
│   ├── csrf_get.php       # GET型CSRF漏洞（带返回主页按钮）
│   ├── csrf_post.php      # POST型CSRF漏洞（带返回主页按钮）
│   ├── login.php          # 登录处理脚本
│   ├── logout.php         # 退出登录（优化版）
│   ├── dashboard.php      # 用户中心（带返回主页按钮）
│   ├── vulnerable.php     # 漏洞版本（带返回主页按钮）
│   ├── secure.php         # 安全版本（带返回主页按钮）
│   ├── attack.html        # 通用攻击页面（优化UI）
│   │
│   ├── bank/              # 银行转账场景
│   │   ├── index.php      # 银行登录（带返回主页按钮）
│   │   ├── account.php    # 账户中心（带切换功能）
│   │   ├── logout.php     # 退出登录（新建）
│   │   └── attack.html    # 红包领取攻击页面（优化UI）
│   │
│   └── forum/             # 论坛密码修改场景
│   │   ├── index.php      # 论坛登录（带返回主页按钮）
│   │   ├── profile.php    # 个人中心（带切换功能）
│   │   ├── logout.php     # 退出登录（优化版）
│   │   ├── attack.html    # 安全升级攻击页面（优化UI）
│   │   └── data/
│   │       └── forum_users.json  # 用户数据
│
└── ssrf/
    ├── index.php          # SSRF基础演示（带返回主页按钮）
    └── demo.php           # SSRF拟真场景（带切换功能）
```

## 🎯 使用方法

### 银行转账演示流程
1. 访问主页，点击"银行系统"
2. 点击"返回主页"按钮可随时返回
3. 使用测试账号登录（张三/123456）
4. 在账户中心，使用切换按钮切换模式
5. 在新标签页打开攻击页面
6. 点击"返回主页"按钮返回主页
7. 点击"退出登录"按钮退出系统

### 论坛密码修改演示流程
1. 访问主页，点击"论坛系统"
2. 点击"返回主页"按钮可随时返回
3. 使用测试账号登录（admin/admin123）
4. 在个人中心，使用切换按钮切换模式
5. 在新标签页打开攻击页面
6. 点击"返回主页"按钮返回主页
7. 点击"退出登录"按钮退出系统

### SSRF演示流程
1. 访问主页，点击"SSRF拟真场景"
2. 点击"返回主页"按钮可随时返回
3. 使用切换按钮切换模式
4. 测试各种Payload
5. 点击"返回主页"按钮返回主页

## 🎨 UI特点

- **拟真界面**：攻击页面伪装成红包领取、安全升级等常见场景
- **渐变配色**：漏洞版本使用红色渐变，安全版本使用蓝紫渐变
- **交互友好**：一键切换、参数编辑、代码展示等交互设计
- **视觉提示**：警告框、信息框、成功框等不同状态提示
- **导航便捷**：所有页面都有返回主页按钮和退出登录功能

## ⚠️ 安全提示

本平台仅用于安全教学目的，请勿将所学知识用于非法攻击行为。未经授权的渗透测试是违法行为。