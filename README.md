# Zandy_Template 模板引擎

本模板系统属于编译型的，最大特色是变量直接使用 PHP 的变量，毫无学习成本；运行速度快，开发效率高。

## 目录

- [快速开始](#快速开始)
- [模板语法](#模板语法)
  - [循环](#循环)
    - [基本循环语法](#基本循环语法)
    - [命名循环（访问循环索引信息）](#命名循环访问循环索引信息)
- [API 参考](#api-参考)
- [安全提示](#安全提示)
- [变量作用域](#变量作用域)
- [示例代码](#示例代码)
- [测试](#测试)
- [特性](#特性)

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

#### 基本循环语法

Zandy_Template 支持多种循环格式，按以下顺序匹配（从具体到抽象）：

**支持的格式（共12种）：**

1. `<!--{loop $arr AS $key => $value name="loopname"}-->` - 带 AS、=> 和 name
2. `<!--{loop $arr AS $key $value name="loopname"}-->` - 带 AS、key value 和 name
3. `<!--{loop $arr AS $value name="loopname"}-->` - 带 AS、value 和 name
4. `<!--{loop $arr $key => $value name="loopname"}-->` - 带 => 和 name
5. `<!--{loop $arr $key $value name="loopname"}-->` - 带 key value 和 name
6. `<!--{loop $arr $value name="loopname"}-->` - 带 value 和 name
7. `<!--{loop $arr AS $key => $value}-->` - 带 AS 和 =>
8. `<!--{loop $arr AS $key $value}-->` - 带 AS 和 key value
9. `<!--{loop $arr AS $value}-->` - 带 AS 和 value
10. `<!--{loop $arr $key => $value}-->` - 带 =>
11. `<!--{loop $arr $key $value}-->` - 带 key value
12. `<!--{loop $arr $value}-->` - 仅 value

**基本示例：**

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
<!--{loop-else}-->
    <p>没有数据</p>
<!--{/loop}-->

<!--{for $i = 0; $i < 100; $i++}-->
    <span>{$i}</span>
<!--{/for}-->

<!--{loop $categories as $category}-->
    <h3>{$category['name']}</h3>
    <ul>
        <!--{loop $category['items'] as $item}-->
            <li>{$item['name']} - {$item['price']}</li>
        <!--{/loop}-->
    </ul>
<!--{/loop}-->

<!--{foreach $items as $item}-->
    <li>{$item}</li>
<!--{/foreach}-->
```

#### 命名循环（访问循环索引信息）

如果需要访问循环的索引、迭代次数等信息，可以使用命名循环功能。

**核心规则**：如果没有指定 `name`，则索引相关的变量都不可用。

**基本用法：**

```html
<!--{loop $items AS $item name="items"}-->
    <!-- 生成 $_zte_loop_items 变量 -->
    索引: {$_zte_loop_items['index']}        <!-- 从 0 开始 -->
    迭代: {$_zte_loop_items['iteration']}   <!-- 从 1 开始 -->
    第一个: {$_zte_loop_items['first']}      <!-- true/false -->
    最后一个: {$_zte_loop_items['last']}    <!-- true/false -->
    总数: {$_zte_loop_items['length']}       <!-- 数组长度 -->
    
    <div class="item-{$_zte_loop_items['index']}">
        {$item}
        <!--{if $_zte_loop_items['first']}-->(第一个)<!--{/if}-->
        <!--{if $_zte_loop_items['last']}-->(最后一个)<!--{/if}-->
    </div>
<!--{/loop}-->
```

**循环信息变量说明：**

- `$_zte_loop_{name}['index']` - 当前索引（从 0 开始）
- `$_zte_loop_{name}['iteration']` - 当前迭代次数（从 1 开始）
- `$_zte_loop_{name}['first']` - 是否为第一次迭代（布尔值）
- `$_zte_loop_{name}['last']` - 是否为最后一次迭代（布尔值）
- `$_zte_loop_{name}['length']` - 数组总长度

**变量命名说明：**
- 使用 `_zte_loop_` 前缀避免与用户变量冲突
- 下划线前缀 `_` 是 PHP 约定，表示内部/系统变量
- `zte` 是模板引擎的标识符

**嵌套循环示例：**

```html
<!--{loop $users AS $user name="users"}-->
    <div class="user-{$_zte_loop_users['index']}">
        <h3>{$user['name']}</h3>
        
        <!--{loop $user['posts'] AS $post name="posts"}-->
            <div class="post-{$_zte_loop_posts['index']}">
                用户索引: {$_zte_loop_users['index']}  <!-- 访问外层 -->
                文章索引: {$_zte_loop_posts['index']}  <!-- 访问内层 -->
                {$post['title']}
            </div>
        <!--{/loop}-->
    </div>
<!--{/loop}-->
```

**向后兼容：**

不指定 `name` 时，不生成循环信息变量，保持现有行为：

```html
<!--{loop $items AS $item}-->
    <!-- 只循环数据，不生成循环信息变量 -->
    <div>{$item}</div>
<!--{/loop}-->
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
    <!--{break-case 2}-->
        <p>值为 2</p>
    <!--{break-default}-->
        <p>默认值</p>
    <!--{default}-->
        <p>其他默认值</p>
<!--{/switch}-->
```

**说明**：
- `switch` 和 `case` 支持表达式，如 `<!--{switch $x + 1}-->`、`<!--{case $y * 2}-->`
- `break-case` 用于 fall-through：break 后继续执行下一个 case
- `break-default` 用于 fall-through：break 后继续执行 default
- `break` 用于跳出当前 case
- `continue` 在 switch 中无效，仅用于循环

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

**建议**：
- 在生产环境中，如果模板来源不可信，应禁用这些功能
- 使用白名单机制限制可包含的文件
- 严格验证模板路径，防止路径遍历攻击

## 路径验证

模板引擎会自动验证：
- 模板目录必须在 `tplBaseDir` 内
- 缓存目录必须在 `tplCacheBaseDir` 内
- 防止路径遍历攻击

## 变量作用域

模板引擎使用以下机制避免变量污染：

- **循环变量**：使用 `$_zte_loop_{name}` 前缀，避免与用户变量冲突
- **内部变量**：使用 `__zte_` 前缀的临时变量，自动生成唯一标识符
- **变量传递**：通过 `$GLOBALS` 或 `extract()` 传递数据（注意：`extract($GLOBALS)` 会提取所有全局变量）

## 示例代码

查看 `examples/` 目录获取更多使用示例：

- `examples/basic_usage.php` - 基础使用示例
- `examples/loops_and_conditions.php` - 循环和条件语法示例
- `examples/template_inheritance.php` - 模板包含示例
- `examples/named_loop_demo.php` - 命名循环功能演示
- `examples/loop_index_demo.php` - 循环索引演示
- `examples/switch_demo.php` - Switch 语句示例
- `examples/lang_demo.php` - 语言包功能示例
- `examples/include_once_demo.php` - include_once 功能示例
- `examples/include_demo.php` - include 功能示例
- `examples/foreach_demo.php` - foreach 循环示例
- `examples/time_and_constants_demo.php` - 时间函数和 PHP 常量示例
- `examples/template_comment_demo.php` - 模板注释示例
- `examples/api_methods_demo.php` - API 方法示例

详细说明请参考 [examples/README.md](examples/README.md)

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

## 特性

- ✅ 编译型模板，性能优异
- ✅ 使用原生 PHP 变量，零学习成本
- ✅ 支持完整的 PHP 语法
- ✅ 自动缓存管理
- ✅ 路径安全验证
- ✅ 命名循环功能，支持访问循环索引信息
- ✅ 变量作用域隔离，避免变量污染
- ✅ 兼容 PHP 5.6 - PHP 8.4+

## 许可证

查看 [Licence](Licence) 文件了解许可证信息。
