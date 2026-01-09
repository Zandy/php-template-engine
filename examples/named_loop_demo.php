<?php
/**
 * 命名循环功能演示
 * 
 * 展示如何使用命名循环功能访问循环索引信息
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备测试数据
$users = array(
    array('name' => 'Alice', 'age' => 25, 'posts' => array('Post 1', 'Post 2')),
    array('name' => 'Bob', 'age' => 30, 'posts' => array('Post 1')),
    array('name' => 'Charlie', 'age' => 35, 'posts' => array('Post 1', 'Post 2', 'Post 3')),
);

$items = array('苹果', '香蕉', '橙子');

// 将数据放入全局变量
$GLOBALS['users'] = $users;
$GLOBALS['items'] = $items;

echo "=== 测试 1: 不指定 name（向后兼容） ===\n";
$html1 = Zandy_Template::outString('named_loop_test1.htm', $tplDir, $cacheDir);
echo $html1;
echo "\n\n";

echo "=== 测试 2: 指定 name（单层循环） ===\n";
$html2 = Zandy_Template::outString('named_loop_test2.htm', $tplDir, $cacheDir);
echo $html2;
echo "\n\n";

echo "=== 测试 3: 嵌套循环（都指定 name） ===\n";
$html3 = Zandy_Template::outString('named_loop_test3.htm', $tplDir, $cacheDir);
echo $html3;
echo "\n\n";

echo "=== 测试 4: 三层嵌套循环 ===\n";
$html4 = Zandy_Template::outString('named_loop_test4.htm', $tplDir, $cacheDir);
echo $html4;
echo "\n\n";

echo "=== 测试 5: 混合（外层命名，内层不命名） ===\n";
$html5 = Zandy_Template::outString('named_loop_test5.htm', $tplDir, $cacheDir);
echo $html5;
echo "\n";
