<?php
/**
 * Zandy_Template 循环和条件语法示例
 * 
 * 展示模板引擎的各种循环和条件语法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates';
$cacheDir = __DIR__ . '/cache';

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

$categories = array(
    array(
        'name' => '水果',
        'items' => array(
            array('name' => '苹果', 'price' => '10元'),
            array('name' => '香蕉', 'price' => '8元'),
        ),
    ),
    array(
        'name' => '蔬菜',
        'items' => array(
            array('name' => '白菜', 'price' => '5元'),
            array('name' => '萝卜', 'price' => '6元'),
        ),
    ),
);

$categoriesWithEmpty = array(
    array(
        'name' => '水果',
        'items' => array(
            array('name' => '苹果', 'price' => '10元'),
            array('name' => '香蕉', 'price' => '8元'),
        ),
    ),
    array(
        'name' => '蔬菜',
        'items' => array(), // 空数组，用于测试 loop-else
    ),
    array(
        'name' => '肉类',
        'items' => array(
            array('name' => '猪肉', 'price' => '30元'),
        ),
    ),
);

// 输出示例
echo Zandy_Template::outString('loops.htm', $tplDir, $cacheDir);

