<?php
/**
 * include_once 功能示例
 * 
 * 展示 <!--{include_once ...}--> 的用法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备数据
$data = array(
    'title' => 'include_once 示例',
    'content' => '这是主要内容。',
);

foreach ($data as $key => $value) {
    $GLOBALS[$key] = $value;
}

// 输出模板
$html = Zandy_Template::outString('include_once_demo.htm', $tplDir, $cacheDir);
echo $html;
