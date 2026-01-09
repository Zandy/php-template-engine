# 安全指南

本文档提供 Zandy_Template 模板引擎的安全使用指南。

## 目录

- [变量访问安全](#变量访问安全)
- [代码执行安全](#代码执行安全)
- [路径安全](#路径安全)
- [最佳实践](#最佳实践)

## 变量访问安全

### 问题说明

⚠️ **重要**：默认情况下，模板可以访问所有全局变量，包括系统变量和配置信息。

### 安全风险

- **变量泄露**：模板可以访问所有全局变量
- **敏感信息泄露**：可能泄露数据库连接信息、API密钥等
- **变量覆盖**：可能导致变量覆盖，影响程序逻辑

### 解决方案

#### 方案1：白名单模式（推荐用于生产环境）

```php
// 配置白名单，只允许指定的变量被模板访问
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data', 'items'];

// 使用方式不变
$GLOBALS['user'] = $user;
$GLOBALS['data'] = $data;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
```

**优点**：
- 平衡安全性和易用性
- 只允许模板访问指定的变量
- 推荐用于生产环境

#### 方案2：显式传递模式（最安全）

```php
// 显式传递变量，完全控制变量访问
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data
]);
```

**优点**：
- 最安全，完全控制变量访问
- 不依赖全局变量
- 适合高安全要求的场景

### 安全建议

1. **开发环境**：可以使用完全开放模式（默认），方便调试
2. **生产环境**：推荐使用白名单模式或显式传递模式
3. **高安全要求**：使用显式传递模式，完全控制变量访问
4. **模板来源不可信**：必须使用显式传递模式，并禁用 PHP 代码块

## 代码执行安全

### 问题说明

⚠️ **重要**：以下语法允许执行任意 PHP 代码，请确保模板来源可信：

- `<!--{php}-->...<!--{/php}-->` - 执行任意 PHP 代码
- `<!--{set ...}-->` - 设置变量，可执行代码
- `<!--{include ...}-->` - 包含 PHP 文件

### 安全风险

- **任意代码执行**：模板可以执行任意 PHP 代码
- **系统访问**：可以访问文件系统、数据库等
- **数据泄露**：可以读取敏感文件

### 安全建议

1. **模板来源可信**：
   - 如果模板由开发者编写，可以使用这些功能
   - 确保模板文件权限正确

2. **模板来源不可信**：
   - 必须禁用 PHP 代码块（如果可能）
   - 使用白名单机制限制可包含的文件
   - 严格验证模板路径

3. **生产环境**：
   - 如果模板来源不可信，应禁用这些功能
   - 考虑在模板解析阶段过滤这些语法

## 路径安全

### 保护机制

模板引擎会自动验证：

- ✅ 模板目录必须在 `tplBaseDir` 内
- ✅ 缓存目录必须在 `tplCacheBaseDir` 内
- ✅ 防止路径遍历攻击（使用 `realpath()` 检查）

### 路径验证代码

```php
// 模板路径验证
if (!$tplDir2 || !$tplBaseDir || false === stripos($tplDir2, $tplBaseDir)) {
    self::halt('$tplDir is not a valid tpl path', true);
}

// 缓存路径验证
if (!$cacheDir2 || !$tplCacheBaseDir || false === stripos(realpath($cacheDir2), $tplCacheBaseDir)) {
    self::halt('cache path is not valid', true);
}
```

### 安全建议

1. **配置正确的路径**：
   - 确保 `tplBaseDir` 和 `tplCacheBaseDir` 配置正确
   - 使用绝对路径，避免相对路径问题

2. **文件权限**：
   - 模板文件：只读权限
   - 缓存目录：可写权限，但限制访问

3. **路径验证**：
   - 引擎已自动验证路径，无需额外处理
   - 确保配置的路径正确

## 最佳实践

### 开发环境

```php
// 可以使用完全开放模式（默认），方便调试
$GLOBALS['user'] = $user;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
```

### 生产环境

```php
// 推荐：白名单模式
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data', 'items'];
$GLOBALS['user'] = $user;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
```

### 高安全要求

```php
// 推荐：显式传递模式
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data
]);
```

### 模板来源不可信

如果模板来源不可信，必须：

1. **使用显式传递模式**：
   ```php
   $html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
       'user' => $user
   ]);
   ```

2. **禁用 PHP 代码块**（如果可能）：
   - 在模板解析阶段过滤 `<!--{php}-->` 语法
   - 或使用模板验证机制

3. **严格验证模板路径**：
   - 确保模板文件在允许的目录内
   - 使用白名单机制限制可包含的文件

## 安全检查清单

- [ ] 生产环境是否使用白名单模式或显式传递模式？
- [ ] 模板文件权限是否正确（只读）？
- [ ] 缓存目录权限是否正确（可写但限制访问）？
- [ ] 模板路径配置是否正确？
- [ ] 如果模板来源不可信，是否禁用了 PHP 代码块？
- [ ] 是否使用了 `includeTemplate()` 或 `getTemplateVars()` 而不是直接 `extract($GLOBALS)`？

## 更多信息

- [使用指南](USAGE.md) - 详细的使用说明
- [API 参考](API.md) - 完整的 API 文档
- [语法参考](SYNTAX.md) - 完整的模板语法
