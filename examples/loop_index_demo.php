<?php
/**
 * 循环索引演示
 * 
 * 展示如何使用命名循环访问循环索引信息
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备测试数据
$users = array(
    array(
        'name' => 'Alice',
        'items' => array(
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ),
    ),
    array(
        'name' => 'Bob',
        'items' => array(
            'key1' => 'value1',
            'key2' => 'value2',
        ),
    ),
);

// 将数据放入全局变量
$GLOBALS['users'] = $users;

// 输出模板
$html = Zandy_Template::outString('loop_index_demo.htm', $tplDir, $cacheDir);
echo $html;
