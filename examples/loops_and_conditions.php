<?php
/**
 * Zandy_Template 循环和条件语法示例
 * 
 * 展示模板引擎的各种循环和条件语法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates';
$cacheDir = __DIR__ . '/cache';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// 准备测试数据
$users = array(
    array('id' => 1, 'name' => '张三', 'age' => 25),
    array('id' => 2, 'name' => '李四', 'age' => 30),
    array('id' => 3, 'name' => '王五', 'age' => 20),
);

$products = array(
    'apple' => '苹果',
    'banana' => '香蕉',
    'orange' => '橙子',
);

$emptyArray = array();

// 输出示例
echo Zandy_Template::outString('loops.htm', $tplDir, $cacheDir);

