<?php
/**
 * 时间函数和 PHP 常量示例
 * 
 * 展示 {time}, {now}, {date} 和 {CONSTANT_NAME} 的用法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备数据
$data = array(
    'title' => '时间函数和常量示例',
);

foreach ($data as $key => $value) {
    $GLOBALS[$key] = $value;
}

// 输出模板
$html = Zandy_Template::outString('time_and_constants_demo.htm', $tplDir, $cacheDir);
echo $html;
