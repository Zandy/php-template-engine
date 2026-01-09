<?php
/**
 * API 方法示例
 * 
 * 展示所有 API 方法的使用：outString(), outCache(), out(), outHTML(), outEval()
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备数据
$data = array(
    'title' => 'API 方法示例',
    'content' => '这是主要内容。',
);

foreach ($data as $key => $value) {
    $GLOBALS[$key] = $value;
}

echo "=== 方式1: outString() - 返回 HTML 字符串（推荐）===\n";
$html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir);
echo $html;
echo "\n\n";

echo "=== 方式2: outCache() - 返回缓存文件路径，然后 include ===\n";
$cacheFile = Zandy_Template::outCache('basic.htm', $tplDir, $cacheDir);
include $cacheFile;
echo "\n\n";

echo "=== 方式3: out() - 通用方法，指定缓存模式 ===\n";
// 返回 HTML 内容
$html = Zandy_Template::out('basic.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);
echo $html;
echo "\n\n";

echo "=== 方式4: outHTML() - 返回 HTML 文件路径或内容 ===\n";
$htmlFile = Zandy_Template::outHTML('basic.htm', $tplDir, $cacheDir);
echo "HTML 文件路径: $htmlFile\n";
echo "\n";

// 返回 HTML 内容
$htmlContent = Zandy_Template::outHTML('basic.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);
echo "HTML 内容:\n";
echo $htmlContent;
echo "\n\n";

echo "=== 方式5: outEval() - 返回可 eval 的字符串 ===\n";
$code = Zandy_Template::outEval('basic.htm', $tplDir);
echo "生成的代码长度: " . strlen($code) . " 字节\n";
echo "代码预览（前200字符）:\n";
echo substr($code, 0, 200) . "...\n";
echo "\n";

// 执行 eval
ob_start();
eval($code);
$result = ob_get_clean();
echo "执行结果:\n";
echo $result;
echo "\n";
