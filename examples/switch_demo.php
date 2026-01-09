<?php
/**
 * Switch 语句示例
 * 
 * 展示 switch、case、break-case、break-default、default、break、continue 的用法
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备测试数据
$status = 1;
$value = 5;
$day = 3;

$GLOBALS['status'] = $status;
$GLOBALS['value'] = $value;
$GLOBALS['day'] = $day;

// 输出模板
$html = Zandy_Template::outString('switch_demo.htm', $tplDir, $cacheDir);
echo $html;
