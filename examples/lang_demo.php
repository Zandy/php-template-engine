<?php
/**
 * 语言包功能示例
 * 
 * 展示 {LANG key} 的用法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备语言包数据
$_LANG = array(
    'welcome' => '欢迎使用 Zandy_Template',
    'hello' => '你好',
    'goodbye' => '再见',
    'title' => '语言包示例',
);

$GLOBALS['_LANG'] = $_LANG;

// 输出模板
$html = Zandy_Template::outString('lang_demo.htm', $tplDir, $cacheDir);
echo $html;
