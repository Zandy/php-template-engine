<?php
/**
 * Zandy_Template 基础使用示例
 * 
 * 本示例展示模板引擎的基本用法
 */

// 引入模板引擎
require_once __DIR__ . '/../Template.php';

// 配置模板目录和缓存目录
$tplDir = __DIR__ . '/templates';
$cacheDir = __DIR__ . '/cache';

// 确保缓存目录存在
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// 准备数据
$title = '欢迎使用 Zandy_Template';
$user = array(
    'name' => '张三',
    'email' => 'zhangsan@example.com',
    'age' => 25
);
$items = array('苹果', '香蕉', '橙子');

// 方式1: 使用 outString() - 返回 HTML 字符串（推荐）
echo "=== 方式1: outString() ===\n";
$html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir);
echo $html;
echo "\n\n";

// 方式2: 使用 outCache() - 返回缓存文件路径，然后 include
echo "=== 方式2: outCache() + include ===\n";
$cacheFile = Zandy_Template::outCache('basic.htm', $tplDir, $cacheDir);
include $cacheFile;
echo "\n\n";

// 方式3: 使用 out() - 通用方法，可指定输出模式
echo "=== 方式3: out() 方法 ===\n";
$html2 = Zandy_Template::out('basic.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);
echo $html2;
echo "\n";

