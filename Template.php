<?php
/**
 * @version $Id: Template.php 45318 2013-03-19 13:21:07Z zandy $
 *
 * ! Zandy_Template 模板系统横空出世！——这么强的东西，应该搞个发明奖什么的了，哈哈——自娱一下
 * Filename : Zandy_Template.php
 * @author  : Zandy
 * Create   : 20060211
 * LastMod  : 20060227 20060301 20060313 20060329
 * Usage    :
 * Desc     : 20060227 由 TPL 改名为 ZandyTemplate，20070528 由 TPL 改名为 Zandy_Template
 * 版权所有，违者必揪！
 */
//ini_set('display_errors', 1);
//error_reporting(E_ALL ^ E_NOTICE);
/**
 * 模板语法说明：
 *
 * 分隔符使用规范：
 * 1. 逻辑控制语句使用 <!--{ }--> (HTML 注释包裹，浏览器预览时不破坏结构)
 *    适用于：if, for, foreach, loop, switch, template, include, php, set 等
 *    示例：<!--{if $condition}-->, <!--{loop $items as $item}-->, <!--{template header.htm}-->
 *    块级语法：<!--{php}-->...<!--{/php}-->
 *
 * 2. 变量输出使用 { } (简洁，不破坏结构)
 *    适用于：{$var}, {$array['key']}, {time}, {now}, {date}, {echo}, {LANG} 等
 *    示例：{$user['name']}, {echo date('Y-m-d')}, {time}
 *
 * 模板示例：
 *
 * <!--{include tpl/header.htm}-->
 *
 * {$helloTPL}
 *
 * <!--{if $showContent}-->
 *     <p>内容显示</p>
 * <!--{/if}-->
 *
 * <!--{include tpl/footer.htm}-->
 *
 * 输出方式：
 * 1. include Zandy_Template::outCache($tplFileName, $tplDir);
 * 2. echo Zandy_Template::outHTML($tplFileName, $tplDir);
 * 3. eval(Zandy_Template::outEval($tplFileName, $tplDir));
 *
 * 或者使用通用方法：
 * include_once(Zandy_Template::out($tplName, $tplDir, '', false, ZANDY_TEMPLATE_CACHE_MOD_PHPC));
 * echo Zandy_Template::out($tplName, $tplDir, '', false, ZANDY_TEMPLATE_CACHE_MOD_HTML);
 * eval(Zandy_Template::out($tplName, $tplDir, '', false, ZANDY_TEMPLATE_CACHE_MOD_EVAL));
 *
 * 此文件最后有更多使用方法例子
 */
// {{{
#!defined('TPL_BASE_DIR')    && die('please define tpl const var TPL_BASE_DIR');
#!defined('TPL_DIR')         && die('please define tpl const var TPL_DIR');
#!defined('TPL_COMPILE_DIR') && die('please define tpl const var TPL_COMPILE_DIR');
// }}}
defined('Zandy_Template') || define('Zandy_Template', true);
defined('ZANDY_TEMPLATE_CACHE_MOD_PHPC') || define('ZANDY_TEMPLATE_CACHE_MOD_PHPC', 1); // parsed a file and return the result file name
defined('ZANDY_TEMPLATE_CACHE_MOD_HTML') || define('ZANDY_TEMPLATE_CACHE_MOD_HTML', 2); // cache as a html file and return the html file name
defined('ZANDY_TEMPLATE_CACHE_MOD_EVAL') || define('ZANDY_TEMPLATE_CACHE_MOD_EVAL', 4); // parsed a file and return as a string for eval
defined('ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS') || define('ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS', 8); // cache as a html file and return the html file content
defined('ZANDY_TEMPLATE_CACHE_SIMPLE') || define('ZANDY_TEMPLATE_CACHE_SIMPLE', 0); // 可以设为 0 或 1，与 $GLOBALS['siteConf']['EOF'] 结合使用，取 异或 的值
/**
 * 模板分隔符定义
 *
 * 设计原则：
 * - 逻辑控制语句使用 <!--{ }--> (HTML 注释包裹，浏览器预览时不破坏结构)
 *   适用于：if, for, foreach, loop, switch, template, include, php, set 等
 *   块级语法：<!--{php}-->...<!--{/php}-->
 * - 变量输出使用 { } (简洁，不破坏结构)
 *   适用于：{$var}, {$array['key']}, {time}, {now}, {date}, {LANG} 等
 */
define('ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT', '<!--{'); // 逻辑控制语句左分隔符
define('ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT', '}-->'); // 逻辑控制语句右分隔符
define('ZANDY_TEMPLATE_DELIMITER_VAR_LEFT', '{'); // 变量输出左分隔符
define('ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT', '}'); // 变量输出右分隔符


#define('ZANDY_TEMPLATE_DELIMITER_VAR_LEFT_QUOTE',  preg_quote(ZANDY_TEMPLATE_DELIMITER_VAR_LEFT)); // 20061226
#define('ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT_QUOTE', preg_quote(ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT)); // 20061226
#defined('ZANDY_TEMPLATE_INONEFILE') || define('ZANDY_TEMPLATE_INONEFILE', FALSE); // 20060408
class Zandy_Template
{
    /**
     * 构造函数（PHP 5+）
     *
     * Zandy_Template 类使用静态方法，构造函数为空。
     * 保留此方法以支持实例化语法（虽然不推荐）。
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * 构造函数（PHP 4 兼容）
     *
     * PHP 4 风格的构造函数，用于向后兼容。
     * Zandy_Template 类使用静态方法，构造函数为空。
     *
     * @return void
     */
    public function Zandy_Template()
    {
        //self::__construct();
        $this->__construct();
    }

    /**
     * 错误处理函数：输出错误信息并终止脚本执行
     *
     * 当模板引擎遇到严重错误时调用此方法，会：
     * 1. 设置 HTTP 503 状态码
     * 2. 输出错误信息
     * 3. 可选择发送告警邮件
     * 4. 终止脚本执行
     *
     * @param string $msg 错误信息
     * @param bool $send_email 是否发送告警邮件（默认 false）
     * @return void 此方法不会返回，会终止脚本执行
     */
    public static function halt($msg, $send_email = false)
    {
        @header('HTTP/1.1 503 Service Temporarily Unavailable');
        @header('Status: 503 Service Temporarily Unavailable');
        if ($send_email) {
            self::sendAlarmEmail($msg);
        }
        echo $msg;
        die();
    }

    /**
     * 根据配置提取模板变量
     *
     * 支持三种模式：
     * 1. 'open' (默认): 完全开放模式，提取所有全局变量（向后兼容）
     * 2. 'whitelist': 白名单模式，只提取指定的变量（推荐用于生产环境）
     * 3. 'explicit': 显式模式，只使用显式传递的变量（最安全）
     *
     * 配置方式：
     * $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
     * $GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data', 'items'];
     *
     * @param array|null $explicitVars 显式传递的变量（优先级最高，如果提供则忽略配置）
     * @return array 提取的变量数组
     */
    private static function extractTemplateVars($explicitVars = null)
    {
        $siteConf = isset($GLOBALS['siteConf']) ? $GLOBALS['siteConf'] : array();

        // 如果提供了显式变量，直接使用（优先级最高）
        if ($explicitVars !== null && is_array($explicitVars)) {
            // 始终包含 siteConf（模板引擎需要）
            if (isset($GLOBALS['siteConf'])) {
                $explicitVars['siteConf'] = $GLOBALS['siteConf'];
            }
            return $explicitVars;
        }

        // 根据配置模式提取变量
        $mode = isset($siteConf['template_vars_mode']) ? $siteConf['template_vars_mode'] : 'open';

        switch ($mode) {
            case 'whitelist':
                // 白名单模式：只提取指定的变量
                $whitelist = isset($siteConf['template_vars_whitelist'])
                    ? $siteConf['template_vars_whitelist']
                    : [];
                if (empty($whitelist)) {
                    // 如果白名单为空，回退到完全开放模式（向后兼容）
                    return $GLOBALS;
                }
                $vars = [];
                foreach ($whitelist as $key) {
                    if (isset($GLOBALS[$key])) {
                        $vars[$key] = $GLOBALS[$key];
                    }
                }
                // 始终包含 siteConf（模板引擎需要）
                if (isset($GLOBALS['siteConf'])) {
                    $vars['siteConf'] = $GLOBALS['siteConf'];
                }
                return $vars;

            case 'explicit':
                // 显式模式：只使用显式传递的变量
                // 如果没有显式传递，只包含 siteConf
                $vars = [];
                if (isset($GLOBALS['siteConf'])) {
                    $vars['siteConf'] = $GLOBALS['siteConf'];
                }
                return $vars;

            case 'open':
            default:
                // 完全开放模式：提取所有全局变量（默认，向后兼容）
                return $GLOBALS;
        }
    }

    /**
     * 通用输出方法：根据缓存模式返回不同的结果
     *
     * 这是一个通用方法，根据 $cacheMod 参数调用不同的输出方法：
     * - ZANDY_TEMPLATE_CACHE_MOD_PHPC (1): 返回 PHP 缓存文件路径（用于 include）
     * - ZANDY_TEMPLATE_CACHE_MOD_HTML (2): 返回 HTML 文件路径
     * - ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS (8): 返回 HTML 内容字符串
     * - ZANDY_TEMPLATE_CACHE_MOD_EVAL (4): 返回可 eval 的 PHP 代码字符串
     *
     * @param string $tplFileName 模板文件名（如 'header.htm'）
     * @param string $tplDir 模板目录路径（可选，默认使用 $GLOBALS['siteConf']['tplDir']）
     * @param string $cacheDir 缓存目录路径（可选，默认使用 $GLOBALS['siteConf']['tplCacheBaseDir']）
     * @param bool $forceRefreshCache 是否强制刷新缓存（默认 false）
     * @param int $cacheMod 缓存模式（默认 ZANDY_TEMPLATE_CACHE_MOD_PHPC）：
     *   - ZANDY_TEMPLATE_CACHE_MOD_PHPC (1): 返回 PHP 缓存文件路径
     *   - ZANDY_TEMPLATE_CACHE_MOD_HTML (2): 返回 HTML 文件路径
     *   - ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS (8): 返回 HTML 内容字符串
     *   - ZANDY_TEMPLATE_CACHE_MOD_EVAL (4): 返回可 eval 的 PHP 代码字符串
     * @param array|null $vars 显式传递的变量（可选，仅用于 HTML 模式）
     * @return string|false 根据 $cacheMod 返回文件路径、内容字符串或 PHP 代码，失败返回 false
     *
     * @example
     * // 返回 PHP 缓存文件路径（用于 include）
     * $cacheFile = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_PHPC);
     * include $cacheFile;
     *
     * // 返回 HTML 文件路径
     * $htmlFile = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML);
     * echo file_get_contents($htmlFile);
     *
     * // 返回 HTML 内容字符串
     * $html = Zandy_Template::out('template.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS, ['user' => $user]);
     * echo $html;
     *
     * // 返回可 eval 的 PHP 代码
     * $code = Zandy_Template::out('template.htm', $tplDir, false, false, ZANDY_TEMPLATE_CACHE_MOD_EVAL);
     * eval($code);
     */
    public static function out($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $cacheMod = ZANDY_TEMPLATE_CACHE_MOD_PHPC, $vars = null)
    {
        $mods = ZANDY_TEMPLATE_CACHE_MOD_PHPC | ZANDY_TEMPLATE_CACHE_MOD_HTML | ZANDY_TEMPLATE_CACHE_MOD_EVAL;
        switch ($mods & $cacheMod) {
            case ZANDY_TEMPLATE_CACHE_MOD_PHPC:
                return Zandy_Template::outCache($tplFileName, $tplDir, $cacheDir, $forceRefreshCache);
                break;
            case ZANDY_TEMPLATE_CACHE_MOD_HTML:
            case ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS:
                return Zandy_Template::outHTML($tplFileName, $tplDir, $cacheDir, $forceRefreshCache, $mods & $cacheMod, $vars);
                break;
            case ZANDY_TEMPLATE_CACHE_MOD_EVAL:
                return Zandy_Template::outEval($tplFileName, $tplDir);
                break;
            default:
                return Zandy_Template::outCache($tplFileName, $tplDir, $cacheDir, $forceRefreshCache);
                break;
        }
    }

    /**
     * 返回填充数据后的 HTML 字符串（推荐）
     *
     * @param string $tplFileName 模板文件名
     * @param string $tplDir 模板目录
     * @param string $cacheDir 缓存目录
     * @param bool $forceRefreshCache 是否强制刷新缓存
     * @param array|null $vars 显式传递的变量（可选，如果提供则只使用这些变量，忽略全局变量配置）
     * @return string HTML 字符串
     *
     * 使用示例：
     * // 方式1：使用全局变量（向后兼容）
     * $GLOBALS['user'] = $user;
     * $html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
     *
     * // 方式2：显式传递变量（更安全）
     * $html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir, false, ['user' => $user]);
     *
     * // 方式3：配置白名单模式
     * $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
     * $GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data'];
     * $html = Zandy_Template::outString('template.htm', $tplDir, $cacheDir);
     */
    public static function outString($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $vars = null)
    {
        $f = self::outCache($tplFileName, $tplDir, $cacheDir, $forceRefreshCache);
        ob_start();
        extract(self::extractTemplateVars($vars));
        include $f;
        $r = ob_get_clean();
        return $r;
    }

    /**
     * 安全地 include 模板文件（推荐用于 outCache 方式）
     *
     * 使用示例：
     * // 方式1：使用全局变量（向后兼容）
     * $GLOBALS['user'] = $user;
     * Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir);
     *
     * // 方式2：显式传递变量（更安全，推荐用于函数/类方法内部）
     * Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir, false, ['user' => $user]);
     *
     * // 方式3：配置白名单模式
     * $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
     * $GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data'];
     * $GLOBALS['user'] = $user;
     * Zandy_Template::includeTemplate('template.htm', $tplDir, $cacheDir);
     *
     * @param string $tplFileName 模板文件名
     * @param string $tplDir 模板目录
     * @param string $cacheDir 缓存目录
     * @param bool $forceRefreshCache 是否强制刷新缓存
     * @param array|null $vars 显式传递的变量（可选，如果提供则只使用这些变量，忽略全局变量配置）
     * @return void 此方法直接输出内容，不返回值
     */
    public static function includeTemplate($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $vars = null)
    {
        $f = self::outCache($tplFileName, $tplDir, $cacheDir, $forceRefreshCache);
        extract(self::extractTemplateVars($vars));
        include $f;
    }

    /**
     * 安全地提取模板变量（用于 outCache + include 方式）
     *
     * 使用示例：
     * // 方式1：显式传递变量（推荐）
     * $cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
     * extract(Zandy_Template::getTemplateVars(['user' => $user]));
     * include $cacheFile;
     *
     * // 方式2：使用配置模式
     * $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
     * $GLOBALS['siteConf']['template_vars_whitelist'] = ['user', 'data'];
     * $GLOBALS['user'] = $user;
     * $cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
     * extract(Zandy_Template::getTemplateVars());  // 使用配置的白名单
     * include $cacheFile;
     *
     * @param array|null $explicitVars 显式传递的变量（可选，如果提供则只使用这些变量，忽略全局变量配置）
     * @return array 提取的变量数组
     */
    public static function getTemplateVars($explicitVars = null)
    {
        return self::extractTemplateVars($explicitVars);
    }

    /**
     * 编译模板并返回 PHP 缓存文件路径
     *
     * 将模板文件编译成 PHP 文件并缓存，返回缓存文件的完整路径。
     * 如果模板文件已修改或强制刷新，会重新编译。
     * 编译后的 PHP 文件可以直接使用 include 包含执行。
     *
     * @param string $tplFileName 模板文件名（如 'header.htm'）
     * @param string $tplDir 模板目录路径（可选，默认使用 $GLOBALS['siteConf']['tplDir']）
     * @param string $cacheDir 缓存目录路径（可选，默认使用 $GLOBALS['siteConf']['tplCacheBaseDir']）
     * @param bool $forceRefreshCache 是否强制刷新缓存（默认 false）
     * @return string|false 返回缓存文件的完整路径，失败返回 false
     *
     * @example
     * // 基本用法
     * $cacheFile = Zandy_Template::outCache('header.htm', '/path/to/templates/', '/path/to/cache/');
     * if ($cacheFile) {
     *     extract(Zandy_Template::getTemplateVars());
     *     include $cacheFile;
     * }
     *
     * // 强制刷新缓存
     * $cacheFile = Zandy_Template::outCache('header.htm', $tplDir, $cacheDir, true);
     */
    public static function outCache($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false)
    {
        // {{{ 接管 error handler
        if (!function_exists('zte_error_handler')) {
            // PHP 8.0+ 移除了 $errcontext 参数，需要兼容处理
            function zte_error_handler($errno, $errstr, $errfile, $errline, $errcontext = null)
            {
                // var_dump($errno, $errstr, $errfile, $errline, $errcontext);
                $filename = "/tmp/zte_error_handler." . date("Ymd") . ".log";
                $data = array(
                    'datetime: ' . date("Y-m-d H:i:s"),
                    '$errno: ' . Zandy_Template::friendlyErrorType($errno),
                    '$errstr: ' . print_r($errstr, true),
                    '$errfile: ' . print_r($errfile, true),
                    '$errline: ' . print_r($errline, true)
                );
                $log_error = join("\n", $data) . "\n----\n";
                Zandy_Template::sendAlarmEmail($log_error);
                @file_put_contents($filename, $log_error, FILE_APPEND);
            }
        }
        // PHP 8.4+ 中 E_STRICT 已被废弃，需要兼容处理
        // 通过文件分离避免在 PHP 8.4+ 中访问 E_STRICT 常量
        if (PHP_VERSION_ID >= 80400) {
            // PHP 8.4+ 直接使用 E_ALL（E_STRICT 已移除）
            $error_level = E_ALL;
        } else {
            // PHP 8.4 以下版本，包含配置文件（避免在 PHP 8.4+ 中解析时访问 E_STRICT）
            require __DIR__ . '/Template_error_level.php';
        }
        set_error_handler("zte_error_handler", $error_level);
        // }}}

        if (substr($tplFileName, -4) != '.htm' && substr($tplFileName, -5) != '.html') {
            $tplFileName .= '.htm';
        }
        //global $siteConf;
        $siteConf = isset($GLOBALS['siteConf']) ? $GLOBALS['siteConf'] : array();
        // need tplBaseDir tplCacheBaseDir, need tplDir if $tplDir is empty.
        $tplBaseDir = realpath(preg_replace("/[\\\\\\/]+/", DIRECTORY_SEPARATOR, $siteConf['tplBaseDir'])) . DIRECTORY_SEPARATOR;
        $tplCacheBaseDir = realpath(preg_replace("/[\\\\\\/]+/", DIRECTORY_SEPARATOR, $siteConf['tplCacheBaseDir'])) . DIRECTORY_SEPARATOR;
        $tplBaseDir = preg_replace("/[\\\\\\/]+/", DIRECTORY_SEPARATOR, $tplBaseDir);
        $tplCacheBaseDir = preg_replace("/[\\\\\\/]+/", DIRECTORY_SEPARATOR, $tplCacheBaseDir);
        $tplDir2 = '' != $tplDir ? $tplDir : $siteConf['tplDir'];
        $tplDir2 = realpath($tplDir2) ? realpath($tplDir2) . DIRECTORY_SEPARATOR : $tplDir2;
        $tplDir2 = preg_replace("/[\\\\\\/]+/", DIRECTORY_SEPARATOR, $tplDir2);
        if (!$tplDir2 || !$tplBaseDir || false === stripos($tplDir2, $tplBaseDir)) {
            self::halt('$tplDir("' . $tplDir . '") is not a valid tpl path', true);
        }
        $cacheDir2 = '' != $cacheDir ? $cacheDir : $siteConf['tplCacheBaseDir'];
        $cacheDir2 = realpath($cacheDir2) ? realpath($cacheDir2) . DIRECTORY_SEPARATOR : $cacheDir2;
        $cacheDir2 = '' != $cacheDir ? $cacheDir : $siteConf['tplCacheBaseDir'];
        // {{{ check
        if (empty($tplDir2) || empty($cacheDir2)) {
            self::halt('lost parameter "$tplDir" or "$cacheDir"', true);
        }
        // }}}

        if (defined('PROJECT_NAME')) {
            $host = strtolower(PROJECT_NAME);
        } else {
            $host = str_replace(":", "_", isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        }
        $host = 'ztec/' . ($host == '' ? 'cli' : $host);

        $index = substr(basename($tplFileName), 0, 1);
        $xx = str_replace($tplBaseDir, '', $tplDir2); // 取得文件相对目录层次以创建cache目录
        $cacheDir2 = $cacheDir2 . $host . '/' . $index . '/' . $xx;
        $cacheDir2 = preg_replace("/[\\\\\\/]+/", DIRECTORY_SEPARATOR, $cacheDir2);
        self::mkdir($cacheDir2, 0777, true);
        if (!$cacheDir2 || !$tplCacheBaseDir || false === stripos(realpath($cacheDir2), $tplCacheBaseDir)) {
            //v($cacheDir2, realpath($cacheDir2), $tplCacheBaseDir, stripos(realpath($cacheDir2), $tplCacheBaseDir));
            self::halt('"' . $cacheDir . '" is not a valid cache path', true);
        }
        // {{{ 为了安全和能正确的创建目录
        $cacheDir2 = str_replace(array(
            "*",
            "?",
            "<",
            ">",
            "|",
            "\""
        ), array(
            "_",
            "_",
            "_",
            "_",
            "_",
            "_"
        ), $cacheDir2);
        // }}}
        $f = $tplDir2 . $tplFileName; // tpl file full name
        if (is_readable($f)) {
            $cacheRealFilename = $cacheDir2 . $tplFileName . '.' . md5($f) . '.php';
            $cacheRealDir = dirname($cacheRealFilename) . DIRECTORY_SEPARATOR;
            $cacheRealDir = Zandy_Template::adjustPath($cacheRealDir);
            if (!file_exists($cacheRealDir)) {
                Zandy_Template::mkdir($cacheRealDir, 0777, true);
            }
            // tplCacheMaxTime default is one day
            $tplCacheMaxTime = isset($siteConf['tplCacheMaxTime']) && $siteConf['tplCacheMaxTime'] > 0 ? $siteConf['tplCacheMaxTime'] : 3 * 60 * 60;
            if (!file_exists($cacheRealFilename) || filemtime($cacheRealFilename) + $tplCacheMaxTime < time() || filemtime($f) > filemtime($cacheRealFilename) || $forceRefreshCache || (isset($GLOBALS['siteConf']['forceRefreshCache']) && $GLOBALS['siteConf']['forceRefreshCache']) || (defined('TPL_FORCE_CACHE') && TPL_FORCE_CACHE) || (isset($_SERVER['SCRIPT_FILENAME']) && filemtime($_SERVER['SCRIPT_FILENAME']) > filemtime($cacheRealFilename))) {
                $s = file_get_contents($f);
                if (function_exists('html_compress')) {
                    $s = html_compress($s);
                }
                //$r = Zandy_Template::parse($s, $tplDir, $cacheDir);
                $r = Zandy_Template::parse($s, dirname($f) . DIRECTORY_SEPARATOR, $cacheDir);
                $r = '<?php defined(\'Zandy_Template\') || die(\'<h3>Access denied !</h3>\');' . $r . '?>';
                /*
                $fw = file_put_contents($cacheRealFilename, $r, LOCK_EX);
                if (false === $fw)
                {
                    self::halt("save compiled file failed: $cacheRealFilename", true);
                }
                @chmod($cacheRealFilename, 0777);
                */


                // write to tmp file, then move to overt file lock race condition
                $_tmp_file = $cacheRealFilename . uniqid('wrt', true);
                if (!file_put_contents($_tmp_file, $r, LOCK_EX)) {
                    self::halt("unable to write tmp file {$_tmp_file}", true);
                    return false;
                }

                /**
                 * Windows' rename() fails if the destination exists,
                 * Linux' rename() properly handles the overwrite.
                 * Simply unlink()ing a file might cause other processes
                 * currently reading that file to fail, but linux' rename()
                 * seems to be smart enough to handle that for us.
                 */
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // remove original file
                    unlink($cacheRealFilename);
                    // rename tmp file
                    $success = rename($_tmp_file, $cacheRealFilename);
                } else {
                    // rename tmp file
                    $success = rename($_tmp_file, $cacheRealFilename);
                    if (!$success) {
                        // remove original file
                        unlink($cacheRealFilename);
                        // rename tmp file
                        $success = rename($_tmp_file, $cacheRealFilename);
                    }
                }

            }

            #if (!(isset($GLOBALS['ON_PRODUCT']) && $GLOBALS['ON_PRODUCT']))
            #{
            // do not check syntax on product environment
            self::check_syntax($cacheRealFilename, $f);
            #}

            restore_error_handler();
            return $cacheRealFilename;
        } else {
            $msg = '<p>The template file <b>' . $f . '</b> does not exists!</p>';
            self::halt($msg, true);
            die();
            //return false;
        }
    }

    /**
     * 检查 PHP 文件的语法是否正确
     *
     * 使用优先级策略检查语法：
     * 1. opcache_compile_file（最安全，只编译不执行，需要 OPcache 扩展）
     * 2. php -l CLI 命令（安全，独立进程，需要 PHP CLI）
     * 3. eval（最后备选，可能不安全）
     *
     * 如果检测到语法错误，会返回详细的错误信息（包含文件名和行号）。
     *
     * @param string $filename 要检查的 PHP 文件路径
     * @param string $tplName 模板名称（可选，用于错误信息显示）
     * @return bool|string 语法正确返回 true，语法错误返回错误信息字符串
     *
     * @example
     * // 检查编译后的缓存文件语法
     * $cacheFile = Zandy_Template::outCache('template.htm', $tplDir, $cacheDir);
     * $result = Zandy_Template::check_syntax($cacheFile, 'template.htm');
     * if ($result !== true) {
     *     echo "语法错误: " . $result;
     * }
     */
    public static function check_syntax($filename, $tplName = '')
    {
        $error_message = null;
        $result = false;
        $error_line = 0;

        // 优先级1: 使用 opcache_compile_file (最安全，只编译不执行)
        // 注意：需要 OPcache 扩展已安装且已启用（在 CLI 模式下需要 opcache.enable_cli=On）
        if (function_exists('opcache_compile_file')) {
            // 检查 OPcache 是否已启用（在 CLI 模式下需要检查）
            $opcache_enabled = true;
            if (function_exists('opcache_get_status')) {
                $status = @opcache_get_status();
                if ($status === false || (isset($status['opcache_enabled']) && !$status['opcache_enabled'])) {
                    $opcache_enabled = false;
                }
            }

            if ($opcache_enabled) {
                $result = self::check_syntax_with_opcache($filename, $error_message, $error_line);
            } else {
                // OPcache 未启用，继续尝试下一个方法
                $result = false;
            }
        } else {
            $result = false;
        }

        // 如果 opcache 检查失败（未启用或函数不存在），继续下一个优先级
        if ($result === false && empty($error_message)) {
            // 优先级2: 使用 php -l 命令 (安全，独立进程检查)
            if (function_exists('exec') && !ini_get('safe_mode')) {
                $result = self::check_syntax_with_php_cli($filename, $error_message, $error_line);
            } // 优先级3: 使用 eval 兜底 (不安全，但兼容性最好)
            else {
                $result = self::check_syntax_with_eval($filename, $error_message, $error_line);
            }
        }

        if (!$result && $error_message) {
            // 如果还没有提取到行号，尝试从错误信息中提取
            if ($error_line <= 0) {
                if (preg_match('/on line (?P<line>\d+)/is', $error_message, $mmm)) {
                    $error_line = isset($mmm['line']) ? intval($mmm['line']) : 0;
                }
            }

            // 构建完整的错误信息，包含编译后的文件名、原模板文件名和出错行数
            $compiled_file = $filename; // 编译后的文件名
            $template_file = !empty($tplName) ? $tplName : $filename; // 原模板文件名

            if ($error_line > 0) {
                $explode = explode("\n", file_get_contents($filename));
                $line_count = count($explode);

                $tplinfo = "Compiled file: <strong>" . htmlspecialchars($compiled_file) . "</strong><br />";
                $tplinfo .= "Template file: <strong>" . htmlspecialchars($template_file) . "</strong><br />";
                $tplinfo .= "Error on line: <strong style=\"color: red;\">{$error_line}</strong><br />";

                $msg = "<div style=\"border: 1px solid blue; padding: 3px; font-size: 12px;\">";
                $msg .= $tplinfo . "<hr size=\"1\" />";
                $msg .= "<strong>Error message:</strong> " . htmlspecialchars($error_message) . "<br />";
                $msg .= "<div style=\"border: 1px solid red; padding: 3px;\">";

                // 显示错误行及上下文（确保数组索引有效）
                if ($error_line > 1 && isset($explode[$error_line - 2])) {
                    $msg .= "<strong>prev line ({$error_line} - 1):</strong>" . str_replace(" ", "&nbsp;", htmlspecialchars($explode[$error_line - 2])) . "<br />";
                }
                if (isset($explode[$error_line - 1])) {
                    $msg .= "<span style=\"color: blue;\"><strong style=\"color: red;\">error line ({$error_line}):</strong>" . str_replace(" ", "&nbsp;", htmlspecialchars($explode[$error_line - 1])) . "</span><br />";
                }
                if ($error_line < $line_count && isset($explode[$error_line])) {
                    $msg .= "<strong>next line ({$error_line} + 1):</strong>" . str_replace(" ", "&nbsp;", htmlspecialchars($explode[$error_line])) . "<br />";
                }

                $msg .= "</div></div>";

                self::halt($msg, true);
            } else {
                // 无法提取行号，但仍然显示文件名信息
                $msg = "<div style=\"border: 1px solid blue; padding: 3px; font-size: 12px;\">";
                $msg .= "Compiled file: <strong>" . htmlspecialchars($compiled_file) . "</strong><br />";
                $msg .= "Template file: <strong>" . htmlspecialchars($template_file) . "</strong><br />";
                $msg .= "<hr size=\"1\" />";
                $msg .= "<strong>Error message:</strong> " . htmlspecialchars($error_message) . "<br />";
                $msg .= "</div>";

                self::halt($msg, true);
            }
        }
    }

    /**
     * 使用 opcache_compile_file 检查语法（优先级1）
     *
     * 最安全的语法检查方法，只编译不执行，不会影响当前进程。
     * 需要 OPcache 扩展已安装且已启用（在 CLI 模式下需要 opcache.enable_cli=On）。
     *
     * @param string $filename 要检查的 PHP 文件路径
     * @param string|null &$error_message 错误信息（通过引用返回）
     * @param int &$error_line 错误行号（通过引用返回）
     * @return bool 语法正确返回 true，语法错误返回 false
     */
    private static function check_syntax_with_opcache($filename, &$error_message = null, &$error_line = 0)
    {
        // 清除之前的错误 (PHP 7.0+)
        if (function_exists('error_clear_last')) {
            error_clear_last();
        }

        // 尝试编译文件（不执行）
        $result = @opcache_compile_file($filename);

        if ($result === false) {
            // 获取错误信息
            $error = error_get_last();
            if ($error) {
                // 尝试从错误信息中提取行号
                if (preg_match('/on line (\<b\>)?(?P<line>\d+)/is', $error['message'], $matches)) {
                    $error_line = isset($matches['line']) ? intval($matches['line']) : 0;
                    $error_message = $error['message'];
                } else {
                    $error_message = $error['message'];
                    $error_line = 0;
                }
            } else {
                $error_message = "Syntax error (opcache_compile_file returned false)";
                $error_line = 0;
            }

            // 确保错误信息包含文件名和行号
            $basename = basename($filename);
            if (strpos($error_message, $basename) === false) {
                if ($error_line > 0) {
                    $error_message = $error_message . " in " . $basename . " on line " . $error_line;
                } else {
                    $error_message = $error_message . " in " . $basename . " on line 1";
                    $error_line = 1;
                }
            } else {
                // 如果已经包含文件名，确保行号正确
                if (!preg_match('/on line \d+/i', $error_message)) {
                    if ($error_line > 0) {
                        $error_message = $error_message . " on line " . $error_line;
                    } else {
                        $error_message = $error_message . " on line 1";
                        $error_line = 1;
                    }
                }
            }

            return false;
        }

        // opcache_compile_file 在 PHP 7+ 中如果语法错误可能会抛出 ParseError
        // 但由于使用了 @ 操作符，异常会被抑制，错误信息会通过 error_get_last() 获取
        // 所以这里不需要额外的异常处理

        return true;
    }

    /**
     * 使用 php -l 命令检查语法（优先级2）
     *
     * 通过执行独立的 PHP CLI 进程检查语法，安全且可靠。
     * 支持 Windows 和 Unix 系统，自动处理 PHP 路径。
     *
     * @param string $filename 要检查的 PHP 文件路径
     * @param string|null &$error_message 错误信息（通过引用返回）
     * @param int &$error_line 错误行号（通过引用返回）
     * @return bool 语法正确返回 true，语法错误返回 false
     */
    private static function check_syntax_with_php_cli($filename, &$error_message = null, &$error_line = 0)
    {
        // 检查文件是否存在
        if (!file_exists($filename)) {
            $error_message = "File not found: " . $filename;
            $error_line = 0;
            return false;
        }

        // 判断操作系统
        $is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        // 获取 PHP CLI 路径
        $path = PHP_BINARY;

        // Windows 系统特殊处理
        if ($is_windows) {
            // Windows 下处理 php-fpm.exe 和 php-cgi.exe
            if (stripos($path, 'php-fpm') !== false) {
                $path = str_ireplace('php-fpm', 'php', $path);
                // 确保保留 .exe 扩展名
                if (substr($path, -4) !== '.exe') {
                    $path_info = pathinfo($path);
                    $path = $path_info['dirname'] . DIRECTORY_SEPARATOR . $path_info['filename'] . '.exe';
                }
            } elseif (stripos($path, 'php-cgi') !== false) {
                $path = str_ireplace('php-cgi', 'php', $path);
                // 确保保留 .exe 扩展名
                if (substr($path, -4) !== '.exe') {
                    $path_info = pathinfo($path);
                    $path = $path_info['dirname'] . DIRECTORY_SEPARATOR . $path_info['filename'] . '.exe';
                }
            }

            // Windows 下确保使用 .exe 扩展名（如果路径中没有扩展名）
            $path_info = pathinfo($path);
            if (!isset($path_info['extension']) || empty($path_info['extension'])) {
                // 检查是否存在 php.exe
                $php_exe = $path . '.exe';
                if (file_exists($php_exe)) {
                    $path = $php_exe;
                } else {
                    // 如果 php.exe 不存在，尝试在相同目录下查找
                    $dir = isset($path_info['dirname']) ? $path_info['dirname'] : dirname($path);
                    $php_exe = $dir . DIRECTORY_SEPARATOR . 'php.exe';
                    if (file_exists($php_exe)) {
                        $path = $php_exe;
                    }
                }
            }
        } else {
            // Unix/Linux/macOS 系统处理
            if (strpos($path, 'php-fpm') !== false) {
                $path = str_replace('php-fpm', 'php', $path);
            } elseif (strpos($path, 'php-cgi') !== false) {
                $path = str_replace('php-cgi', 'php', $path);
            }
        }

        // 检测 opcache 扩展是否存在但 CLI 未启用
        $opcache_extension_loaded = extension_loaded('Zend OPcache') || extension_loaded('opcache');
        $opcache_cli_enabled = ini_get('opcache.enable_cli');

        // 如果 opcache 扩展存在但 CLI 未启用，使用 -d 参数在命令行中启用它
        // 注意：ini_set() 无法设置 opcache.enable_cli（PHP_INI_SYSTEM 类型），
        // 但可以通过命令行 -d 参数在启动时设置
        $opcache_flag = '';
        if ($opcache_extension_loaded && !$opcache_cli_enabled) {
            $opcache_flag = '-d opcache.enable_cli=1';
        }

        // 转义文件名，防止命令注入
        $escaped_filename = escapeshellarg($filename);
        $escaped_path = escapeshellarg($path);

        // 构建命令（如果需要在 CLI 中启用 opcache，使用 -d 参数）
        if (!empty($opcache_flag)) {
            $command = sprintf('%s %s -l %s 2>&1', $escaped_path, $opcache_flag, $escaped_filename);
        } else {
            $command = sprintf('%s -l %s 2>&1', $escaped_path, $escaped_filename);
        }

        $output = array();
        $return_var = 0;

        // 执行命令（Windows 和 Unix 都支持直接 exec）
        @exec($command, $output, $return_var);

        // Windows 下如果直接 exec 失败，尝试使用 cmd /c
        if ($is_windows && $return_var !== 0 && empty($output)) {
            $cmd_command = sprintf('cmd /c %s', $command);
            @exec($cmd_command, $output, $return_var);
        }

        // php -l 返回 0 表示语法正确，非 0 表示有错误
        if ($return_var !== 0) {
            $error_output = implode("\n", $output);

            // 如果输出为空，可能是命令执行失败
            if (empty($error_output)) {
                $error_message = "Failed to execute PHP syntax check command. PHP binary: $path";
                $error_line = 0;
                return false;
            }

            // 解析错误信息
            $error_output = trim($error_output);
            if (empty($error_output)) {
                $error_message = "Syntax error detected";
                $error_line = 0;
                return false;
            } elseif (preg_match('/on line (?P<line>\d+)/is', $error_output, $matches)) {
                $error_line = isset($matches['line']) ? intval($matches['line']) : 0;
                $error_message = $error_output;
                if (empty($error_message)) {
                    $error_message = "Syntax error detected";
                }
                return false;
            } else {
                $error_message = $error_output;
                if (empty($error_message)) {
                    $error_message = "Syntax error detected";
                }
                $error_line = 0;
                return false;
            }
        }

        return true;
    }

    /**
     * 使用 eval 检查语法（优先级3，最后备选）
     *
     * 通过包装代码并使用 eval 检查语法，兼容 PHP 5.6+。
     * 注意：此方法会执行代码，可能存在安全风险，仅在无法使用前两种方法时使用。
     *
     * @param string $filename 要检查的 PHP 文件路径
     * @param string|null &$error_message 错误信息（通过引用返回）
     * @param int &$error_line 错误行号（通过引用返回）
     * @return bool 语法正确返回 true，语法错误返回 false
     */
    private static function check_syntax_with_eval($filename, &$error_message = null, &$error_line = 0)
    {
        $tmpcontent = file_get_contents($filename);

        $evalstr = "return true; ?>" . $tmpcontent . "<?php ";

        // {{{ 以后注意这里是否有潜在bug
        ob_start();
        $parse_error = null;

        if (PHP_VERSION_ID >= 70000) {
            require __DIR__ . '/Template_parse_error_70.php';
        } else {
            require __DIR__ . '/Template_parse_error.php';
        }

        $obcontent = ob_get_clean();
        // }}}

        // PHP 5.6 中，@eval 会抑制错误输出，需要使用 error_get_last() 获取错误
        if (PHP_VERSION_ID < 70000 && isset($GLOBALS['_zte_eval_error'])) {
            $error = $GLOBALS['_zte_eval_error'];
            unset($GLOBALS['_zte_eval_error']);

            $error_message = $error['message'];
            $error_line = isset($error['line']) ? intval($error['line']) : 0;

            // 从错误信息中提取行号
            if (preg_match('/on line (?P<line>\d+)/is', $error_message, $matches)) {
                $extracted_line = isset($matches['line']) ? intval($matches['line']) : 0;
                if ($extracted_line > 0) {
                    /* 减去包装代码的行数（1行 return true; ?>） */
                    if ($extracted_line > 1) {
                        $error_line = $extracted_line - 1;
                    } else {
                        $error_line = 1;
                    }
                }
            } elseif ($error_line > 1) {
                // 减去包装代码的行数
                $error_line = $error_line - 1;
            }

            // 确保错误信息包含文件名和行号
            $basename = basename($filename);
            if (strpos($error_message, $basename) === false) {
                $error_message = $error_message . " in " . $basename . " on line " . $error_line;
            } else {
                if (!preg_match('/on line \d+/i', $error_message)) {
                    $error_message = $error_message . " on line " . $error_line;
                }
            }

            return false;
        }

        // 如果捕获到解析错误异常，处理异常信息
        if ($parse_error !== null) {
            $error_message = $parse_error->getMessage();
            $exception_line = $parse_error->getLine();

            /* 异常中的行号是相对于包装代码的
             * 包装代码：1行 return true; ?> + 原始内容 + 1行 <?php
             * 所以需要从异常行号中减去 1 来得到原始文件的行号
             */
            // 但如果行号是 1，说明错误在包装代码中，应该设为 1
            if ($exception_line > 1) {
                $error_line = $exception_line - 1;
            } else {
                $error_line = 1;
            }

            // 从异常信息中提取行号（优先使用异常信息中的行号，因为它可能更准确）
            // 异常信息格式通常是：syntax error, unexpected '?' on line X
            if (!empty($error_message) && preg_match('/on line (?P<line>\d+)/is', $error_message, $matches)) {
                $extracted_line = isset($matches['line']) ? intval($matches['line']) : 0;
                if ($extracted_line > 0) {
                    // 如果提取的行号大于 1，也需要减去包装代码的行数
                    if ($extracted_line > 1) {
                        $error_line = $extracted_line - 1;
                    } else {
                        $error_line = 1;
                    }
                }
            }

            // 构建包含文件名的错误信息
            if (empty($error_message)) {
                $error_message = "Parse error";
            }
            $error_message = trim($error_message);
            // 确保错误信息包含文件名和行号
            $basename = basename($filename);
            if (strpos($error_message, $basename) === false) {
                $error_message = $error_message . " in " . $basename . " on line " . $error_line;
            } else {
                // 如果已经包含文件名，确保行号正确
                if (!empty($error_message) && !preg_match('/on line \d+/i', $error_message)) {
                    $error_message = $error_message . " on line " . $error_line;
                }
            }

            return false;
        }

        if ($obcontent) {
            preg_match('/on line (\<b\>)?(?P<line>\d+)/is', $obcontent, $mmm);
            if (isset($mmm['line']) && $mmm['line'] >= 0) {
                $error_line = intval($mmm['line']);
                $explode = explode("\n", $tmpcontent);
                $all = sizeof($explode);

                $ec = explode(" in ", $obcontent);

                $error_message = isset($ec[0]) && !empty($ec[0]) ? trim($ec[0]) : trim($obcontent);
                if (empty($error_message)) {
                    $error_message = "Syntax error detected";
                }

                // 确保错误信息包含文件名和行号
                $basename = basename($filename);
                if (strpos($error_message, $basename) === false) {
                    $error_message = $error_message . " in " . $basename . " on line " . $error_line;
                } else {
                    // 如果已经包含文件名，确保行号正确
                    if (!preg_match('/on line \d+/i', $error_message)) {
                        $error_message = $error_message . " on line " . $error_line;
                    }
                }

                return false;
            } else {
                // 如果输出中有内容但没有匹配到行号，仍然设置错误信息
                $error_message = trim($obcontent);
                if (empty($error_message)) {
                    $error_message = "Syntax error detected";
                }
                $error_line = 0;

                // 确保错误信息包含文件名
                $basename = basename($filename);
                if (strpos($error_message, $basename) === false) {
                    $error_message = $error_message . " in " . $basename;
                    if ($error_line > 0) {
                        $error_message = $error_message . " on line " . $error_line;
                    }
                }

                return false;
            }
        }

        return true;
    }

    /**
     * 编译模板并返回 HTML 文件路径或内容
     *
     * 根据 $outMod 参数决定返回文件路径还是 HTML 内容。
     * 模板会被编译并缓存为 HTML 文件，支持原子写入（避免并发写入问题）。
     *
     * @param string $tplFileName 模板文件名（如 'page.htm'）
     * @param string $tplDir 模板目录路径（可选，默认使用 $GLOBALS['siteConf']['tplDir']）
     * @param string $cacheDir 缓存目录路径（可选，默认使用 $GLOBALS['siteConf']['cacheHTMLDir']）
     * @param bool $forceRefreshCache 是否强制刷新缓存（默认 false）
     * @param int $outMod 输出模式（默认 ZANDY_TEMPLATE_CACHE_MOD_HTML）：
     *   - ZANDY_TEMPLATE_CACHE_MOD_HTML (2): 返回 HTML 文件路径
     *   - ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS (8): 返回 HTML 内容字符串
     * @param array|null $vars 显式传递的变量（可选，如果提供则只使用这些变量，忽略全局变量配置）
     * @return string|false 根据 $outMod 返回文件路径或内容字符串，失败返回 false
     *
     * @example
     * // 返回文件路径
     * $htmlFile = Zandy_Template::outHTML('page.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML);
     * if ($htmlFile) {
     *     echo file_get_contents($htmlFile);
     * }
     *
     * // 返回 HTML 内容
     * $html = Zandy_Template::outHTML('page.htm', $tplDir, $cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS, ['user' => $user]);
     * echo $html;
     */
    public static function outHTML($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $outMod = ZANDY_TEMPLATE_CACHE_MOD_HTML, $vars = null)
    {
        //global $siteConf;
        $siteConf = isset($GLOBALS['siteConf']) ? $GLOBALS['siteConf'] : array();
        extract(self::extractTemplateVars($vars));
        $tplDir = '' != $tplDir ? $tplDir : $siteConf['tplDir'];
        if ($cacheDir) {
            $a = pathinfo($tplFileName);
            $cacheRealFilename = substr($cacheDir . $a['basename'], 0, -1 * (strlen($a['extension']) + 1)) . '.htm';
        } else {
            $cacheDir = '' != $cacheDir ? $cacheDir : $siteConf['cacheHTMLDir'];
            $a = pathinfo($cacheDir . $tplFileName);
            $cacheRealFilename = substr($cacheDir . $tplFileName, 0, -1 * (strlen($a['extension']) + 1)) . '.htm';
        }
        $f = $tplDir . $tplFileName;
        if (is_file($f)) {
            $r = null; // 初始化 $r 变量
            if (!file_exists($cacheRealFilename) || filemtime($f) > filemtime($cacheRealFilename) || $forceRefreshCache) {
                ob_start();
                $s = file_get_contents($f);
                $parsed = Zandy_Template::parse($s, $tplDir);
                eval($parsed); // need GLOBALS var
                $r = ob_get_clean();
                $cacheRealDir = dirname($cacheDir . $tplFileName);
                if (!file_exists($cacheRealDir)) {
                    Zandy_Template::mkdir($cacheRealDir, 0777, true);
                }
                //file_put_contents($cacheRealFilename, $r, LOCK_EX);


                // write to tmp file, then move to overt file lock race condition
                $_tmp_file = $cacheRealFilename . uniqid('wrt', true);
                if (!file_put_contents($_tmp_file, $r, LOCK_EX)) {
                    self::halt("unable to write tmp file {$_tmp_file}", true);
                    return false;
                }

                /**
                 * Windows' rename() fails if the destination exists,
                 * Linux' rename() properly handles the overwrite.
                 * Simply unlink()ing a file might cause other processes
                 * currently reading that file to fail, but linux' rename()
                 * seems to be smart enough to handle that for us.
                 */
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // remove original file
                    unlink($cacheRealFilename);
                    // rename tmp file
                    $success = rename($_tmp_file, $cacheRealFilename);
                } else {
                    // rename tmp file
                    $success = rename($_tmp_file, $cacheRealFilename);
                    if (!$success) {
                        // remove original file
                        unlink($cacheRealFilename);
                        // rename tmp file
                        $success = rename($_tmp_file, $cacheRealFilename);
                    }
                }

            } else {
                // 如果缓存文件已存在，需要读取缓存文件内容（当需要返回内容时）
                if ($outMod & ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS) {
                    $r = file_get_contents($cacheRealFilename);
                }
            }
            if ($outMod & ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS) {
                return $r; // return html contents
            }
            return $cacheRealFilename; // return html filename
        } else {
            return false;
        }
    }

    /**
     * 解析模板并返回可 eval 的 PHP 代码字符串
     *
     * 将模板文件解析为可执行的 PHP 代码字符串，不生成缓存文件。
     * 适用于需要直接执行模板代码的场景，或需要自定义变量作用域的场景。
     *
     * @param string $tplFileName 模板文件名（如 'template.htm'）
     * @param string $tplDir 模板目录路径（可选，默认使用 $GLOBALS['siteConf']['tplDir']）
     * @return string|false 返回可 eval 的 PHP 代码字符串，失败返回 false
     *
     * @example
     * // 基本用法
     * $code = Zandy_Template::outEval('template.htm', $tplDir);
     * if ($code) {
     *     extract(['user' => $user, 'data' => $data]);
     *     eval($code);
     * }
     *
     * // 在函数内部使用（避免全局变量污染）
     * function renderTemplate($tplFile, $tplDir, $vars) {
     *     $code = Zandy_Template::outEval($tplFile, $tplDir);
     *     if ($code) {
     *         extract($vars);
     *         eval($code);
     *     }
     * }
     */
    public static function outEval($tplFileName, $tplDir = '')
    {
        //global $siteConf;
        $siteConf = isset($GLOBALS['siteConf']) ? $GLOBALS['siteConf'] : array();
        $tplDir = '' != $tplDir ? $tplDir : $siteConf['tplDir'];
        $f = $tplDir . $tplFileName;
        if (is_file($f)) {
            $s = file_get_contents($f);
            $r = Zandy_Template::parse($s, $tplDir);
            return $r;
        } else {
            return false;
        }
    }

    /**
     * 核心解析方法：将模板内容解析为 PHP 代码
     *
     * 这是模板引擎的核心方法，将模板语法转换为可执行的 PHP 代码。
     * 支持所有模板语法特性：
     * - 变量输出：{$var}, {$array['key']}
     * - 循环：loop, for, foreach（12种格式）
     * - 条件判断：if, elseif, else
     * - Switch 语句：switch, case, default, break
     * - 模板包含：template, include, include_once
     * - PHP 代码块：php
     * - 其他：set, echo, time, date, LANG 等
     *
     * @param string $s 模板内容字符串
     * @param string $tplDir 模板目录路径（用于解析相对路径的包含，可选）
     * @param string $cacheDir 缓存目录路径（可选，当前未使用）
     * @return string 解析后的 PHP 代码字符串
     *
     * @example
     * $template = '{$var}<!--{if $condition}-->Yes<!--{/if}-->';
     * $phpCode = Zandy_Template::parse($template, '/path/to/templates/');
     * extract(['var' => 'value', 'condition' => true]);
     * eval($phpCode);
     */
    public static function parse($s, $tplDir = '', $cacheDir = '')
    {
        // {{{ 阶段 1: 初始化解析上下文
        // 生成唯一的 EOB (End Of Block) 标识符，用于 heredoc 语法
        // 注意：使用 EOB 而非 EOF 是为了避免与 PHP 内置的 EOF 冲突
        $uniqueReplaceString = md5(serialize(microtime())) . "_TPL___________Zandy_20060218_Zandy__________TPL_" . time() . mt_rand(0, 999999);
        $EOB = "TPL___________Zandy_20060218_Zandy__________TPL_" . $uniqueReplaceString;
        $EOB = 'Z_' . md5($EOB) . '_Y';
        
        // 生成唯一的循环计数器栈变量名，避免变量污染，支持嵌套循环
        // 注意：uniqid('', true) 会生成包含小数点的字符串，需要替换为下划线才能作为变量名
        $loopStackVar = '__zte_loop_stack_' . str_replace('.', '_', uniqid('', true)) . '__';
        $loopInfoStackVar = '__zte_loop_info_stack_' . str_replace('.', '_', uniqid('', true)) . '__';
        $loopNamesStackVar = '__zte_loop_names_stack_' . str_replace('.', '_', uniqid('', true)) . '__';
        
        // 规范化路径格式
        $tplDir = Zandy_Template::normalizePath($tplDir);
        $cacheDir = Zandy_Template::normalizePath($cacheDir);
        // }}}
        
        // {{{ 阶段 2: 预处理模板内容
        // 移除模板注释：<!--{*这是注释内容*}-->
        // 模板注释在编译时会被移除，不会出现在最终输出中
        $s = preg_replace("/\\<\\!\\-\\-\\{\\*.*\\*\\}\\-\\-\\>/isU", '', $s);
        
        // 将模板内容包装为 heredoc 语法，便于后续处理
        // 使用 heredoc 可以安全地处理包含特殊字符的内容
        $s = "echo <<<$EOB\r\n" . $s . "\r\n$EOB;\r\n";
        // }}}
        
        // {{{ 阶段 3: 处理模板包含
        // 语法：<!--{template header.htm}-->
        // 功能：包含其他模板文件，支持相对路径
        // 注意：逻辑控制语句统一使用 <!--{ }--> 分隔符，浏览器预览时不会破坏 HTML 结构
        $m = array();
        preg_match_all("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "template\\s+([^\\}\\s]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/is", $s, $m);
        if (is_array($m[0]) && is_array($m[1])) {
            foreach ($m[1] as $k => $v) {
                $tmp_md5 = uniqid('zte') . mt_rand(1, 999999);
                $s = str_replace($m[0][$k], "\r\n$EOB;\r\n\$tpl_$tmp_md5 = Zandy_Template::outCache(\"" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . "\");if(empty(\$tpl_$tmp_md5)){Zandy_Template::halt('template file: ' . \$tpl_$tmp_md5 . '; gettype: ' . gettype(\$tpl_$tmp_md5) . '; params:(" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . ")');}\$$tmp_md5 = require \$tpl_$tmp_md5;if(\$$tmp_md5===false){Zandy_Template::halt('template file: \'" . $tplDir . $v . "\'<br><br>include compiled file \'' . \$tpl_$tmp_md5 . '\' failed.<br>\$_SERVER[\"SERVER_ADDR\"]: " . $_SERVER["SERVER_ADDR"] . "<br>\$server_host: " . (isset($GLOBALS['server_host']) ? $GLOBALS['server_host'] : '') . "', true);}echo <<<$EOB\r\n", $s);
            }
        }
        // }}}
        
        // {{{ 阶段 4: 处理 PHP 代码块和变量设置
        // 语法：<!--{php}-->...<!--{/php}-->
        // 功能：执行块级 PHP 代码
        // 注意：支持块级 PHP 代码，浏览器预览时不会破坏 HTML 结构
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "php" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/php" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        
        // 语法：<!--{set $var = 'value'}-->
        // 功能：设置变量（语法糖，用于简化变量赋值）
        // 等价于：<!--{php}-->$var = 'value';<!--{/php}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "set\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        // }}}
        
        // {{{ 阶段 5: 处理循环语句
        // 说明：循环语句处理按匹配优先级排序（从具体到抽象），确保正确匹配
        // 支持的循环类型：for, foreach, loop（12种格式）
        //
        // --- 5.1 for 循环 ---
        // 语法：<!--{for $i = 0; $i < 10; $i++}-->
        // 功能：标准 PHP for 循环
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "for\\s+(.+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nfor(\\1){echo <<<$EOB\r\n", $s);
        
        // --- 5.2 foreach 循环 ---
        // 语法：<!--{foreach $items as $item}-->
        // 功能：标准 PHP foreach 循环，支持 foreach-else
        // 说明：
        //   - 为了支持 foreach-else，需要在循环前检查数组是否为空（类似 loop 的处理）
        //   - 提取数组变量名：foreach($arr as $key => $value) 或 foreach($arr as $value)
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "foreach\\s+(.+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB) {
            $foreachExpr = trim($m[1]);
            // 提取数组变量名：从 "as" 之前提取
            if (preg_match('/^(\S+)\s+as\s+/i', $foreachExpr, $arrMatch)) {
                $arrVar = $arrMatch[1];
                // 生成代码：if (is_array($arr)&&sizeof($arr)>0) { foreach(...) }
                return "\r\n$EOB;\r\nif (is_array($arrVar)&&sizeof($arrVar)>0){foreach($foreachExpr){echo <<<$EOB\r\n";
            } else {
                // 如果无法提取数组变量，则直接使用原表达式（向后兼容）
                return "\r\n$EOB;\r\nforeach($foreachExpr){echo <<<$EOB\r\n";
            }
        }, $s);

        // --- 5.3 loop 循环（12种格式）---
        // 语法：<!--{loop $arr AS $key => $value}--> 或 <!--{loop $arr $value}-->
        // 功能：简化语法，自动检查数组，支持命名循环和循环索引信息
        // 说明：
        //   - 使用栈结构支持嵌套循环，每个循环层级有独立的计数器
        //   - 支持 name="loopname" 参数，生成 $_zte_loop_{name} 变量
        //   - 提供循环索引信息：index, iteration, first, last, length
        //   - 按从具体到抽象的顺序匹配，确保正确解析
        //
        // 格式1: <!--{loop $arr AS $key => $value name="loopname"}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)(?:\\s+name\\s*=\\s*[\"'](\\w+)[\"'])?\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar, $loopInfoStackVar, $loopNamesStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $name = isset($m[5]) && !empty($m[5]) ? $m[5] : '';
            $name_init = $name ? '$' . $loopNamesStackVar . "[]='" . addslashes($name) . "';" . '$' . $loopInfoStackVar . "[]=array('length'=>sizeof(" . $arr . "),'index'=>-1,'iteration'=>0);" : '';
            $name_iter = $name ? '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['index']++;" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['iteration']++;\$__zte_loop_current_info__=" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1];\$__zte_loop_current_info__['first']=(\$__zte_loop_current_info__['index']==0);\$__zte_loop_current_info__['last']=(\$__zte_loop_current_info__['index']==\$__zte_loop_current_info__['length']-1);\$_zte_loop_" . $name . "=\$__zte_loop_current_info__;" : '';
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();if(!isset(" . '$' . $loopInfoStackVar . "))" . '$' . $loopInfoStackVar . "=array();if(!isset(" . '$' . $loopNamesStackVar . "))" . '$' . $loopNamesStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . $name_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;" . $name_iter . "echo <<<$EOB\r\n";
        }, $s);
        // 格式3: <!--{loop $arr AS $value name="loopname"}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)(?:\\s+name\\s*=\\s*[\"'](\\w+)[\"'])?\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar, $loopInfoStackVar, $loopNamesStackVar) {
            $arr = $m[2];
            $val = $m[3];
            $name = isset($m[4]) && !empty($m[4]) ? $m[4] : '';
            $name_init = $name ? '$' . $loopNamesStackVar . "[]='" . addslashes($name) . "';" . '$' . $loopInfoStackVar . "[]=array('length'=>sizeof(" . $arr . "),'index'=>-1,'iteration'=>0);" : '';
            $name_iter = $name ? '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['index']++;" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['iteration']++;\$__zte_loop_current_info__=" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1];\$__zte_loop_current_info__['first']=(\$__zte_loop_current_info__['index']==0);\$__zte_loop_current_info__['last']=(\$__zte_loop_current_info__['index']==\$__zte_loop_current_info__['length']-1);\$_zte_loop_" . $name . "=\$__zte_loop_current_info__;" : '';
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();if(!isset(" . '$' . $loopInfoStackVar . "))" . '$' . $loopInfoStackVar . "=array();if(!isset(" . '$' . $loopNamesStackVar . "))" . '$' . $loopNamesStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . $name_init . "foreach($arr as $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;" . $name_iter . "echo <<<$EOB\r\n";
        }, $s);
        // 格式2: <!--{loop $arr AS $key $value name="loopname"}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s+(\\S+)(?:\\s+name\\s*=\\s*[\"'](\\w+)[\"'])?\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar, $loopInfoStackVar, $loopNamesStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $name = isset($m[5]) && !empty($m[5]) ? $m[5] : '';
            $name_init = $name ? '$' . $loopNamesStackVar . "[]='" . addslashes($name) . "';" . '$' . $loopInfoStackVar . "[]=array('length'=>sizeof(" . $arr . "),'index'=>-1,'iteration'=>0);" : '';
            $name_iter = $name ? '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['index']++;" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['iteration']++;\$__zte_loop_current_info__=" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1];\$__zte_loop_current_info__['first']=(\$__zte_loop_current_info__['index']==0);\$__zte_loop_current_info__['last']=(\$__zte_loop_current_info__['index']==\$__zte_loop_current_info__['length']-1);\$_zte_loop_" . $name . "=\$__zte_loop_current_info__;" : '';
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();if(!isset(" . '$' . $loopInfoStackVar . "))" . '$' . $loopInfoStackVar . "=array();if(!isset(" . '$' . $loopNamesStackVar . "))" . '$' . $loopNamesStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . $name_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;" . $name_iter . "echo <<<$EOB\r\n";
        }, $s);
        // 格式4: <!--{loop $arr $key => $value name="loopname"}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)(?:\\s+name\\s*=\\s*[\"'](\\w+)[\"'])?\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar, $loopInfoStackVar, $loopNamesStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $name = isset($m[5]) && !empty($m[5]) ? $m[5] : '';
            $name_init = $name ? '$' . $loopNamesStackVar . "[]='" . addslashes($name) . "';" . '$' . $loopInfoStackVar . "[]=array('length'=>sizeof(" . $arr . "),'index'=>-1,'iteration'=>0);" : '';
            $name_iter = $name ? '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['index']++;" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['iteration']++;\$__zte_loop_current_info__=" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1];\$__zte_loop_current_info__['first']=(\$__zte_loop_current_info__['index']==0);\$__zte_loop_current_info__['last']=(\$__zte_loop_current_info__['index']==\$__zte_loop_current_info__['length']-1);\$_zte_loop_" . $name . "=\$__zte_loop_current_info__;" : '';
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();if(!isset(" . '$' . $loopInfoStackVar . "))" . '$' . $loopInfoStackVar . "=array();if(!isset(" . '$' . $loopNamesStackVar . "))" . '$' . $loopNamesStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . $name_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;" . $name_iter . "echo <<<$EOB\r\n";
        }, $s);
        // 格式5: <!--{loop $arr $key $value name="loopname"}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)(?:\\s+name\\s*=\\s*[\"'](\\w+)[\"'])?\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar, $loopInfoStackVar, $loopNamesStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $name = isset($m[5]) && !empty($m[5]) ? $m[5] : '';
            $name_init = $name ? '$' . $loopNamesStackVar . "[]='" . addslashes($name) . "';" . '$' . $loopInfoStackVar . "[]=array('length'=>sizeof(" . $arr . "),'index'=>-1,'iteration'=>0);" : '';
            $name_iter = $name ? '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['index']++;" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['iteration']++;\$__zte_loop_current_info__=" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1];\$__zte_loop_current_info__['first']=(\$__zte_loop_current_info__['index']==0);\$__zte_loop_current_info__['last']=(\$__zte_loop_current_info__['index']==\$__zte_loop_current_info__['length']-1);\$_zte_loop_" . $name . "=\$__zte_loop_current_info__;" : '';
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();if(!isset(" . '$' . $loopInfoStackVar . "))" . '$' . $loopInfoStackVar . "=array();if(!isset(" . '$' . $loopNamesStackVar . "))" . '$' . $loopNamesStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . $name_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;" . $name_iter . "echo <<<$EOB\r\n";
        }, $s);
        // 格式6: <!--{loop $arr $value name="loopname"}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)(?:\\s+name\\s*=\\s*[\"'](\\w+)[\"'])?\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar, $loopInfoStackVar, $loopNamesStackVar) {
            $arr = $m[2];
            $val = $m[3];
            $name = isset($m[4]) && !empty($m[4]) ? $m[4] : '';
            $name_init = $name ? '$' . $loopNamesStackVar . "[]='" . addslashes($name) . "';" . '$' . $loopInfoStackVar . "[]=array('length'=>sizeof(" . $arr . "),'index'=>-1,'iteration'=>0);" : '';
            $name_iter = $name ? '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['index']++;" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1]['iteration']++;\$__zte_loop_current_info__=" . '$' . $loopInfoStackVar . "[count(" . '$' . $loopInfoStackVar . ")-1];\$__zte_loop_current_info__['first']=(\$__zte_loop_current_info__['index']==0);\$__zte_loop_current_info__['last']=(\$__zte_loop_current_info__['index']==\$__zte_loop_current_info__['length']-1);\$_zte_loop_" . $name . "=\$__zte_loop_current_info__;" : '';
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();if(!isset(" . '$' . $loopInfoStackVar . "))" . '$' . $loopInfoStackVar . "=array();if(!isset(" . '$' . $loopNamesStackVar . "))" . '$' . $loopNamesStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . $name_init . "foreach($arr as $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;" . $name_iter . "echo <<<$EOB\r\n";
        }, $s);
        // 格式7: <!--{loop $arr AS $key => $value}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;echo <<<$EOB\r\n";
        }, $s);
        // 格式8: <!--{loop $arr AS $key $value}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;echo <<<$EOB\r\n";
        }, $s);
        // 格式9: <!--{loop $arr AS $value}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar) {
            $arr = $m[2];
            $val = $m[3];
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . "foreach($arr as $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;echo <<<$EOB\r\n";
        }, $s);
        // 格式10: <!--{loop $arr $key => $value}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;echo <<<$EOB\r\n";
        }, $s);
        // 格式11: <!--{loop $arr $key $value}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar) {
            $arr = $m[2];
            $key = $m[3];
            $val = $m[4];
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();";
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . "foreach($arr as $key => $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;echo <<<$EOB\r\n";
        }, $s);
        // 格式12: <!--{loop $arr $value}-->
        $s = preg_replace_callback("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", function ($m) use ($EOB, $loopStackVar) {
            $arr = $m[2];
            $val = $m[3];
            $stack_init = "if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();";
            // 当数组为空时，在 else 分支中初始化栈并推入计数器0，以支持 loop-else
            return "\r\n$EOB;\r\nif (is_array($arr)&&sizeof($arr)>0){" . $stack_init . "foreach($arr as $val){" . '$' . $loopStackVar . "[]=0;" . '$' . $loopStackVar . "[count(" . '$' . $loopStackVar . ")-1]++;echo <<<$EOB\r\n";
        }, $s);

        // --- 5.4 循环的 else 分支 ---
        // 语法：<!--{loop-else}-->, <!--{foreach-else}-->, <!--{for-else}-->
        // 功能：当循环数组为空时执行 else 分支
        // 说明：
        //   - loop-else：出栈并判断，如果计数器为0，执行else分支
        //   - 当数组为空时，在栈中推入计数器0，以便 loop-else 能正确检测空数组情况
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop-else)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "(.*)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(loop)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\n}}else{if(!isset(" . '$' . $loopStackVar . "))" . '$' . $loopStackVar . "=array();" . '$' . $loopStackVar . "[]=0;}if(isset(" . '$' . $loopStackVar . ")&&count(" . '$' . $loopStackVar . ")>0){\$__zte_loop_count__=array_pop(" . '$' . $loopStackVar . ");if(isset(" . '$' . $loopNamesStackVar . ")&&count(" . '$' . $loopNamesStackVar . ")>0 && !empty(" . '$' . $loopNamesStackVar . "[count(" . '$' . $loopNamesStackVar . ")-1])){array_pop(" . '$' . $loopNamesStackVar . ");}if(isset(" . '$' . $loopInfoStackVar . ")&&count(" . '$' . $loopInfoStackVar . ")>0 && isset(" . '$' . $loopNamesStackVar . ") && count(" . '$' . $loopNamesStackVar . ") > 0 && !empty(" . '$' . $loopNamesStackVar . "[count(" . '$' . $loopNamesStackVar . ")-1])){array_pop(" . '$' . $loopInfoStackVar . ");}if(\$__zte_loop_count__==0){echo <<<$EOB\r\n\\2\r\n$EOB;\r\n}}echo <<<$EOB\r\n", $s);
        // foreach-else：由于 foreach 已经包含数组检查，这里只需要添加 else 分支
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(foreach-else)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "(.*)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(foreach)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\n}}else{echo <<<$EOB\r\n\\2\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // for-else：for 循环不支持 else，但为了语法一致性，提供空实现
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(for-else)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "(.*)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(for)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\n}echo <<<$EOB\r\n\\2\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        
        // --- 5.5 循环结束标签 ---
        // 语法：<!--{/loop}-->, <!--{/for}-->, <!--{/foreach}-->
        // 功能：结束循环，出栈循环计数器
        // 说明：
        //   - 出栈：移除当前循环层级的计数器、循环名字和循环信息
        //   - 注意：只有当栈中有对应元素时才弹出（避免内层无name的循环错误弹出外层循环信息）
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(loop)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}if(isset(" . '$' . $loopStackVar . ")&&count(" . '$' . $loopStackVar . ")>0){array_pop(" . '$' . $loopStackVar . ");}if(isset(" . '$' . $loopNamesStackVar . ")&&count(" . '$' . $loopNamesStackVar . ")>0 && !empty(" . '$' . $loopNamesStackVar . "[count(" . '$' . $loopNamesStackVar . ")-1])){array_pop(" . '$' . $loopNamesStackVar . ");}if(isset(" . '$' . $loopInfoStackVar . ")&&count(" . '$' . $loopInfoStackVar . ")>0 && isset(" . '$' . $loopNamesStackVar . ") && count(" . '$' . $loopNamesStackVar . ") > 0 && !empty(" . '$' . $loopNamesStackVar . "[count(" . '$' . $loopNamesStackVar . ")-1])){array_pop(" . '$' . $loopInfoStackVar . ");}}echo <<<$EOB\r\n", $s);
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(for)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // foreach 结束标签：需要闭合 if 和 foreach 的大括号
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(foreach)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}}echo <<<$EOB\r\n", $s);
        // }}}
        
        // {{{ 阶段 6: 处理条件语句
        // 语法：<!--{if $condition}-->, <!--{elseif $other}-->, <!--{else}-->, <!--{/if}-->
        // 功能：条件判断，支持完整 PHP 表达式
        // if 条件：<!--{if $condition}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "if (.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nif (\\1){echo <<<$EOB\r\n", $s);
        // elseif 条件：<!--{elseif $other}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "elseif (.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}elseif (\\1){echo <<<$EOB\r\n", $s);
        // else 条件：<!--{else}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(else)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}\\1{echo <<<$EOB\r\n", $s);
        // if 结束标签：<!--{/if}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/if" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // }}}
        
        // {{{ 阶段 7: 处理 Switch 语句
        // 语法：<!--{switch $value}-->, <!--{case $value}-->, <!--{default}-->, <!--{break}-->, <!--{/switch}-->
        // 功能：Switch 语句，支持表达式，支持 fall-through（break-case, break-default）
        //
        // switch 开始：<!--{switch $value}--> (支持表达式，使用 .+? 而非 \S+)
        // 注意：switch 后不能有 echo，必须直接是 case 或 default
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "switch\\s+(.+?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nswitch(\\1){\r\n", $s);
        // break-case：<!--{break-case $value}--> (必须在 case 之前匹配，更具体)
        // 功能：break 后继续执行下一个 case（fall-through）
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "break-case\\s+(.+?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nbreak;case \\1:echo <<<$EOB\r\n", $s);
        // break-default：<!--{break-default}--> (必须在 default 之前匹配，更具体；default 不需要参数)
        // 功能：break 后继续执行 default（fall-through）
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "break-default" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nbreak;default:echo <<<$EOB\r\n", $s);
        // case：<!--{case $value}--> (支持表达式)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "case\\s+(.+?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\ncase \\1:echo <<<$EOB\r\n", $s);
        // default：<!--{default}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(default)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1:echo <<<$EOB\r\n", $s);
        // 清理 switch 和第一个 case/default 之间的所有内容（包括空白、换行、heredoc等）
        // 必须在 case 和 default 都替换后执行，确保标签已被替换
        // 匹配 switch(...){ 后面到第一个 case 或 default 之间的所有内容（包括 heredoc 结束标记）
        // 使用更宽松的匹配，匹配任何字符直到遇到 case 或 default
        $s = preg_replace("/(switch\([^)]+\)\{)\s*" . preg_quote($EOB, '/') . ";\s*(?=case|default)/s", "\\1\r\n", $s);
        // continue：<!--{continue}-->
        // 功能：循环控制（在循环中使用）
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(continue)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        // break：<!--{break}--> (必须在 break-case/break-default 之后匹配，更抽象)
        // 功能：跳出当前 case 或循环
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(break)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        // switch 结束标签：<!--{/switch}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/switch" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // }}}
        
        // {{{ 阶段 8: 处理变量输出
        // 说明：变量输出使用 { } 分隔符，不会破坏 HTML 结构
        // 支持的语法：{$var}, {time}, {now}, {date}, {echo}, {CONSTANT_NAME}
        //
        // 注意：过滤器功能可通过 {echo} 语法实现，例如：
        //   - {echo htmlspecialchars($variable)} 等价于 {$variable|escape}
        //   - {echo strtoupper($variable)} 等价于 {$variable|upper}
        //   - {echo substr($variable, 0, 50)} 等价于 {$variable|truncate:50}

        // 时间函数：{time}, {now}, {date "Y-m-d"}
        // 功能：输出时间相关函数结果
        $s = preg_replace("/\\{time\\}/si", "\r\n$EOB;\r\necho time();echo <<<$EOB\r\n", $s);
        $s = preg_replace("/\\{now\\}/si", "\r\n$EOB;\r\necho date(\"Y-m-d H:i:s\");echo <<<$EOB\r\n", $s);
        $s = preg_replace("/\\{date ([\"|'])([^'\"\\}]+)\\1\\}/is", "\r\n$EOB;\r\necho date(\\1\\2\\1);echo <<<$EOB\r\n", $s);

        // PHP 常量：{CONSTANT_NAME}
        // 功能：输出 PHP 常量（全大写+下划线格式）
        $s = preg_replace("/\\{([A-Z_]+)\\}/s", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);

        // echo 表达式：{echo expression}
        // 功能：直接输出表达式结果，支持复杂表达式和函数调用
        // 说明：
        //   - 支持 {echo "afd"} {echo $dafda} {echo $fda['fda']}
        //   - 不支持里面有换行符 \r \n
        //   - 注意：只匹配空格，不匹配制表符和换行符
        //   - 可用于实现过滤器功能：{echo htmlspecialchars(strtoupper($var))}
        //     例如：{echo htmlspecialchars($variable)} 等价于 {$variable|escape}
        //          {echo strtoupper($variable)} 等价于 {$variable|upper}
        //          {echo substr($variable, 0, 50)} 等价于 {$variable|truncate:50}
        $s = preg_replace("/\\{echo +([^\r\n}]+)\\}/i", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);
        /*
        // {{{ 数组的简单访问方式支持 e.g. {arr key1 num2 key3} 解析后为 {$arr['key1'][num2]['key3']}
        $m = array();
        preg_match_all("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(( [a-zA-Z0-9_\x7f-\xff]*)*)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/is", $s, $m);
        if (is_array($m[0]) && is_array($m[1]))
        {
            foreach ($m[0] as $k => $v)
            {
                $s = str_replace($m[0][$k], Zandy_Template::parseArray(substr($v, strlen(ZANDY_TEMPLATE_DELIMITER_VAR_LEFT), -1 * strlen(ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT))), $s);
            }
        }
        // }}}
        */
        /*
        // {{{ 对象的简单访问方式支持 e.g. {obj.property.name} 解析后为 {$obj->property->name}
        $m = array();
        #preg_match_all("/".ZANDY_TEMPLATE_DELIMITER_VAR_LEFT."[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*((\.[a-zA-Z0-9_\x7f-\xff]*(\([^\)]*\))?)+)".ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT."/is", $s, $m);
        preg_match_all("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\.[a-zA-Z0-9_\x7f-\xff]*([a-zA-Z0-9_\x7f-\xff\"'\(\)\[\]\=\>\$, -]*))+" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/is", $s, $m);
        if (is_array($m[0]) && is_array($m[1]))
        {
            foreach ($m[0] as $k => $v)
            {
                $s = str_replace($m[0][$k], Zandy_Template::parseObject(substr($v, strlen(ZANDY_TEMPLATE_DELIMITER_VAR_LEFT), -1 * strlen(ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT))), $s);
            }
        }
        // }}}
        */
        // }}}
        
        // {{{ 阶段 9: 处理语言包
        // 语法：{LANG key}
        // 功能：输出语言包文本
        // 说明：
        //   - 如果语言包存在，输出对应的文本
        //   - 如果语言包不存在且开启调试模式，输出 #key#
        //   - 如果语言包不存在且未开启调试模式，输出 key 本身
        $s = preg_replace('/\{LANG (.+?)\}/si', "\r\n$EOB;\r\nif(isset(\$_LANG[\"\\1\"])){echo <<<$EOB\r\n{\$_LANG[\"\\1\"]}\r\n$EOB;\r\n}elseif(isset(\$GLOBALS['siteConf']['tpl_debug'])&&\$GLOBALS['siteConf']['tpl_debug']){echo <<<$EOB\r\n#\\1#\r\n$EOB;\r\n}else{echo <<<$EOB\r\n\\1\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // }}}
        
        // {{{ 阶段 10: 处理文件包含
        // 语法：<!--{include file.php}-->, <!--{include_once file.php}-->
        // 功能：包含 PHP 文件
        // 说明：包含php文件时也会对其内容进行处理（20060301 发现未必应该有这样的担忧）
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "include\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/is", "'\r\n$EOB;\r\ninclude \"\\1\";echo <<<$EOB\r\n'", $s);
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "include_once\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/is", "'\r\n$EOB;\r\ninclude_once \"\\1\";echo <<<$EOB\r\n'", $s);
        // }}}
        
        // {{{ 阶段 11: 返回解析结果
        // 注意：以下代码已注释，保留用于未来可能的优化
        // 功能：将 heredoc 转换为双引号字符串（可选优化）
        // 说明：
        //   - 可以通过配置 $GLOBALS['siteConf']['EOF'] 控制是否启用
        //   - 启用后模板里的变量不能含有双引号，{$al['article_id']} 不能写为 {$al["article_id"]}
        /*
        if (ZANDY_TEMPLATE_CACHE_SIMPLE ^ intval(isset($GLOBALS['siteConf']) && isset($GLOBALS['siteConf']['EOF']) && $GLOBALS['siteConf']['EOF']))
        {
            $m = array();
            preg_match_all("/echo \<\<\<$EOB" . "\r\n(.+)\r\n$EOB" . ";\r\n/iUs", $s, $m);
            if (is_array($m[0]) && is_array($m[1]))
            {
                foreach ($m[1] as $k => $v)
                {
                    $s = str_replace($m[0][$k], "echo \"" . str_replace(array(
                        "\\",
                        "\""
                    ), array(
                        "\\\\",
                        "\\\""
                    ), $v) . "\";", $s);
                }
            }
        }
        */
        // }}}
        
        return $s;
    }

    /*
     * 对对象的访问的简单支持
    function parseObject($var)
    {
        $a = str_replace('.', '->', $var);
        $r = '{$' . $a . '}';
        return $r;
    }
     */

    /*
     * 对多维数组的支持，也支持这样{var}的变量
    function parseArray($var)
    {
        if (empty($var))
        {
            return $var;
        }
        $a = explode(" ", $var);
        $m = '';
        $b = $a[0];
        unset($a[0]);
        if (is_array($a))
        {
            foreach ($a as $k => $v)
            {
                $a[$k] = preg_match("/^[0-9]+$/", $v) ? '[' . $v . ']' : "['" . $v . "']";
            }
            $m = join("", $a);
        }
        //if (empty($m)) {
        //	return '#'.$var.'#';
        //}
        return '{$' . $b . $m . '}';
    }
     */

    /**
     * 规范化路径格式（统一为正斜杠）
     *
     * 将路径中的反斜杠统一为正斜杠，并合并多个连续的正斜杠为单个正斜杠。
     * 用于模板路径和缓存路径的规范化，确保路径格式一致。
     *
     * @param string $path 路径字符串
     * @return string 规范化后的路径（统一为正斜杠）
     *
     * @example
     * $path = Zandy_Template::normalizePath('path\\to//dir\\subdir');
     * // 结果: 'path/to/dir/subdir'
     */
    public static function normalizePath($path)
    {
        $path = str_replace("\\", "/", $path);
        $path = preg_replace("/[\\/]+/", "/", $path);
        return $path;
    }

    /**
     * 调整目录路径格式
     *
     * 统一路径分隔符，处理路径中的冗余分隔符。
     * 将混合的 / 和 \ 统一为系统默认的分隔符。
     *
     * @param string $dir 目录路径
     * @return string 调整后的目录路径
     *
     * @example
     * $path = Zandy_Template::adjustDir('path/to//dir\\subdir');
     * // 结果: 'path/to/dir/subdir' (Unix) 或 'path\to\dir\subdir' (Windows)
     */
    public static function adjustDir($dir)
    {
        #$dir = preg_replace("/[\\/\\\\]+/", DIRECTORY_SEPARATOR, $dir);
        $dir = preg_replace('/[\/\\\]+/', DIRECTORY_SEPARATOR, $dir);
        $dir = str_replace(array(
            '/./../',
            '/.././'
        ), '/../', $dir);
        return $dir;
    }

    /**
     * 递归创建目录（兼容 PHP 4）
     *
     * 实现了 PHP 4 没有的递归创建目录功能，兼容 PHP 5+ 的 mkdir() 函数。
     * 如果目录已存在，直接返回 true。
     *
     * @param string $pathname 要创建的目录路径
     * @param int $mode 目录权限（默认 0777）
     * @param bool|null $recursive 是否递归创建（默认 null，自动判断）
     * @param resource|null $context 流上下文（可选，PHP 5+）
     * @return bool 成功返回 true，失败返回 false
     *
     * @example
     * // 创建单层目录
     * Zandy_Template::mkdir('/path/to/dir', 0755);
     *
     * // 递归创建多层目录
     * Zandy_Template::mkdir('/path/to/deep/nested/dir', 0755, true);
     */
    public static function mkdir($pathname, $mode = 0777, $recursive = null, $context = null)
    {
        if (file_exists($pathname)) {
            return true;
        }
        $pathname = Zandy_Template::adjustDir($pathname);
        if (PHP_VERSION >= '5.0.0') {
            $m = (null != $context ? mkdir($pathname, $mode, $recursive, $context) : ($recursive ? mkdir($pathname, $mode, $recursive) : (null != $mode ? mkdir($pathname, $mode) : mkdir($pathname))));
            @chmod($pathname, $mode);
        } else {
            if ($recursive) {
                $a = explode(DIRECTORY_SEPARATOR, $pathname);
                $b = substr($pathname, 0, 1) == DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '';
                foreach ($a as $v) {
                    $b .= $v . DIRECTORY_SEPARATOR;
                    if (!@file_exists($b)) {
                        @mkdir($b, $mode);
                        @chmod($b, $mode);
                    }
                }
                return true;
            } elseif (null != $mode) {
                $m = mkdir($pathname, $mode);
                @chmod($pathname, $mode);
            } elseif ($pathname && PHP_VERSION < '4.2.0') {
                $m = mkdir($pathname, 0777);
                @chmod($pathname, 0777);
            } else {
                $m = mkdir($pathname);
            }
        }
        return $m;
    }

    /**
     * 规范化路径：处理路径中的 . 和 .. 符号
     *
     * 将类似 "/a/b/c/d/.././e/f" 的路径整理为 "a/b/c/e/f"。
     * 处理路径中的相对路径符号（. 和 ..），生成绝对路径。
     * 支持 Windows 和 Unix 路径格式。
     *
     * @param string $path 原始路径
     * @return string 规范化后的路径
     *
     * @example
     * $path = Zandy_Template::adjustPath('/a/b/c/d/.././e/f');
     * // 结果: '/a/b/c/e/f'
     *
     * $path = Zandy_Template::adjustPath('C:\\a\\b\\c\\..\\d');
     * // 结果: 'C:\a\b\d' (Windows)
     */
    public static function adjustPath($path)
    {
        $b = explode(DIRECTORY_SEPARATOR, Zandy_Template::adjustDir($path));
        $c = array();
        if (substr($b[0], -1) == ':') {
            for ($i = 0; $i < sizeof($b); $i++) {
                $v = $b[$i];
                if ($i > 1 && $v == '.') {
                    continue;
                } elseif ($i > 1 && $v == '..' && sizeof($c) > 1) {
                    array_pop($c);
                } else {
                    $c[] = $v;
                }
            }
        } elseif ($b[0] == '') {
            $b[1] = DIRECTORY_SEPARATOR . $b[1];
            for ($i = 1; $i < sizeof($b); $i++) {
                $v = $b[$i];
                if ($i > 1 && $v == '.') {
                    continue;
                } elseif ($i > 1 && $v == '..' && sizeof($c) > 1) {
                    array_pop($c);
                } else {
                    $c[] = $v;
                }
            }
        } else {
            for ($i = 0; $i < sizeof($b); $i++) {
                $v = $b[$i];
                if ($i > 0 && $v == '.') {
                    continue;
                } elseif ($i > 0 && $v == '..' && sizeof($c) > 0) {
                    array_pop($c);
                } else {
                    $c[] = $v;
                }
            }
        }
        $d = join(DIRECTORY_SEPARATOR, $c);
        return $d;
    }

    /**
     * 发送告警邮件
     *
     * 当模板引擎遇到严重错误时，发送告警邮件通知管理员。
     * 需要系统中有 send_mail() 函数可用。
     * 根据 $GLOBALS['ON_PRODUCT'] 设置不同的告警级别和收件人。
     *
     * @param string $msg 错误信息
     * @return void
     *
     * @example
     * // 通常在 halt() 方法中调用
     * Zandy_Template::sendAlarmEmail('模板编译失败: ' . $error);
     */
    public static function sendAlarmEmail($msg)
    {
        if (function_exists('send_mail')) {
            $title = '<title>[Sev-2]Template Engine Error</title>';
            $alarm_email = 'alarm2@example.com';
            if (isset($GLOBALS['ON_PRODUCT']) && $GLOBALS['ON_PRODUCT']) {
                $title = '<title>[Sev-1]Template Engine Error</title>';
                $alarm_email = 'alarm1@example.com';
            }
            $msg = $title . $msg;
            $timezone = date_default_timezone_get();
            $msg .= "<hr><p>Timezone: {$timezone}, Time: " . date("Y-m-d H:i:s") . "</p><hr>";
            if (file_exists('/var/job/hostname.conf')) {
                $msg .= file_get_contents('/var/job/hostname.conf');
            }
            if (isset($GLOBALS['server_host'])) {
                $msg .= "<br>" . $GLOBALS['server_host'];
            }

            $msg .= "<br>" . print_r(debug_backtrace(), true);

            @send_mail($alarm_email, $msg, '', NOTICE_EMAIL, 'SYSTEM', '');
        }
    }

    /**
     * 将 PHP 错误类型常量转换为可读的字符串
     *
     * 将 PHP 错误类型常量（如 E_ERROR, E_WARNING）转换为对应的字符串名称。
     * 用于错误日志和调试信息显示。
     *
     * @param int $type PHP 错误类型常量（如 E_ERROR, E_WARNING, E_NOTICE 等）
     * @return string 错误类型名称（如 'E_ERROR', 'E_WARNING'）
     *
     * @example
     * $errorType = Zandy_Template::friendlyErrorType(E_ERROR);
     * // 结果: 'E_ERROR'
     *
     * $errorType = Zandy_Template::friendlyErrorType(E_WARNING);
     * // 结果: 'E_WARNING'
     */
    public static function friendlyErrorType($type)
    {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
            case E_ALL: // 32767 //
                return 'E_ALL';
        }
        return "";
    }

} // End class


/*

// reg test:

$a = '\/';
var_dump($a, preg_quote($a, '/'), str_replace('\\', '\\\\', preg_quote($a, '/')));


// {{{ usage:

$getAll = array(
	0 => array('id' => 1, 'name' => 'a'),
	1 => array('id' => 2, 'name' => 'b'),
	2 => array('id' => 3, 'name' => 'c'),
	3 => array('id' => 4, 'name' => 'd'),
	4 => array('id' => 5, 'name' => 'e'),
	5 => array('id' => 6, 'name' => 'f'),
);

$filename = "tpl.htm";
$contents = file_get_contents($filename);
//include Zandy_Template::outCache($contents);

// or
ob_start();
eval(Zandy_Template::outEval($contents));
$final_html = ob_get_clean();

echo $final_html;

// }}}
function p($s){
	echo '<xmp>';
	print_r($s);
	echo '</xmp><hr>';
}

 */
/*

// file tpl.htm

<table align="" valign="" bgcolor="" width="100%" height="" border="1" cellspacing="0" cellpadding="3" frame="box">
<tr bgcolor="">
	<td nowrap>id</td>
	<td nowrap>name</td>
</tr>
<!--{foreach $getAll as $k => $v}-->
<tr bgcolor="">
	<!--{loop $v as $vv}-->
	<td nowrap><!--{if $v == 3}-->l{$vv}xx<!--{else}-->r{$vv}yy<!--{/if}--></td>
	<!--{/loop}-->
</tr>
<!--{/foreach}-->
</table>

// 模板里注释用法：<!--{*这是注释内容*}-->

 */
/*
// ----------------------------------------------------------------------------------------------------
bug list :

BUG 1（仅出现在循环情况）,
<!--{loop range(1, 31) $v}--> 这样使用可能不行，
解决：
<!--{loop range(1,31) $v}-->  这样变通使用，即 1,31 没有空格
或者
{set $range = range(1, 31)}   先赋值给一个变量
<!--{loop $range $v}-->       然后再使用

 */
?>