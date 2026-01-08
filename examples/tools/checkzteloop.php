<?php
/**
 * 检查模板文件中 loop 语法的工具
 *
 * 用途：扫描模板目录，检查各模板文件中 loop 语法的使用情况
 *
 * 使用方法：
 *   1. 修改下面的 $tplDir 变量为你的模板目录路径
 *   2. 运行: php examples/tools/checkzteloop.php
 *   3. 查看输出的 HTML 表格
 *
 * 或者通过命令行参数指定目录：
 *   php examples/tools/checkzteloop.php /path/to/templates
 */

date_default_timezone_set("Asia/Shanghai");

define('ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT', '<!--{');
define('ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT', '}-->');

// 配置模板文件目录（可通过命令行参数覆盖）
$tplDir = isset($argv[1]) ? $argv[1] : __DIR__ . '/../templates';

// 配置newold正则
$preg = array(
    array(
        "old" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU",
        "new" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU"
    ),
    array(
        "old" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU",
        "new" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU"
    ),
    array(
        "old" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU",
        "new" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU"
    ),
    array(
        "old" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU",
        "new" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU"
    ),
    array(
        "old" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU",
        "new" => "/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU"
    )
);

function dirTree($dir)
{
    $r = array();
    $d = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($d, RecursiveIteratorIterator::SELF_FIRST) as $name => $object) {
        if ($object->isFile()) {
            $r[] = $name;
        }
    }
    return $r;
}

// 检查目录是否存在
if (!is_dir($tplDir)) {
    die("错误: 模板目录不存在: $tplDir\n使用方法: php " . basename(__FILE__) . " [模板目录路径]\n");
}

$dirTree = dirTree($tplDir);
$sizedt = sizeof($dirTree);

if ($sizedt == 0) {
    die("错误: 模板目录为空: $tplDir\n");
}

$row1 = $row2 = '';
foreach ($preg as $k => $v) {
    $row1 .= "<td colspan=\"2\">regexp" . ($k + 1) . "</td>";
    $row2 .= "<td>new</td><td>old</td>";
}

$r = <<<EOF
<table border="1" cellpadding="3" cellspacing="0">
	<tr>
		<td rowspan="2">template filename(total: $sizedt)</td>
		$row1
	</tr>
	<tr>
		$row2
	</tr>
EOF;

foreach ($dirTree as $filename) {
    $s = file_get_contents($filename);

    $r .= "<tr><td>$filename</td>";

    foreach ($preg as $k => $v) {
        $tp1 = preg_match_all($v['old'], $s, $m);
        $tp2 = preg_match_all($v['new'], $s, $m);
        $color = $tp1 != $tp2 ? ' style="color: red"' : '';

        $r .= "<td$color>$tp1</td><td$color>$tp2</td>";
    }

    $r .= "</tr>";
}

$r .= "</table>";

// output
echo $r;

// output like this
<<<aaa
<table border="1" cellpadding="3" cellspacing="0">
	<tbody>
		<tr>
			<td rowspan="2">template filename</td>
			<td colspan="2">regexp1</td>
			<td colspan="2">regexp2</td>
			<td colspan="2">regexp3</td>
			<td colspan="2">regexp4</td>
			<td colspan="2">regexp5</td>
		</tr>
		<tr>
			<td>new</td>
			<td>old</td>
			<td>new</td>
			<td>old</td>
			<td>new</td>
			<td>old</td>
			<td>new</td>
			<td>old</td>
			<td>new</td>
			<td>old</td>
		</tr>
		<tr>
			<td>/var/www/http/tetx/tpl/account.htm</td>
			<td>0</td>
			<td>0</td>
			<td>0</td>
			<td>0</td>
			<td>1</td>
			<td>1</td>
			<td>0</td>
			<td>0</td>
			<td>0</td>
			<td>0</td>
		</tr>
		<tr>
			<td>/var/www/http/tetx/tpl/address.htm</td>
			<td>2</td>
			<td>2</td>
			<td>0</td>
			<td>0</td>
			<td>1</td>
			<td>1</td>
			<td>0</td>
			<td>0</td>
			<td>0</td>
			<td>0</td>
		</tr>
	</tbody>
</table>
aaa;
