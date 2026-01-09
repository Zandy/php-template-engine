<?php
/**
 * 模板包含示例
 * 
 * 展示如何使用 template 和 include 包含其他模板和 PHP 文件
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备数据
$data = array(
    'pageTitle' => '模板包含示例',
    'content' => '这是页面的主要内容。',
);

// 将数据放入全局变量
foreach ($data as $key => $value) {
    $GLOBALS[$key] = $value;
}

// 输出模板
$html = Zandy_Template::outString('page.htm', $tplDir, $cacheDir);
echo $html;
