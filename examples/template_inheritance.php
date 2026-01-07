<?php
/**
 * Zandy_Template 模板包含示例
 * 
 * 展示如何使用 {template} 和 {include} 包含其他模板
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates';
$cacheDir = __DIR__ . '/cache';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// 准备数据
$pageTitle = '模板包含示例';
$content = '这是主要内容区域';

// 输出包含其他模板的页面
echo Zandy_Template::outString('page.htm', $tplDir, $cacheDir);

