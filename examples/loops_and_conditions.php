<?php
/**
 * 循环和条件语法示例
 * 
 * 展示各种循环和条件判断的用法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备测试数据
$users = array(
    array('name' => '张三', 'age' => 25),
    array('name' => '李四', 'age' => 30),
    array('name' => '王五', 'age' => 20),
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
            array('name' => '苹果', 'price' => 10),
            array('name' => '香蕉', 'price' => 8),
        ),
    ),
    array(
        'name' => '蔬菜',
        'items' => array(
            array('name' => '白菜', 'price' => 5),
        ),
    ),
);

$categoriesWithEmpty = array(
    array(
        'name' => '水果',
        'items' => array(
            array('name' => '苹果', 'price' => 10),
        ),
    ),
    array(
        'name' => '空分类',
        'items' => array(),
    ),
);

// 将数据放入全局变量
$GLOBALS['users'] = $users;
$GLOBALS['products'] = $products;
$GLOBALS['emptyArray'] = $emptyArray;
$GLOBALS['categories'] = $categories;
$GLOBALS['categoriesWithEmpty'] = $categoriesWithEmpty;

// 输出模板
$html = Zandy_Template::outString('loops.htm', $tplDir, $cacheDir);
echo $html;
