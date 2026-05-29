# 修复GitHub Push Protection错误

GitHub检测到代码中包含敏感信息（Stripe API密钥），已阻止推送。

## 已修复的文件

1. `ssrf/mock_data/api.php` - 替换Stripe密钥为示例值
2. `ssrf/mock_data/config.txt` - 替换API密钥为示例值
3. `ssrf/mock_data/redis.php` - 替换API密钥为示例值

## 解决方案

由于敏感信息已经在Git历史记录中，需要重写历史记录：

### 方法1：重新初始化仓库（推荐）

```bash
# 1. 删除旧的Git历史
rm -rf .git

# 2. 重新初始化仓库
git init

# 3. 配置用户信息
git config user.name "Guojin0826"
git config user.email "jinrcsy@gmail.com"

# 4. 添加所有文件
git add .

# 5. 提交
git commit -m "feat: 初始版本 - CSRF & SSRF漏洞演示环境"

# 6. 添加远程仓库
git remote add origin https://github.com/Guojin0826/csrf-ssrf-lab.git

# 7. 强制推送（会覆盖远程仓库）
git push -f origin main
```

### 方法2：使用git filter-branch（保留历史）

```bash
# 从历史记录中删除敏感信息
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch ssrf/mock_data/api.php ssrf/mock_data/config.txt ssrf/mock_data/redis.php' \
  --prune-empty --tag-name-filter cat -- --all

# 强制推送
git push origin main --force
```

## 注意事项

- 所有API密钥都已替换为示例值（sk_test_example_key_for_demo）
- 这些密钥仅用于演示，不是真实的密钥
- 不会影响演示功能