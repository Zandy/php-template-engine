# Zandy_Template 模板引擎

本模板系统属于编译型的，最大特色是变量直接使用 PHP 的变量，毫无学习成本；运行速度快，开发效率高。

## 快速开始

### 基本使用

```php
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

## 模板语法

### 分隔符规范

1. **逻辑控制语句**使用 `<!--{ }-->`（HTML 注释包裹，浏览器预览时不破坏结构）
   - 适用于：`if`, `for`, `foreach`, `loop`, `switch`, `template`, `include`, `php`, `set` 等
   - 示例：`<!--{if $condition}-->`, `<!--{loop $items as $item}-->`, `<!--{template header.htm}-->`

2. **变量输出**使用 `{ }`（简洁，不破坏结构）
   - 适用于：`{$var}`, `{$array['key']}`, `{time}`, `{now}`, `{date}`, `{echo}`, `{LANG}` 等
   - 示例：`{$user['name']}`, `{echo date('Y-m-d')}`, `{time}`

### 变量输出

```html
{$variable}
{$array['key']}
{$object->property}
{$array['key1']['key2'][$var][$obj->prop]}
{$object->method()->property}
```

### 循环

```html
<!--{loop $items as $item}-->
    <li>{$item}</li>
<!--{/loop}-->

<!--{loop $items $k $v}-->
    <p>{$k}: {$v}</p>
<!--{/loop}-->

<!--{loop $items AS $key => $value}-->
    <p>{$key}: {$value}</p>
<!--{/loop}-->

<!--{loop array() as $v}-->
    <li>{$v}</li>
<!--{loopelse}-->
    <p>没有数据</p>
<!--{/loop}-->

<!--{for $i = 0; $i < 100; $i++}-->
    <span>{$i}</span>
<!--{/for}-->

<!--{foreach $items as $item}-->
    <li>{$item}</li>
<!--{/foreach}-->
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

### Switch 语句

```html
<!--{switch $value}-->
    <!--{case 1}-->
        <p>值为 1</p>
    <!--{break case 2}-->
        <p>值为 2</p>
    <!--{default}-->
        <p>默认值</p>
<!--{/switch}-->
```

### 模板包含

```html
<!--{template header.htm}-->
<!--{template ../common/footer.htm}-->
```

**注意**：子模板的路径按父模板的相对路径计算。

例如目录结构：
```
common/
  ├── header.htm
  └── footer.htm
index/
  ├── index.htm
  └── menu.htm
```

在 `index/index.htm` 中：
```html
<!--{template ../common/header.htm}-->
<!--{template menu.htm}-->
<!--{template ../common/footer.htm}-->
```

### 文件包含

```html
<!--{include helper.php}-->
<!--{include_once helper.php}-->
```

### 表达式和 PHP 代码

```html
{echo date('Y-m-d H:i:s')}
{echo $a ? 'a' : 'b'}
{echo xxx($aaa, $bbb)}

<!--{php}-->
    if ($a) { 
        echo 'a'; 
    }
<!--{/php}-->

<!--{set $var = 'value'}-->
```

### 时间函数

```html
{time}              <!-- 输出当前时间戳 -->
{now}               <!-- 输出当前日期时间：Y-m-d H:i:s -->
{date "Y-m-d"}      <!-- 输出格式化日期 -->
{date 'Y-m-d H:i:s'} <!-- 支持单引号 -->
```

### PHP 常量

```html
{PHP_VERSION}       <!-- 输出 PHP 版本 -->
{CONSTANT_NAME}     <!-- 输出任何 PHP 常量（全大写+下划线） -->
```

### 语言包

```html
{$_LANG['title']}   <!-- 标准 PHP 写法 -->
{LANG title}        <!-- 简化写法，自动处理未定义情况 -->
```

### 其他

```html
<!--{*这是注释内容*}-->  <!-- 模板注释 -->
```

## API 参考

### outString()

返回填充数据后的 HTML 字符串（推荐）

```php
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
echo $html;
```

### outCache()

返回编译后的缓存文件路径，然后 include

```php
$cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
include $cacheFile;
```

### out()

通用输出方法，可指定输出模式

```php
// 返回 HTML 内容
$html = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);

// 返回缓存文件路径
$cacheFile = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_PHPC);

// 返回可 eval 的字符串
$code = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_EVAL);
eval($code);
```

### outHTML()

返回 HTML 文件路径或内容

```php
$htmlFile = Zandy_Template::outHTML('template.htm', $tplDir, $cacheDir);
```

### outEval()

返回可 eval 的字符串

```php
$code = Zandy_Template::outEval('template.htm', $tplDir);
eval($code);
```

## 缓存模式常量

- `ZANDY_TEMPLATE_CACHE_MOD_PHPC` (1) - 返回编译后的 PHP 文件路径
- `ZANDY_TEMPLATE_CACHE_MOD_HTML` (2) - 返回 HTML 文件路径
- `ZANDY_TEMPLATE_CACHE_MOD_EVAL` (4) - 返回可 eval 的字符串
- `ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS` (8) - 返回 HTML 内容

## 安全提示

⚠️ **重要**：以下语法允许执行任意 PHP 代码，请确保模板来源可信：

- `<!--{php}-->...<!--{/php}-->` - 执行任意 PHP 代码
- `<!--{set ...}-->` - 设置变量，可执行代码
- `<!--{include ...}-->` - 包含 PHP 文件

## 路径验证

模板引擎会自动验证：
- 模板目录必须在 `tplBaseDir` 内
- 缓存目录必须在 `tplCacheBaseDir` 内
- 防止路径遍历攻击

## 示例代码

查看 `examples/` 目录获取更多使用示例：

- `examples/basic_usage.php` - 基础使用示例
- `examples/loops_and_conditions.php` - 循环和条件语法示例
- `examples/template_inheritance.php` - 模板包含示例

详细说明请参考 [examples/README.md](examples/README.md)

## 特性

- ✅ 编译型模板，性能优异
- ✅ 使用原生 PHP 变量，零学习成本
- ✅ 支持完整的 PHP 语法
- ✅ 自动缓存管理
- ✅ 路径安全验证
- ✅ 兼容 PHP 5.6 - PHP 8.4+

## 许可证

查看 [Licence](Licence) 文件了解许可证信息。
