# 模板语法参考

本文档提供 Zandy_Template 模板引擎的完整语法参考。

## 目录

- [分隔符规范](#分隔符规范)
- [变量输出](#变量输出)
- [循环](#循环)
- [条件判断](#条件判断)
- [Switch 语句](#switch-语句)
- [模板包含](#模板包含)
- [文件包含](#文件包含)
- [表达式和 PHP 代码](#表达式和-php-代码)
- [时间函数](#时间函数)
- [PHP 常量](#php-常量)
- [语言包](#语言包)
- [模板注释](#模板注释)

## 分隔符规范

### 逻辑控制语句

使用 `<!--{ }-->`（HTML 注释包裹，浏览器预览时不破坏结构）

**适用于**：
- `if`, `for`, `foreach`, `loop`, `switch`
- `template`, `include`, `include_once`
- `php`, `set`

**示例**：
```html
<!--{if $condition}-->
<!--{loop $items as $item}-->
<!--{template header.htm}-->
```

### 变量输出

使用 `{ }`（简洁，不破坏结构）

**适用于**：
- `{$var}`, `{$array['key']}`
- `{time}`, `{now}`, `{date}`
- `{echo}`, `{LANG}`

**示例**：
```html
{$user['name']}
{echo date('Y-m-d')}
{time}
```

## 变量输出

### 基本语法

```html
{$variable}                    <!-- 简单变量 -->
{$array['key']}               <!-- 数组访问 -->
{$object->property}           <!-- 对象属性 -->
{$array['key1']['key2']}      <!-- 多维数组 -->
{$object->method()->property} <!-- 方法链 -->
```

### 示例

```html
<!-- 输出用户信息 -->
<h1>{$user['name']}</h1>
<p>邮箱: {$user['email']}</p>
<p>年龄: {$user['profile']['age']}</p>
```

## 循环

### 基本循环语法

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

### 基本示例

```html
<!-- loop 循环 -->
<!--{loop $items as $item}-->
    <li>{$item}</li>
<!--{/loop}-->

<!-- loop 循环（带键值） -->
<!--{loop $items $k $v}-->
    <p>{$k}: {$v}</p>
<!--{/loop}-->

<!-- loop 循环（AS 语法） -->
<!--{loop $items AS $key => $value}-->
    <p>{$key}: {$value}</p>
<!--{/loop}-->

<!-- loop-else（空数组处理） -->
<!--{loop array() as $v}-->
    <li>{$v}</li>
<!--{loop-else}-->
    <p>没有数据</p>
<!--{/loop}-->

<!-- for 循环 -->
<!--{for $i = 0; $i < 100; $i++}-->
    <span>{$i}</span>
<!--{/for}-->

<!-- 嵌套循环 -->
<!--{loop $categories as $category}-->
    <h3>{$category['name']}</h3>
    <ul>
        <!--{loop $category['items'] as $item}-->
            <li>{$item['name']} - {$item['price']}</li>
        <!--{/loop}-->
    </ul>
<!--{/loop}-->

<!-- foreach 循环 -->
<!--{foreach $items as $item}-->
    <li>{$item}</li>
<!--{/foreach}-->
```

### 命名循环（访问循环索引信息）

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

## 条件判断

### if/elseif/else

```html
<!--{if $condition}-->
    <p>条件为真</p>
<!--{elseif $other > 100}-->
    <p>其他条件</p>
<!--{else}-->
    <p>默认</p>
<!--{/if}-->
```

### 支持表达式

条件判断支持完整的 PHP 表达式：

```html
<!--{if $user['age'] >= 18 && $user['status'] == 'active'}-->
    <p>成年活跃用户</p>
<!--{/if}-->
```

## Switch 语句

### 基本语法

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

### 特性说明

- `switch` 和 `case` 支持表达式，如 `<!--{switch $x + 1}-->`、`<!--{case $y * 2}-->`
- `break-case` 用于 fall-through：break 后继续执行下一个 case
- `break-default` 用于 fall-through：break 后继续执行 default
- `break` 用于跳出当前 case
- `continue` 在 switch 中无效，仅用于循环

### 示例

```html
<!--{switch $status}-->
    <!--{case 'active'}-->
        <span class="active">活跃</span>
    <!--{break-case 'pending'}-->
        <span class="pending">待审核</span>
    <!--{break-default}-->
        <span class="inactive">未激活</span>
    <!--{default}-->
        <span>未知状态</span>
<!--{/switch}-->
```

## 模板包含

### 基本语法

```html
<!--{template header.htm}-->
<!--{template ../common/footer.htm}-->
```

### 路径规则

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

## 文件包含

### include/include_once

```html
<!--{include helper.php}-->
<!--{include_once helper.php}-->
```

**注意**：包含的文件必须是 PHP 文件，会被直接执行。

## 表达式和 PHP 代码

### echo 表达式

```html
{echo date('Y-m-d H:i:s')}
{echo $a ? 'a' : 'b'}
{echo xxx($aaa, $bbb)}
```

### PHP 代码块

```html
<!--{php}-->
    if ($a) { 
        echo 'a'; 
    }
<!--{/php}-->
```

### set 变量

```html
<!--{set $var = 'value'}-->
<!--{set $count = count($items)}-->
```

## 时间函数

### time

输出当前时间戳：

```html
{time}
```

### now

输出当前日期时间（格式：Y-m-d H:i:s）：

```html
{now}
```

### date

输出格式化日期：

```html
{date "Y-m-d"}
{date 'Y-m-d H:i:s'}  <!-- 支持单引号 -->
```

## PHP 常量

输出 PHP 常量（全大写+下划线）：

```html
{PHP_VERSION}       <!-- 输出 PHP 版本 -->
{CONSTANT_NAME}     <!-- 输出任何 PHP 常量 -->
```

## 语言包

### 标准写法

```html
{$_LANG['title']}
```

### 简化写法

```html
{LANG title}
```

**说明**：
- 如果 `$_LANG['title']` 存在，输出其值
- 如果不存在且开启了调试模式，输出 `#title#`
- 否则输出 `title`

## 模板注释

模板注释在编译时会被移除，不会出现在最终输出中：

```html
<!--{*这是注释内容*}-->
```

## 更多信息

- [使用指南](USAGE.md) - 详细的使用说明和最佳实践
- [API 参考](API.md) - 完整的 API 文档
- [安全指南](SECURITY.md) - 安全使用指南
