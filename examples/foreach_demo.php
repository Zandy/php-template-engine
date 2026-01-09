<?php
/**
 * foreach 循环示例
 * 
 * 展示 <!--{foreach ...}--> 和 <!--{foreach-else}--> 的用法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备测试数据
$items = array('苹果', '香蕉', '橙子');
$products = array(
    'apple' => '苹果',
    'banana' => '香蕉',
    'orange' => '橙子',
);
$emptyArray = array();

$GLOBALS['items'] = $items;
$GLOBALS['products'] = $products;
$GLOBALS['emptyArray'] = $emptyArray;

// 输出模板
$html = Zandy_Template::outString('foreach_demo.htm', $tplDir, $cacheDir);
echo $html;
