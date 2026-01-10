# Zandy_Template 模板引擎

本模板系统属于编译型的，最大特色是变量直接使用 PHP 的变量，毫无学习成本；运行速度快，开发效率高。

## 目录

- [快速开始](#快速开始)
- [主要特性](#主要特性)
- [基本使用](#基本使用)
- [主要 API](#主要-api)
- [安全提示](#安全提示)
- [文档](#文档)
- [示例代码](#示例代码)
- [测试](#测试)
- [许可证](#许可证)

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

// 返回填充数据后的纯 HTML 字符串（推荐）
echo Zandy_Template::outString('goods.htm', $siteConf['tplDir'], $siteConf['cacheDir']);

// 相当于 Smarty 的 display，直接显示结果
include Zandy_Template::outCache('goods.htm', $siteConf['tplDir'], $siteConf['cacheDir']);
```

### 配置要求

需要设置 `$GLOBALS['siteConf']` 配置：

```php
$GLOBALS['siteConf'] = array(
    'tplBaseDir' => '/path/to/templates/base',      // 模板基础目录
    'tplCacheBaseDir' => '/path/to/cache',          // 缓存基础目录
    'tplDir' => '/path/to/templates',               // 当前模板目录
);
```

## 主要特性

- ✅ **编译型模板**，性能优异
- ✅ **使用原生 PHP 变量**，零学习成本
- ✅ **支持完整的 PHP 语法**
- ✅ **自动缓存管理**
- ✅ **路径安全验证**
- ✅ **命名循环功能**，支持访问循环索引信息
- ✅ **变量作用域隔离**，避免变量污染
- ✅ **兼容 PHP 5.6 - PHP 8.4+**

## 基本使用

### 变量输出

```html
{$variable}
{$array['key']}
{$object->property}
```

### 循环

```html
<!--{loop $items as $item}-->
    <li>{$item}</li>
<!--{/loop}-->

<!--{for $i = 0; $i < 10; $i++}-->
    <span>{$i}</span>
<!--{/for}-->
```

### 条件判断

```html
<!--{if $condition}-->
    <p>条件为真</p>
<!--{elseif $other > 100}-->
    <p>其他条件</p>
<!--{else}-->
    <p>默认</p>
<!--{/if}-->
```

### 模板包含

```html
<!--{template header.htm}-->
<!--{template ../common/footer.htm}-->
```

**更多语法**：查看 [完整语法参考](docs/syntax.md)

## 主要 API

### outString()

返回填充数据后的 HTML 字符串（推荐）

```php
// 基本用法
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
echo $html;

// 显式传递变量（更安全）
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data
]);
```

### includeTemplate()

安全地 include 模板文件（推荐用于 outCache 方式）

```php
// 显式传递变量（推荐）
Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir, false, ['user' => $user]);
```

### outCache()

返回编译后的缓存文件路径

```php
$cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
include $cacheFile;
```

**完整 API 文档**：查看 [API 参考](docs/api.md)

## 安全提示

### 变量访问安全

⚠️ **重要**：默认情况下，模板可以访问所有全局变量，包括系统变量和配置信息。

**安全建议**：
- **开发环境**：可以使用完全开放模式（默认），方便调试
- **生产环境**：推荐使用白名单模式或显式传递模式
- **高安全要求**：使用显式传递模式，完全控制变量访问

**配置示例**：
```php
// 生产环境推荐配置
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data', 'items'];
```

### 代码执行安全

⚠️ **重要**：以下语法允许执行 PHP 代码，请确保模板来源可信：

- `<!--{php}-->...<!--{/php}-->` - 执行任意 PHP 代码
- `<!--{set ...}-->` - 设置变量（语法糖，用于简化变量赋值，如 `<!--{set $var = 'value'}-->` 替代 `<!--{php}-->$var = 'value';<!--{/php}-->`）
- `<!--{include ...}-->` - 包含 PHP 文件

**详细安全指南**：查看 [安全指南](docs/security.md)

## 文档

- [使用指南](docs/usage.md) - 详细的使用说明和最佳实践
- [API 参考](docs/api.md) - 完整的 API 文档
- [语法参考](docs/syntax.md) - 完整的模板语法
- [安全指南](docs/security.md) - 安全使用指南
- [示例代码](examples/README.md) - 使用示例

## 示例代码

查看 `examples/` 目录获取更多使用示例：

- `examples/basic_usage.php` - 基础使用示例
- `examples/security_demo.php` - 安全使用示例（变量传递方式）
- `examples/function_class_usage_demo.php` - 函数和类方法内部使用示例
- `examples/loops_and_conditions.php` - 循环和条件语法示例
- `examples/named_loop_demo.php` - 命名循环功能演示
- `examples/switch_demo.php` - Switch 语句示例

**完整示例列表**：查看 [examples/README.md](examples/README.md)

## 测试

运行单元测试：

```bash
# 基本功能测试（变量输出、循环、条件、Switch、时间函数、常量等）
php test/BasicFeaturesTest.php

# 命名循环功能测试
php test/NamedLoopTest.php

# 语法检查测试
php test/CheckSyntaxTest.php

# 运行所有测试（Unix/Linux/macOS）
./test/run_tests.sh
```

详细说明请参考 [test/README.md](test/README.md)

## 许可证

查看 [Licence](Licence) 文件了解许可证信息。
