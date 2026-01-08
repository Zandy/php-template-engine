# Zandy_Template 使用示例

> **注意**：本文档是示例目录的使用指南。完整的语法参考和 API 文档请查看根目录的 [README.md](../README.md)。

本目录包含 Zandy_Template 模板引擎的各种使用示例和演示代码。

## 目录结构

```
examples/
├── README.md                    # 本文件
├── basic_usage.php              # 基础使用示例
├── loops_and_conditions.php     # 循环和条件语法示例
├── template_inheritance.php      # 模板包含示例
├── templates/                   # 模板文件目录
│   ├── basic.htm
│   ├── loops.htm
│   ├── header.htm
│   ├── footer.htm
│   ├── page.htm
│   └── sidebar.htm
├── tools/                       # 工具脚本
│   ├── checkzteloop.php        # 检查模板中 loop 语法的工具
│   └── helper.php              # 辅助函数示例
└── cache/                      # 缓存目录（自动生成）
```

## 快速开始

### 1. 基础使用示例

```bash
php examples/basic_usage.php
```

展示：
- 使用 `outString()` 返回 HTML 字符串
- 使用 `outCache()` 返回缓存文件路径
- 使用 `out()` 通用方法

### 2. 循环和条件语法示例

```bash
php examples/loops_and_conditions.php
```

展示：
- 各种循环语法（`loop`、`for`）
- 条件判断（`if`、`elseif`、`else`）
- 变量输出和表达式
- PHP 代码块

### 3. 模板包含示例

```bash
php examples/template_inheritance.php
```

展示：
- 使用 `<!--{template ...}-->` 包含其他模板
- 使用 `<!--{include ...}-->` 包含 PHP 文件
- 模板继承和组合

## 主要 API

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
```

## 模板语法

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

<!--{loop $items $k $v}-->
    <p>{$k}: {$v}</p>
<!--{/loop}-->

<!--{loop $items AS $key => $value}-->
    <p>{$key}: {$value}</p>
<!--{/loop}-->

<!--{for $i = 0; $i < 10; $i++}-->
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
<!--{elseif $other}-->
    <p>其他条件</p>
<!--{else}-->
    <p>默认</p>
<!--{/if}-->
```

### 模板包含
```html
<!--{template header.htm}-->
<!--{include helper.php}-->
<!--{include_once helper.php}-->
```

### 表达式和 PHP 代码
```html
{echo date('Y-m-d H:i:s')}
<!--{php}-->
    // PHP 代码
<!--{/php}-->
<!--{set $var = 'value'}-->
```

### 时间函数
```html
{time}              <!-- 输出当前时间戳 -->
{now}               <!-- 输出当前日期时间：Y-m-d H:i:s -->
{date "Y-m-d"}      <!-- 输出格式化日期 -->
```

### PHP 常量
```html
{PHP_VERSION}       <!-- 输出 PHP 版本 -->
{CONSTANT_NAME}     <!-- 输出任何 PHP 常量（全大写+下划线） -->
```

### 语言包
```html
{LANG welcome}      <!-- 输出语言包中的文本 -->
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

### 循环的 else 分支
```html
<!--{loop $items as $item}-->
    <li>{$item}</li>
<!--{loopelse}-->
    <p>没有数据</p>
<!--{/loop}-->
```

## 工具脚本

### checkzteloop.php
检查模板目录中所有模板文件的 loop 语法使用情况。

**使用方法：**
1. 修改脚本中的 `$tplDir` 变量为你的模板目录
2. 运行脚本：`php examples/tools/checkzteloop.php`
3. 查看输出的 HTML 表格，了解各模板中 loop 语法的使用情况

## 注意事项

1. **缓存目录**：首次运行会自动创建 `cache/` 目录
2. **模板路径**：模板文件路径相对于 `$tplDir` 参数
3. **包含路径**：`<!--{template ...}-->` 和 `<!--{include ...}-->` 的路径相对于当前模板文件
4. **变量作用域**：模板中可以直接使用全局变量，或通过 `extract()` 传入变量
5. **分隔符规范**：
   - 逻辑控制语句（if, for, loop, template, include, php, set 等）使用 `<!--{ }-->`
   - 变量输出（变量、表达式、时间、常量等）使用 `{ }`
6. **安全提示**：`<!--{php}-->` 和 `<!--{set ...}-->` 允许执行任意 PHP 代码，请确保模板来源可信

## 更多信息

- **完整语法参考**：查看根目录的 [README.md](../README.md) 了解所有语法和功能
- **API 文档**：根目录 README.md 包含完整的 API 参考
- **项目主页**：查看根目录 README.md 了解项目特性和快速开始

