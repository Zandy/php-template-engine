<?php
require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

$value = 1;
$GLOBALS['value'] = $value;

$html = Zandy_Template::outString('switch_case_only.htm', $tplDir, $cacheDir);
echo $html;
