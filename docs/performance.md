# Zandy_Template 性能优化指南

> 本文档提供 Zandy_Template 模板引擎的性能优化建议和最佳实践。

## 目录

1. [性能特性](#性能特性)
2. [缓存机制](#缓存机制)
3. [编译优化](#编译优化)
4. [运行时优化](#运行时优化)
5. [内存优化](#内存优化)
6. [性能测试](#性能测试)

---

## 性能特性

Zandy_Template 是一个**编译型模板引擎**，具有以下性能特性：

- ✅ **编译时解析**：模板在首次使用时编译为 PHP 代码，后续直接执行 PHP
- ✅ **零运行时开销**：编译后的代码是纯 PHP，性能接近原生 PHP
- ✅ **智能缓存**：自动检测模板文件修改，只在必要时重新编译
- ✅ **高效字符串处理**：使用 heredoc 语法，字符串处理性能优异

**性能对比**：
- 编译型模板引擎（Zandy_Template）：接近原生 PHP 性能
- 解释型模板引擎（如 Smarty）：运行时解析，性能较低

---

## 缓存机制

### 缓存工作原理

1. **首次编译**：模板文件 → 解析 → PHP 代码 → 缓存文件
2. **后续使用**：检查模板文件修改时间 → 使用缓存（如果未修改）
3. **强制刷新**：设置 `$forceRefreshCache = true` 强制重新编译

### 缓存使用建议

```php
// ✅ 推荐：充分利用缓存
// 首次编译（较慢，约 30-50ms）
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);

// 后续使用（很快，约 0.5-2ms，性能提升 90%+）
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);

// 模板修改后，强制刷新（开发环境）
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, true);
```

### 缓存目录优化

```php
// ✅ 推荐：使用独立的缓存目录
$cacheDir = '/path/to/cache/';

// 建议：
// 1. 使用 SSD 存储（提升 I/O 性能）
// 2. 定期清理旧缓存（建议在部署时执行）
// 3. 设置适当的文件权限（避免权限问题）

// 清理旧缓存示例
function cleanOldCache($cacheDir, $maxAge = 3600) {
    $files = glob($cacheDir . '**/*.php');
    foreach ($files as $file) {
        if (time() - filemtime($file) > $maxAge) {
            unlink($file);
        }
    }
}
```

---

## 编译优化

### 1. 模板文件大小

```php
// ✅ 推荐：将大模板拆分为多个小模板
// 大模板（不推荐）
// template.htm (5000+ 行)

// 小模板（推荐）
// header.htm
// content.htm
// footer.htm

// 使用模板包含
<!--{template header.htm}-->
<!--{template content.htm}-->
<!--{template footer.htm}-->
```

**原因**：
- 小模板编译更快
- 便于维护和复用
- 可以独立缓存

### 2. 减少正则表达式复杂度

```php
// ✅ 推荐：使用简单的模板语法
{$var}                    // 简单变量
{$array['key']}           // 数组访问

// ⚠️ 避免：过度复杂的表达式
{echo complex_function_call($var1, $var2, $var3)}  // 复杂表达式会增加解析时间
```

### 3. 模板注释

```php
// ✅ 推荐：使用模板注释（编译时移除，不影响性能）
<!--{* 这是注释，编译后会被移除 *}-->

// ❌ 不推荐：使用 HTML 注释（会增加输出内容）
<!-- 这是 HTML 注释，会输出到页面 -->
```

---

## 运行时优化

### 1. 变量传递优化

```php
// ✅ 推荐：只传递需要的变量
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,      // 只传递模板需要的变量
    'data' => $data
]);

// ❌ 不推荐：传递大量不需要的变量
$html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data,
    'unused1' => $large_array,  // 模板不需要，浪费内存
    'unused2' => $large_object
]);
```

### 2. 循环优化

```php
// ✅ 推荐：在循环中避免复杂计算
<!--{loop $items as $item}-->
    {$item['name']}  <!-- 直接访问，性能好 -->
<!--{/loop}-->

// ⚠️ 避免：在循环中进行复杂计算
<!--{loop $items as $item}-->
    {echo complex_function($item)}  <!-- 每次循环都执行，性能差 -->
<!--{/loop}-->

// ✅ 推荐：在循环外预处理数据
<?php
$processed_items = array_map(function($item) {
    return complex_function($item);
}, $items);
?>
<!--{loop $processed_items as $item}-->
    {$item}
<!--{/loop}-->
```

### 3. 条件判断优化

```php
// ✅ 推荐：使用简单的条件判断
<!--{if $status == 'active'}-->
    激活
<!--{/if}-->

// ⚠️ 避免：复杂的条件判断
<!--{if complex_function($var1, $var2) && another_function($var3)}-->
    内容
<!--{/if}-->
```

---

## 内存优化

### 1. 大数组处理

```php
// ✅ 推荐：分批处理大数组
$large_array = [/* 10000+ 项 */];

// 分批处理
$chunk_size = 100;
for ($i = 0; $i < count($large_array); $i += $chunk_size) {
    $chunk = array_slice($large_array, $i, $chunk_size);
    $html = Zandy_Template::outString('list.htm', $tplDir, $cacheDir, false, [
        'items' => $chunk
    ]);
    echo $html;
}

// ❌ 不推荐：一次性处理大数组
$html = Zandy_Template::outString('list.htm', $tplDir, $cacheDir, false, [
    'items' => $large_array  // 可能导致内存溢出
]);
```

### 2. 输出缓冲优化

```php
// ✅ 推荐：使用输出缓冲控制内存
ob_start();
Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir);
$html = ob_get_clean();

// 对于大模板，可以设置输出缓冲大小
ini_set('output_buffering', '4096');
```

---

## 性能测试

### 基准测试示例

```php
// 测试编译性能
$start = microtime(true);
Zandy_Template::outCache('template.htm', $tplDir, $cacheDir, true);
$compile_time = (microtime(true) - $start) * 1000;  // 毫秒

// 测试运行时性能
$iterations = 100;
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
}
$runtime_time = ((microtime(true) - $start) / $iterations) * 1000;  // 毫秒/次

echo "编译时间: {$compile_time}ms\n";
echo "运行时间: {$runtime_time}ms/次\n";
```

### 性能指标参考

- **编译时间**：< 50ms（中等模板，< 1000 行）
- **运行时间**：< 2ms/次（使用缓存）
- **内存使用**：< 10MB（中等模板）

---

## 总结

1. **充分利用缓存**：避免重复编译，性能提升 90%+
2. **拆分大模板**：将大模板拆分为多个小模板，提升编译速度
3. **优化变量传递**：只传递需要的变量，减少内存使用
4. **循环优化**：避免在循环中进行复杂计算
5. **定期清理缓存**：保持缓存目录整洁，提升 I/O 性能

更多详细信息，请参考：
- [使用指南](usage.md)
- [API 参考](api.md)
