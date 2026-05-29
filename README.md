# CSRF & SSRF 漏洞演示环境

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg)](https://php.net/)
[![Author](https://img.shields.io/badge/Author-Guojin0826-orange.svg)](https://github.com/Guojin0826)

一个完整的CSRF和SSRF漏洞演示环境，用于安全教学和漏洞研究。本项目提供了拟真的演示场景，帮助理解Web安全漏洞的原理和防护方法。

## 📋 目录

- [功能特点](#功能特点)
- [快速开始](#快速开始)
- [项目结构](#项目结构)
- [演示场景](#演示场景)
- [使用说明](#使用说明)
- [安全警告](#安全警告)
- [技术栈](#技术栈)
- [贡献指南](#贡献指南)
- [许可证](#许可证)
- [更多文档](#更多文档)

## ✨ 功能特点

### CSRF 演示模块
- 🎓 **入门教学模块** - 包含GET型、POST型、Token缺陷等基础演示
- 🏦 **银行转账场景** - 拟真的银行系统，演示CSRF转账攻击
- 💬 **论坛密码修改** - 模拟论坛系统，演示密码修改攻击
- 🔄 **一键切换** - 漏洞版本和安全版本快速切换对比
- 📝 **攻击代码展示** - 可查看和修改核心攻击代码

### SSRF 演示模块
- 🎓 **入门教学模块** - 包含基础SSRF、协议利用、内网探测、云元数据等演示
- 📂 **协议利用演示** - file://、dict://、gopher://协议详解
- 🔍 **内网探测演示** - 端口扫描、服务探测、拓扑发现
- ☁️ **云元数据攻击** - AWS、阿里云、GCP元数据获取
- 🎯 **拟真场景演示** - 6种真实环境场景（图片加载、Webhook、PDF导出等）

### 通用功能
- 🔄 **初始化按钮** - 一键重置所有演示环境到初始状态
- 🏠 **导航优化** - 所有页面都有返回主页按钮
- 📊 **数据可视化** - 攻击流程图、对比表格、结果展示
- 🛡️ **防护代码** - 每个漏洞都配有完整的防护示例

## 🚀 快速开始

### 环境要求

- PHP >= 7.0
- Web服务器（Apache/Nginx/PHP内置服务器）
- 无需数据库（使用JSON文件存储）

### 安装步骤

1. **克隆项目**
```bash
git clone https://github.com/Guojin0826/csrf_ssrf_demo.git
cd csrf_ssrf_demo
```

2. **启动PHP内置服务器**
```bash
php -S localhost:8000
```

3. **访问演示环境**

打开浏览器访问：`http://localhost:8000`

### 默认账号

**CSRF演示：**
- 用户名：`admin` / 密码：`admin123`
- 用户名：`user1` / 密码：`user123`

**银行系统：**
- 用户名：`zhangsan` / 密码：`123456`
- 用户名：`lisi` / 密码：`123456`

**论坛系统：**
- 用户名：`admin` / 密码：`admin123`
- 用户名：`xiaoming` / 密码：`123456`

**SSRF演示：**
- 用户名：`demo` / 密码：`demo123`

## 📁 项目结构

```
csrf_ssrf/
├── index.php                  # 主页
├── reset.php                  # 初始化脚本
├── README.md                  # 项目说明
├── .gitignore                 # Git忽略文件
├── LICENSE                    # 许可证
│
├── csrf/                      # CSRF演示模块
│   ├── index.php              # 入门教学首页
│   ├── logout.php             # 退出登录
│   ├── csrf_get.php           # GET型攻击演示
│   ├── csrf_post.php          # POST型攻击演示
│   ├── csrf_token_weak.php    # Token缺陷演示
│   ├── csrf_defense.php       # 防护方案指南
│   ├── attack_get.html        # GET型攻击页面
│   ├── attack_post.html       # POST型攻击页面
│   ├── attack_token_weak.html # Token弱版本攻击
│   ├── attack_token_strong.html # Token强版本攻击
│   │
│   ├── bank/                  # 银行转账场景
│   │   ├── index.php          # 银行登录
│   │   ├── account.php        # 账户中心
│   │   ├── logout.php         # 退出登录
│   │   └── attack.html        # 攻击演示页面
│   │
│   └── forum/                 # 论坛密码修改场景
│       ├── index.php          # 论坛登录
│       ├── profile.php        # 个人中心
│       ├── logout.php         # 退出登录
│       ├── attack.html        # 攻击演示页面
│       └── data/
│           └── forum_users.json # 用户数据
│
└── ssrf/                      # SSRF演示模块
    ├── index.php              # 入门教学首页
    ├── logout.php             # 退出登录
    ├── ssrf_basic.php         # 基础SSRF演示
    ├── ssrf_protocol.php      # 协议利用演示
    ├── ssrf_scan.php          # 内网探测演示
    ├── ssrf_cloud.php         # 云元数据攻击
    ├── demo.php               # 图片代理演示
    │
    ├── real/                  # 拟真场景演示
    │   ├── index.php          # 拟真场景首页
    │   ├── image.php          # 图片加载服务
    │   ├── webhook.php        # Webhook回调
    │   ├── pdf.php            # PDF导出服务
    │   ├── proxy.php          # URL代理服务
    │   ├── api.php            # API请求转发
    │   └── cache.php          # 缓存预加载
    │
    └── mock_data/             # 模拟数据
        ├── users.php          # 用户数据
        ├── config.php         # 配置文件
        ├── api.php            # API服务
        ├── metadata.php       # 云元数据
        ├── redis.php          # Redis服务
        ├── config.txt         # 配置文本
        └── logs.txt           # 日志文件
```

## 🎮 演示场景

### CSRF 演示场景

#### 1. 入门教学模块
- **GET型攻击** - 通过URL参数直接修改用户信息
- **POST型攻击** - 通过隐藏表单提交修改数据
- **Token缺陷** - 展示弱Token和强Token的区别
- **防护方案** - 完整的CSRF防护指南

#### 2. 银行转账场景
- 模拟真实银行系统界面
- 演示转账功能的CSRF漏洞
- 攻击页面伪装成"红包领取"
- 展示Token防护效果

#### 3. 论坛密码修改
- 模拟论坛用户中心
- 演示密码修改的CSRF漏洞
- 攻击页面伪装成"安全升级通知"
- 对比漏洞版本和安全版本

### SSRF 演示场景

#### 1. 基础SSRF演示
- 无防护的URL请求
- URL白名单验证
- 多种攻击Payload示例
- 防护代码展示

#### 2. 协议利用演示
- **file://协议** - 读取本地文件
- **dict://协议** - 服务探测
- **gopher://协议** - 构造任意TCP请求

#### 3. 内网探测演示
- 端口扫描工具
- 服务探测工具
- 内网拓扑发现
- Banner识别

#### 4. 云元数据攻击
- AWS EC2元数据获取
- 阿里云ECS元数据获取
- GCP Compute元数据获取

#### 5. 拟真场景演示
- **图片加载服务** - 模拟WordPress远程图片上传
- **Webhook回调** - 模拟GitHub Webhooks
- **PDF导出服务** - 模拟wkhtmltopdf
- **URL代理服务** - 模拟Facebook链接预览
- **API请求转发** - 模拟API Gateway
- **缓存预加载** - 模拟CDN缓存预热

## 📖 使用说明

### 基本使用流程

1. **访问主页** - 查看所有演示场景
2. **选择场景** - 点击进入对应的演示模块
3. **登录系统** - 使用默认账号登录
4. **切换模式** - 在漏洞版本和安全版本间切换
5. **执行攻击** - 在攻击页面执行攻击演示
6. **查看结果** - 观察攻击效果和防护效果
7. **重置环境** - 点击初始化按钮恢复初始状态

### CSRF攻击演示流程

1. 登录目标系统（如银行系统）
2. 在新标签页打开攻击页面
3. 点击攻击按钮执行攻击
4. 返回目标系统查看结果
5. 切换到安全版本对比效果

### SSRF攻击演示流程

1. 登录演示系统
2. 切换到漏洞版本
3. 输入攻击Payload
4. 查看响应结果
5. 切换到安全版本对比

## ⚠️ 安全警告

**重要提示：**

1. **仅供教学使用** - 本项目仅用于安全教学和研究
2. **禁止非法使用** - 不得用于攻击真实系统或非法用途
3. **隔离环境** - 请在隔离的测试环境中运行
4. **不要部署到公网** - 避免被恶意利用
5. **学习目的** - 用于理解漏洞原理和防护方法

## 🛠️ 技术栈

- **后端语言**：PHP 7.0+
- **数据存储**：JSON文件
- **前端**：原生HTML/CSS/JavaScript
- **样式**：响应式设计，渐变主题
- **无框架依赖**：纯PHP实现，无需composer

## 🤝 贡献指南

欢迎提交Issue和Pull Request！

### 贡献步骤

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 提交 Pull Request

### 代码规范

- 使用PSR-12编码规范
- 添加适当的注释
- 保持代码简洁清晰
- 测试所有更改

## 📝 更新日志

### v1.0.0 (2024-01-XX)
- ✨ 初始版本发布
- ✨ 完整的CSRF演示模块
- ✨ 完整的SSRF演示模块
- ✨ 拟真场景演示
- ✨ 一键初始化功能
- ✨ 漏洞版本和安全版本切换

## 👤 作者

- **GitHub**: [Guojin0826](https://github.com/Guojin0826)
- **Email**: jinrcsy@gmail.com

## 📄 许可证

本项目采用 MIT 许可证 - 详见 [LICENSE](LICENSE) 文件

## 📚 更多文档

- [安装说明](docs/INSTALL.md) - 详细的安装和配置说明
- [贡献指南](docs/CONTRIBUTING.md) - 如何为项目做出贡献
- [安全政策](docs/SECURITY.md) - 安全使用说明和警告

## 🙏 致谢

感谢所有为Web安全做出贡献的研究者和教育者。

---

**⚠️ 免责声明：本项目仅供安全教学和研究使用，使用者需自行承担所有风险和法律责任。**