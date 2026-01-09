<?php
/**
 * Switch 只有 Default 测试
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备测试数据
$value = 999;

$GLOBALS['value'] = $value;

// 输出模板
$html = Zandy_Template::outString('switch_default_only.htm', $tplDir, $cacheDir);
echo $html;
