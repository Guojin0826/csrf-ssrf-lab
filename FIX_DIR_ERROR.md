# 目录不存在错误修复说明

## 问题描述
访问CSRF论坛登录界面时出现错误：
```
Warning: file_put_contents(C:\phpstudy_pro\WWW\csrf\csrf\forum/data/forum_users.json): failed to open stream: No such file or directory
```

## 原因分析
PHP代码尝试写入JSON数据文件，但数据目录`csrf/forum/data/`不存在，导致`file_put_contents()`失败。

## 已修复的文件

### 1. `csrf/forum/index.php`
- ✅ 添加目录检查和创建逻辑
- ✅ 在写入文件前确保目录存在

### 2. `reset.php`
- ✅ 添加目录检查和创建逻辑
- ✅ 确保重置功能正常工作

## 修复代码

```php
// 初始化论坛用户数据
$dataFile = __DIR__ . '/data/forum_users.json';
$dataDir = dirname($dataFile);

// 确保数据目录存在
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

if (!file_exists($dataFile)) {
    // 创建默认用户数据...
    file_put_contents($dataFile, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
```

## 测试步骤

1. 访问 `http://localhost/csrf/forum/index.php`
2. 应该不再出现错误
3. 可以正常登录论坛系统

## 注意事项

- 目录权限需要允许PHP写入（755或777）
- 如果使用Windows，确保路径分隔符正确
- 首次访问时会自动创建目录和数据文件