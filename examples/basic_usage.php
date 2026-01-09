<?php
/**
 * 基础使用示例
 * 
 * 展示 Zandy_Template 的基本用法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备数据
$data = array(
    'title' => '欢迎使用 Zandy_Template',
    'user' => array(
        'name' => '张三',
        'email' => 'zhangsan@example.com',
        'age' => 25,
    ),
    'items' => array('苹果', '香蕉', '橙子'),
);

// 将数据放入全局变量
foreach ($data as $key => $value) {
    $GLOBALS[$key] = $value;
}

echo "=== 方式1: outString() ===\n";
$html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir);
echo $html;
echo "\n\n";

echo "=== 方式2: outCache() + include ===\n";
$cacheFile = Zandy_Template::outCache('basic.htm', $tplDir, $cacheDir);
include $cacheFile;
echo "\n";
