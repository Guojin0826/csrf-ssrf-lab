# SSRF模块更新说明

## ✅ 已完成

### 1. 模拟数据文件
创建了完整的模拟数据环境，模拟真实的内网服务：

- `mock_data/users.php` - 内网用户数据（包含敏感API密钥）
- `mock_data/config.php` - 系统配置文件（数据库密码、加密密钥等）
- `mock_data/api.php` - 内网API服务（用户、配置、日志、密钥等）
- `mock_data/metadata.php` - 云元数据服务（AWS、阿里云等）
- `mock_data/redis.php` - Redis服务模拟
- `mock_data/config.txt` - 配置文件（明文密码）
- `mock_data/logs.txt` - 系统日志文件

### 2. SSRF入门模块首页
参考CSRF入门模块设计，包含：

- 📖 **什么是SSRF** - 概念介绍和危害说明
- 🔄 **攻击流程图** - 5步详细流程讲解
- 🎯 **攻击类型** - 基础SSRF、协议利用、内网探测、云元数据
- 🎮 **演示场景** - 4个实战演示场景入口
- 🛡️ **防护措施** - URL白名单、协议限制、IP过滤等

### 3. 退出登录功能
- `logout.php` - 正确的退出登录处理

## 📋 待创建的演示页面

### 场景1：基础SSRF演示 (`ssrf_basic.php`)
- 漏洞版本：无任何防护的URL请求
- 安全版本：URL白名单验证
- 攻击演示：访问内网服务、读取本地文件

### 场景2：协议利用演示 (`ssrf_protocol.php`)
- file:// 协议：读取本地文件
- dict:// 协议：探测Redis等服务
- gopher:// 协议：构造任意请求

### 场景3：内网探测演示 (`ssrf_scan.php`)
- 端口扫描：探测内网服务
- 服务识别：识别内网运行的服务
- 拓扑探测：绘制内网结构

### 场景4：云元数据演示 (`ssrf_cloud.php`)
- AWS元数据：获取IAM角色和凭证
- 阿里云元数据：获取实例信息
- GCP元数据：获取项目信息

## 🎯 设计特点

### 参考CSRF入门模块
- ✅ 统一的视觉风格
- ✅ 侧边栏导航
- ✅ 攻击流程图
- ✅ Tab切换演示
- ✅ 漏洞版本vs安全版本对比
- ✅ 返回主页按钮
- ✅ 用户信息显示

### SSRF特色功能
- ✅ 模拟真实的内网环境
- ✅ 多种协议支持
- ✅ 云元数据模拟
- ✅ 内网服务模拟
- ✅ 敏感数据展示

## 📁 文件结构

```
ssrf/
├── index.php              # 入门模块首页
├── logout.php             # 退出登录
├── ssrf_basic.php         # 基础SSRF演示（待创建）
├── ssrf_protocol.php      # 协议利用演示（待创建）
├── ssrf_scan.php          # 内网探测演示（待创建）
├── ssrf_cloud.php         # 云元数据演示（待创建）
└── mock_data/             # 模拟数据目录
    ├── users.php          # 用户数据
    ├── config.php         # 配置文件
    ├── api.php            # API服务
    ├── metadata.php       # 云元数据
    ├── redis.php          # Redis服务
    ├── config.txt         # 配置文件
    └── logs.txt           # 系统日志
```

## 🔧 下一步

需要创建以下演示页面：
1. ssrf_basic.php - 基础SSRF演示
2. ssrf_protocol.php - 协议利用演示
3. ssrf_scan.php - 内网探测演示
4. ssrf_cloud.php - 云元数据演示

每个页面都应包含：
- 漏洞版本和安全版本的Tab切换
- 攻击演示和Payload示例
- 防护措施说明
- 返回主页按钮