# 安装说明

## 📦 系统要求

- **PHP版本**: >= 7.0
- **Web服务器**: Apache / Nginx / PHP内置服务器
- **操作系统**: Windows / Linux / macOS
- **数据库**: 无需数据库（使用JSON文件存储）

## 🚀 快速安装

### 方法1: 使用PHP内置服务器（推荐用于测试）

```bash
# 1. 克隆项目
git clone https://github.com/Guojin0826/csrf_ssrf_demo.git

# 2. 进入项目目录
cd csrf_ssrf_demo

# 3. 启动PHP内置服务器
php -S localhost:8000

# 4. 打开浏览器访问
# http://localhost:8000
```

### 方法2: 使用Apache

```bash
# 1. 克隆项目到Web目录
cd /var/www/html
git clone https://github.com/Guojin0826/csrf_ssrf_demo.git

# 2. 设置权限
chmod -R 755 csrf_ssrf_demo
chown -R www-data:www-data csrf_ssrf_demo

# 3. 配置虚拟主机（可选）
# 编辑 /etc/apache2/sites-available/csrf_demo.conf

# 4. 访问
# http://localhost/csrf_ssrf_demo/
```

### 方法3: 使用Nginx

```bash
# 1. 克隆项目
cd /usr/share/nginx/html
git clone https://github.com/Guojin0826/csrf_ssrf_demo.git

# 2. 设置权限
chmod -R 755 csrf_ssrf_demo
chown -R nginx:nginx csrf_ssrf_demo

# 3. 配置Nginx（可选）
# 编辑 /etc/nginx/conf.d/csrf_demo.conf

# 4. 重启Nginx
systemctl restart nginx

# 5. 访问
# http://localhost/csrf_ssrf_demo/
```

### 方法4: 使用XAMPP (Windows)

```bash
# 1. 下载并安装XAMPP
# https://www.apachefriends.org/

# 2. 克隆项目到htdocs目录
cd C:\xampp\htdocs
git clone https://github.com/Guojin0826/csrf_ssrf_demo.git

# 3. 启动XAMPP控制面板，启动Apache

# 4. 访问
# http://localhost/csrf_ssrf_demo/
```

## ⚙️ 配置说明

### PHP配置要求

编辑 `php.ini` 文件，确保以下设置：

```ini
; 错误显示（开发环境）
display_errors = On
error_reporting = E_ALL

; Session配置
session.save_path = "/tmp"
session.auto_start = 0

; 文件上传
file_uploads = On
upload_max_filesize = 10M
post_max_size = 10M

; 时区设置
date.timezone = Asia/Shanghai
```

### Apache配置（可选）

创建 `.htaccess` 文件（已包含在项目中）：

```apache
# 启用URL重写
RewriteEngine On

# 默认页面
DirectoryIndex index.php

# 禁止访问敏感文件
<FilesMatch "\.(json|log|txt)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### Nginx配置示例

```nginx
server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html/csrf_ssrf_demo;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 禁止访问敏感文件
    location ~ \.(json|log|txt)$ {
        deny all;
    }
}
```

## 🔐 默认账号

### CSRF演示系统
- **用户1**: admin / admin123
- **用户2**: user1 / user123

### 银行系统
- **张三**: zhangsan / 123456
- **李四**: lisi / 123456
- **王五**: wangwu / 123456

### 论坛系统
- **管理员**: admin / admin123
- **小明**: xiaoming / 123456
- **张三**: zhangsan / 123456

### SSRF演示系统
- **演示用户**: demo / demo123

## ✅ 安装验证

安装完成后，请验证：

1. **访问主页**
   - 打开浏览器访问项目URL
   - 应该看到项目主页

2. **测试CSRF模块**
   - 点击"CSRF演示"进入
   - 使用默认账号登录
   - 测试各个演示场景

3. **测试SSRF模块**
   - 点击"SSRF演示"进入
   - 使用默认账号登录
   - 测试各个演示场景

4. **测试初始化功能**
   - 点击主页的"初始化演示环境"按钮
   - 应该显示成功消息

## 🔧 故障排除

### 问题1: 页面无法访问

**解决方案**:
- 检查Web服务器是否启动
- 检查文件权限
- 检查PHP是否正确安装

### 问题2: Session无法保存

**解决方案**:
- 检查session.save_path是否可写
- 检查PHP session配置

### 问题3: 样式显示异常

**解决方案**:
- 清除浏览器缓存
- 检查文件路径是否正确

### 问题4: 中文乱码

**解决方案**:
- 确保所有文件使用UTF-8编码
- 检查PHP header设置
- 检查浏览器编码设置

## 📞 获取帮助

如果遇到问题：

1. 查看项目 [README.md](README.md)
2. 查看项目 [Wiki](https://github.com/Guojin0826/csrf_ssrf_demo/wiki)
3. 提交 [Issue](https://github.com/Guojin0826/csrf_ssrf_demo/issues)
4. 发送邮件至: jinrcsy@gmail.com

---

**⚠️ 安全提示**: 请勿将本项目部署到公网或生产环境！