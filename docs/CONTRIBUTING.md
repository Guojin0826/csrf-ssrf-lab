# 贡献指南

感谢您考虑为 CSRF & SSRF 演示环境 项目做出贡献！

## 📋 贡献方式

### 报告问题 (Issues)

如果您发现了问题或有改进建议，请：

1. 检查是否已有相关Issue
2. 创建新的Issue，包含：
   - 清晰的标题和描述
   - 重现步骤（如果是bug）
   - 预期行为和实际行为
   - 环境信息（PHP版本、操作系统等）
   - 截图（如果适用）

### 提交代码 (Pull Requests)

#### 准备工作

1. Fork 本仓库
2. 克隆您的 Fork：
```bash
git clone https://github.com/YOUR_USERNAME/csrf_ssrf_demo.git
cd csrf_ssrf_demo
```

3. 创建特性分支：
```bash
git checkout -b feature/amazing-feature
```

#### 开发规范

##### PHP代码规范

- 遵循 PSR-12 编码规范
- 使用有意义的变量名和函数名
- 添加适当的注释
- 保持代码简洁清晰

##### 示例代码

```php
<?php
/**
 * 函数说明
 * 
 * @param string $param 参数说明
 * @return string 返回值说明
 */
function exampleFunction($param) {
    // 验证输入
    if (empty($param)) {
        return false;
    }
    
    // 处理逻辑
    $result = processParam($param);
    
    return $result;
}
```

##### HTML/CSS规范

- 使用语义化HTML标签
- CSS类名使用小写和连字符
- 保持响应式设计
- 遵循项目现有的颜色主题

##### JavaScript规范

- 使用ES5语法（兼容性更好）
- 添加适当的注释
- 避免全局变量污染

#### 提交规范

##### Commit Message格式

```
类型(范围): 简短描述

详细描述（可选）

相关Issue（可选）
```

##### 类型说明

- `feat`: 新功能
- `fix`: 修复bug
- `docs`: 文档更新
- `style`: 代码格式调整
- `refactor`: 代码重构
- `test`: 测试相关
- `chore`: 构建/工具相关

##### 示例

```
feat(csrf): 添加新的银行转账场景

- 实现银行登录页面
- 实现账户中心页面
- 实现攻击演示页面

Closes #123
```

#### 测试

提交前请确保：

1. 在本地测试所有更改
2. 确保没有破坏现有功能
3. 检查所有页面是否正常显示
4. 验证漏洞演示和安全版本都正常工作

#### 提交PR

1. 推送到您的分支：
```bash
git push origin feature/amazing-feature
```

2. 在GitHub上创建Pull Request
3. 填写PR模板：
   - 描述您的更改
   - 关联相关Issue
   - 说明测试情况

## 🎯 贡献方向

我们欢迎以下方面的贡献：

### 新功能

- 新的演示场景
- 新的攻击类型
- 新的防护方法
- UI/UX改进

### 文档改进

- 使用文档完善
- 代码注释添加
- 多语言支持
- 教学材料补充

### Bug修复

- 功能bug修复
- UI显示问题
- 兼容性问题
- 性能优化

### 安全改进

- 防护代码优化
- 安全提示完善
- 漏洞检测改进

## 📚 项目结构

了解项目结构有助于您定位需要修改的文件：

```
csrf_ssrf/
├── index.php          # 主页
├── csrf/              # CSRF模块
│   ├── bank/          # 银行场景
│   └── forum/         # 论坛场景
└── ssrf/              # SSRF模块
    ├── real/          # 拟真场景
    └── mock_data/     # 模拟数据
```

## ⚠️ 注意事项

### 安全相关

- 不要提交真实的密码或密钥
- 不要提交生产环境配置
- 确保演示数据是模拟数据
- 遵循项目的安全警告

### 代码质量

- 保持代码风格一致
- 添加必要的注释
- 避免过度复杂的实现
- 考虑可维护性

### 文档完整性

- 更新相关文档
- 添加使用说明
- 说明更改的影响
- 提供示例（如果适用）

## 🤝 行为准则

- 尊重所有贡献者
- 保持专业和友善
- 接受建设性批评
- 关注项目目标

## 📞 联系方式

如有疑问，可以通过以下方式联系：

- GitHub Issues: 提交问题或建议
- Email: jinrcsy@gmail.com

---

再次感谢您的贡献！🎉