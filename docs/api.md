# API 参考

本文档提供 Zandy_Template 模板引擎的完整 API 参考。

## 目录

- [outString()](#outstring)
- [outCache()](#outcache)
- [includeTemplate()](#includetemplate)
- [getTemplateVars()](#gettemplatevars)
- [out()](#out)
- [outHTML()](#outhtml)
- [outEval()](#outeval)
- [缓存模式常量](#缓存模式常量)

## outString()

返回填充数据后的 HTML 字符串（推荐）

### 语法

```php
public static function outString(
    string $tplFileName, 
    string $tplDir = '', 
    string $cacheDir = '', 
    bool $forceRefreshCache = false, 
    array|null $vars = null
): string
```

### 参数

- `$tplFileName` (string) - 模板文件名
- `$tplDir` (string) - 模板目录（可选，默认使用 `$GLOBALS['siteConf']['tplDir']`）
- `$cacheDir` (string) - 缓存目录（可选，默认使用 `$GLOBALS['siteConf']['tplCacheBaseDir']`）
- `$forceRefreshCache` (bool) - 是否强制刷新缓存（默认 false）
- `$vars` (array|null) - 显式传递的变量数组（可选，如果提供则只使用这些变量）

### 返回值

返回填充数据后的 HTML 字符串。

### 使用示例

```php
// 方式1：使用全局变量（向后兼容）
$GLOBALS['user'] = $user;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
echo $html;

// 方式2：显式传递变量（推荐，更安全）
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data
]);
echo $html;

// 方式3：配置白名单模式
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data'];
$GLOBALS['user'] = $user;
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
```

## outCache()

返回编译后的缓存文件路径，然后 include

### 语法

```php
public static function outCache(
    string $tplFileName, 
    string $tplDir = '', 
    string $cacheDir = '', 
    bool $forceRefreshCache = false
): string|false
```

### 参数

- `$tplFileName` (string) - 模板文件名
- `$tplDir` (string) - 模板目录（可选）
- `$cacheDir` (string) - 缓存目录（可选）
- `$forceRefreshCache` (bool) - 是否强制刷新缓存（默认 false）

### 返回值

返回编译后的缓存文件路径，失败返回 false。

### 使用示例

```php
// 方式1：使用 includeTemplate()（推荐，支持安全模式）
Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir, false, ['user' => $user]);

// 方式2：手动 include（需要手动处理变量）
$cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
extract(Zandy_Template::getTemplateVars(['user' => $user]));
include $cacheFile;

// 方式3：使用全局变量（向后兼容，但不推荐）
$GLOBALS['user'] = $user;
$cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
extract($GLOBALS);  // ⚠️ 安全问题
include $cacheFile;
```

## includeTemplate()

安全地 include 模板文件（推荐用于 outCache 方式）

### 语法

```php
public static function includeTemplate(
    string $tplFileName, 
    string $tplDir = '', 
    string $cacheDir = '', 
    bool $forceRefreshCache = false, 
    array|null $vars = null
): void
```

### 参数

- `$tplFileName` (string) - 模板文件名
- `$tplDir` (string) - 模板目录（可选）
- `$cacheDir` (string) - 缓存目录（可选）
- `$forceRefreshCache` (bool) - 是否强制刷新缓存（默认 false）
- `$vars` (array|null) - 显式传递的变量数组（可选）

### 返回值

无返回值，直接输出模板内容。

### 使用示例

```php
// 方式1：使用全局变量（向后兼容）
$GLOBALS['user'] = $user;
Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir);

// 方式2：显式传递变量（推荐，更安全）
Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir, false, ['user' => $user]);

// 方式3：配置白名单模式
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data'];
$GLOBALS['user'] = $user;
Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir);
```

## getTemplateVars()

安全地提取模板变量（用于 outCache + include 方式）

### 语法

```php
public static function getTemplateVars(array|null $explicitVars = null): array
```

### 参数

- `$explicitVars` (array|null) - 显式传递的变量数组（可选）

### 返回值

返回提取的变量数组。

### 使用示例

```php
// 方式1：显式传递变量（推荐）
$cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
extract(Zandy_Template::getTemplateVars(['user' => $user]));
include $cacheFile;

// 方式2：使用配置模式
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data'];
$GLOBALS['user'] = $user;
$cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
extract(Zandy_Template::getTemplateVars());  // 使用配置的白名单
include $cacheFile;
```

## out()

通用输出方法，可指定输出模式

### 语法

```php
public static function out(
    string $tplFileName, 
    string $tplDir = '', 
    string $cacheDir = '', 
    bool $forceRefreshCache = false, 
    int $cacheMod = ZANDY_TEMPLATE_CACHE_MOD_PHPC
): string|false
```

### 参数

- `$tplFileName` (string) - 模板文件名
- `$tplDir` (string) - 模板目录（可选）
- `$cacheDir` (string) - 缓存目录（可选）
- `$forceRefreshCache` (bool) - 是否强制刷新缓存（默认 false）
- `$cacheMod` (int) - 缓存模式（见下方常量说明）

### 返回值

根据缓存模式返回不同的值（见缓存模式常量说明）。

### 使用示例

```php
// 返回 HTML 内容
$html = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);

// 返回缓存文件路径
$cacheFile = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_PHPC);

// 返回可 eval 的字符串
$code = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_EVAL);
eval($code);
```

## outHTML()

返回 HTML 文件路径或内容

### 语法

```php
public static function outHTML(
    string $tplFileName, 
    string $tplDir = '', 
    string $cacheDir = '', 
    bool $forceRefreshCache = false, 
    int $outMod = ZANDY_TEMPLATE_CACHE_MOD_HTML, 
    array|null $vars = null
): string|false
```

### 参数

- `$tplFileName` (string) - 模板文件名
- `$tplDir` (string) - 模板目录（可选）
- `$cacheDir` (string) - 缓存目录（可选）
- `$forceRefreshCache` (bool) - 是否强制刷新缓存（默认 false）
- `$outMod` (int) - 输出模式（见缓存模式常量）
- `$vars` (array|null) - 显式传递的变量数组（可选）

### 返回值

根据输出模式返回 HTML 文件路径或内容。

### 使用示例

```php
// 返回 HTML 文件路径
$htmlFile = Zandy_Template::outHTML('template.htm', $tplDir, $cacheDir);

// 返回 HTML 内容
$htmlContent = Zandy_Template::outHTML('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);
```

## outEval()

返回可 eval 的字符串

### 语法

```php
public static function outEval(string $tplFileName, string $tplDir = ''): string|false
```

### 参数

- `$tplFileName` (string) - 模板文件名
- `$tplDir` (string) - 模板目录（可选）

### 返回值

返回可 eval 的字符串，失败返回 false。

### 使用示例

```php
$code = Zandy_Template::outEval('template.htm', $tplDir);
if ($code !== false) {
    ob_start();
    eval($code);
    $result = ob_get_clean();
    echo $result;
}
```

## 缓存模式常量

### ZANDY_TEMPLATE_CACHE_MOD_PHPC (1)

返回编译后的 PHP 文件路径

```php
$cacheFile = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_PHPC);
include $cacheFile;
```

### ZANDY_TEMPLATE_CACHE_MOD_HTML (2)

返回 HTML 文件路径

```php
$htmlFile = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML);
```

### ZANDY_TEMPLATE_CACHE_MOD_EVAL (4)

返回可 eval 的字符串

```php
$code = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_EVAL);
eval($code);
```

### ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS (8)

返回 HTML 内容

```php
$html = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);
echo $html;
```

## 更多信息

- [使用指南](USAGE.md) - 详细的使用说明和最佳实践
- [语法参考](SYNTAX.md) - 完整的模板语法
- [安全指南](SECURITY.md) - 安全使用指南
