<?php
/**
 * 安全使用示例
 * 
 * 展示 Zandy_Template 的三种变量传递方式：
 * 1. 完全开放模式（默认，向后兼容）
 * 2. 白名单模式（推荐用于生产环境）
 * 3. 显式传递模式（最安全）
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// 准备数据
$user = array(
    'name' => '张三',
    'email' => 'zhangsan@example.com',
    'age' => 25,
);
$data = array(
    'title' => '安全使用示例',
    'items' => array('苹果', '香蕉', '橙子'),
);

echo "=== 方式1: 完全开放模式（默认，向后兼容）===\n";
echo "特点：模板可以访问所有全局变量\n";
echo "适用：开发环境或模板来源可信的场景\n\n";

// 使用全局变量（最简单，向后兼容）
$GLOBALS['user'] = $user;
$GLOBALS['data'] = $data;
$html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir);
echo $html;
echo "\n\n";

// 清理全局变量
unset($GLOBALS['user'], $GLOBALS['data']);

echo "=== 方式2: 白名单模式（推荐用于生产环境）===\n";
echo "特点：只允许模板访问指定的变量\n";
echo "适用：生产环境，平衡安全性和易用性\n\n";

// 配置白名单
$GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
$GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data'];

// 使用方式不变
$GLOBALS['user'] = $user;
$GLOBALS['data'] = $data;
// 这个变量不会被模板访问（不在白名单中）
$GLOBALS['secret'] = '这是敏感信息，不会被模板访问';

$html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir);
echo $html;
echo "\n\n";

// 清理
unset($GLOBALS['user'], $GLOBALS['data'], $GLOBALS['secret']);
unset($GLOBALS['siteConf']['template_vars_mode'], $GLOBALS['siteConf']['template_vars_whitelist']);

echo "=== 方式3: 显式传递模式（最安全）===\n";
echo "特点：完全控制变量访问，不依赖全局变量\n";
echo "适用：高安全要求的场景\n\n";

// 显式传递变量，完全控制变量访问
$html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data,
    // 只传递需要的变量，其他变量（如 $GLOBALS['secret']）不会被访问
]);

echo $html;
echo "\n\n";

echo "=== 方式4: outCache() + includeTemplate()（推荐用于 outCache 场景）===\n";
echo "特点：安全地 include 模板，支持显式传递变量\n";
echo "适用：需要直接 include 模板的场景\n\n";

// 使用 includeTemplate()，内部处理变量提取和 include
Zandy_Template::includeTemplate('basic.htm', $tplDir, $cacheDir, false, [
    'user' => $user,
    'data' => $data
]);
echo "\n\n";

echo "=== 方式5: outCache() + getTemplateVars()（用于手动 include）===\n";
echo "特点：手动控制 extract 和 include\n";
echo "适用：需要更精细控制的场景\n\n";

// 使用 getTemplateVars() 辅助函数
$cacheFile = Zandy_Template::outCache('basic.htm', $tplDir, $cacheDir);
extract(Zandy_Template::getTemplateVars([
    'user' => $user,
    'data' => $data
]));
include $cacheFile;
echo "\n\n";

echo "=== 安全建议 ===\n";
echo "1. 开发环境：可以使用完全开放模式（默认），方便调试\n";
echo "2. 生产环境：推荐使用白名单模式，平衡安全性和易用性\n";
echo "3. 高安全要求：使用显式传递模式，完全控制变量访问\n";
echo "4. 模板来源不可信：必须使用显式传递模式，并禁用 PHP 代码块\n";
echo "5. 函数/类方法内部：推荐使用显式传递，避免污染全局变量\n";