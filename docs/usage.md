# 使用指南

本文档提供 Zandy_Template 模板引擎的详细使用指南。

## 目录

- [安装和配置](#安装和配置)
- [快速开始](#快速开始)
- [变量传递方式](#变量传递方式)
- [使用场景](#使用场景)
- [最佳实践](#最佳实践)

## 安装和配置

### 基本配置

需要设置 `$GLOBALS['siteConf']` 配置：

```php
$GLOBALS['siteConf'] = array(
    'tplBaseDir' => '/path/to/templates/base',      // 模板基础目录
    'tplCacheBaseDir' => '/path/to/cache',          // 缓存基础目录
    'tplDir' => '/path/to/templates',               // 当前模板目录
);
```

### 配置说明

- `tplBaseDir` - 模板基础目录，用于路径验证
- `tplCacheBaseDir` - 缓存基础目录，用于路径验证
- `tplDir` - 当前模板目录，模板文件的查找路径
- `tplCacheMaxTime` - 缓存过期时间（秒），默认 3 小时

## 快速开始

### 基本使用

```php
require_once 'Template.php';

// 配置
$GLOBALS['siteConf'] = array(
    'tplBaseDir' => '/path/to/templates/base',
    'tplCacheBaseDir' => '/path/to/cache',
    'tplDir' => '/path/to/templates',
);

// 方式1：返回 HTML 字符串（推荐）
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
echo $html;

// 方式2：直接 include（相当于 Smarty 的 display）
include Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
```

## 变量传递方式

模板引擎支持三种变量传递方式，可根据安全需求选择：

### 方式1：完全开放模式（默认，向后兼容）

```php
// 使用全局变量（最简单，向后兼容）
$GLOBALS['user'] = $user;
$GLOBALS['data'] = $data;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
```

**特点**：
- ✅ 零学习成本，使用简单
- ⚠️ 模板可以访问所有全局变量（包括系统变量）
- ⚠️ 适合开发环境或模板来源可信的场景

### 方式2：白名单模式（推荐用于生产环境）

```php
// 配置白名单，只允许指定的变量被模板访问
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data', 'items'];

// 使用方式不变
$GLOBALS['user'] = $user;
$GLOBALS['data'] = $data;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
```

**特点**：
- ✅ 平衡安全性和易用性
- ✅ 只允许模板访问指定的变量
- ✅ 推荐用于生产环境

### 方式3：显式传递模式（最安全）

```php
// 显式传递变量，完全控制变量访问
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data,
    'items' => $items
]);
```

**特点**：
- ✅ 最安全，完全控制变量访问
- ✅ 不依赖全局变量
- ✅ 适合高安全要求的场景

### 配置说明

通过 `$GLOBALS['siteConf']['template_vars_mode']` 配置变量提取模式：

- `'open'` (默认): 完全开放模式，提取所有全局变量
- `'whitelist'`: 白名单模式，只提取指定的变量
- `'explicit'`: 显式模式，只使用显式传递的变量

**注意**：
- 如果通过 API 显式传递变量（`outString()` 的 `$vars` 参数），则忽略配置，直接使用显式变量
- `siteConf` 变量始终会被包含（模板引擎需要）

## 使用场景

### 场景1：面向过程使用（文件顶层）

```php
<?php
// index.php - 文件顶层
require_once 'Template.php';

$tplDir = '/path/to/templates/';
$cacheDir = '/path/to/cache/';

// 方式1：使用全局变量（向后兼容）
$GLOBALS['user'] = $user;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);

// 方式2：显式传递（推荐，更安全）
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, ['user' => $user]);
```

### 场景2：函数内部使用

```php
<?php
// functions.php
function renderUserProfile($userId) {
    $tplDir = '/path/to/templates/';
    $cacheDir = '/path/to/cache/';
    
    // 获取数据（局部变量）
    $user = getUserById($userId);
    $posts = getUserPosts($userId);
    $stats = getUserStats($userId);
    
    // 推荐：显式传递（避免污染全局变量）
    return Zandy_Template::outString('user_profile.htm', $tplDir, $cacheDir, false, [
        'user' => $user,
        'posts' => $posts,
        'stats' => $stats
    ]);
}
```

### 场景3：类方法内部使用

```php
<?php
// Controller.php
class UserController {
    private $tplDir = '/path/to/templates/';
    private $cacheDir = '/path/to/cache/';
    
    public function showProfile($userId) {
        // 获取数据（局部变量）
        $user = $this->getUserById($userId);
        $posts = $this->getUserPosts($userId);
        
        // 推荐：显式传递
        return Zandy_Template::outString('user_profile.htm', $this->tplDir, $this->cacheDir, false, [
            'user' => $user,
            'posts' => $posts
        ]);
    }
    
    public function renderProfile($userId) {
        // 使用 includeTemplate()（适合直接输出）
        $user = $this->getUserById($userId);
        $posts = $this->getUserPosts($userId);
        
        Zandy_Template::includeTemplate('user_profile.htm', $this->tplDir, $this->cacheDir, false, [
            'user' => $user,
            'posts' => $posts
        ]);
    }
}
```

### 场景4：outCache() + include 的使用

```php
<?php
// 面向过程使用
$GLOBALS['user'] = $user;
$cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
extract(Zandy_Template::getTemplateVars());  // 使用配置模式
include $cacheFile;

// 函数内部使用（推荐）
function renderPage() {
    $user = getUser();
    
    // 方式1：使用 includeTemplate()（最推荐）
    Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir, false, [
        'user' => $user
    ]);
    
    // 方式2：使用 getTemplateVars() 辅助函数
    $cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
    extract(Zandy_Template::getTemplateVars(['user' => $user]));
    include $cacheFile;
}
```

## 最佳实践

### 1. 变量传递

**推荐**：
- **函数/类方法内部**：使用显式传递变量，避免污染全局变量
- **面向过程使用**：可以使用 `$GLOBALS` 或显式传递
- **生产环境**：使用白名单模式或显式传递模式

**不推荐**：
- 在函数内部使用 `$GLOBALS`（污染全局变量）
- 在生产环境使用完全开放模式（安全风险）

### 2. outCache 场景

**推荐**：
- 使用 `includeTemplate()` 方法（最推荐）
- 使用 `getTemplateVars()` 辅助函数

**不推荐**：
- 直接使用 `extract($GLOBALS)`（安全风险）

### 3. 安全配置

**开发环境**：
```php
// 可以使用完全开放模式（默认），方便调试
// 无需额外配置
```

**生产环境**：
```php
// 推荐使用白名单模式
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data', 'items'];
```

**高安全要求**：
```php
// 使用显式传递模式
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data
]);
```

### 4. 模板来源不可信

如果模板来源不可信，必须：
1. 使用显式传递模式
2. 禁用 PHP 代码块（如果可能）
3. 严格验证模板路径

## 更多信息

- [API 参考](API.md) - 完整的 API 文档
- [语法参考](SYNTAX.md) - 完整的模板语法
- [安全指南](SECURITY.md) - 安全使用指南
- [示例代码](../examples/README.md) - 使用示例
