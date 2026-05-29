# 🚀 快速启动指南

## 方式一：使用 PHP 内置服务器（推荐用于测试）

1. 打开命令行，进入项目目录
2. 运行以下命令：
   ```bash
   php -S localhost:8000
   ```
3. 打开浏览器访问：http://localhost:8000

## 方式二：使用 XAMPP/WAMP/MAMP

1. 将整个 `csrf_ssrf` 文件夹复制到服务器的网站根目录
   - XAMPP: `C:\xampp\htdocs\`
   - WAMP: `C:\wamp64\www\`
   - MAMP: `/Applications/MAMP/htdocs/`
   
2. 启动 Apache 服务器

3. 访问：http://localhost/csrf_ssrf/

## 方式三：使用 Nginx

在 Nginx 配置中添加：

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/csrf_ssrf;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## ⚠️ 重要提示

1. **仅用于教学目的**：本平台包含真实的安全漏洞，请勿部署到公网或生产环境！

2. **数据权限**：确保 `csrf/data` 目录有写入权限
   ```bash
   chmod 777 csrf/data
   ```

3. **PHP 版本**：建议 PHP 7.0 或更高版本

## 🎯 演示流程

### CSRF 演示
1. 访问首页 → 点击“CSRF演示”
2. 使用测试账号登录（admin/admin123）
3. 在用户中心查看漏洞版本和安全版本的对比
4. 在新标签页打开 `csrf/attack.html` 体验攻击

### SSRF 演示
1. 访问首页 → 点击“SSRF演示”
2. 在漏洞版本中输入测试 URL
3. 观察安全版本的防护效果

## 📞 问题排查

**问题：无法保存用户数据**
- 检查 `csrf/data` 目录权限

**问题：页面样式异常**
- 确保 `assets/style.css` 文件存在

**问题：SSRF 请求失败**
- 检查 PHP cURL 扩展是否启用
- 查看服务器防火墙设置