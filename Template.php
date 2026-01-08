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
     * constructor
     * @createtime
     * @return
     * @throws       none
     * @author       Zandy
     * @modifiedby   $LastChangedBy:  $
     * @parameter
     */
    public function __construct()
    {
    }

    /**
     * constructor
     * @createtime
     * @return
     * @throws       none
     * @author       Zandy
     * @modifiedby   $LastChangedBy:  $
     * @parameter
     */
    public function Zandy_Template()
    {
        //self::__construct();
        $this->__construct();
    }

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

    public static function out($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $cacheMod = ZANDY_TEMPLATE_CACHE_MOD_PHPC)
    {
        $mods = ZANDY_TEMPLATE_CACHE_MOD_PHPC | ZANDY_TEMPLATE_CACHE_MOD_HTML | ZANDY_TEMPLATE_CACHE_MOD_EVAL;
        switch ($mods & $cacheMod) {
            case ZANDY_TEMPLATE_CACHE_MOD_PHPC:
                return Zandy_Template::outCache($tplFileName, $tplDir, $cacheDir, $forceRefreshCache);
                break;
            case ZANDY_TEMPLATE_CACHE_MOD_HTML:
            case ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS:
                return Zandy_Template::outHTML($tplFileName, $tplDir, $cacheDir, $forceRefreshCache, $mods & $cacheMod);
                break;
            case ZANDY_TEMPLATE_CACHE_MOD_EVAL:
                return Zandy_Template::outEval($tplFileName, $tplDir);
                break;
            default:
                return Zandy_Template::outCache($tplFileName, $tplDir, $cacheDir, $forceRefreshCache);
                break;
        }
    }

    public static function outString($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false)
    {
        $f = self::outCache($tplFileName, $tplDir, $cacheDir, $forceRefreshCache);
        ob_start();
        extract($GLOBALS);
        include $f;
        $r = ob_get_clean();
        return $r;
    }

    /**
     *
     * @createtime
     * @return
     * @throws       none
     * @author       Zandy
     * @modifiedby   $LastChangedBy:  $
     * @parameter
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
     * 使用 opcache_compile_file 检查语法 (最安全)
     * @param string $filename
     * @param string &$error_message
     * @param int &$error_line
     * @return bool
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
     * 使用 php -l 命令检查语法 (安全，独立进程)
     * @param string $filename
     * @param string &$error_message
     * @param int &$error_line
     * @return bool
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
     * 使用 eval 检查语法 (兜底方案，不安全但兼容性最好)
     * @param string $filename
     * @param string &$error_message
     * @param int &$error_line
     * @return bool
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

    public static function outHTML($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $outMod = ZANDY_TEMPLATE_CACHE_MOD_HTML)
    {
        //global $siteConf;
        $siteConf = isset($GLOBALS['siteConf']) ? $GLOBALS['siteConf'] : array();
        extract($GLOBALS);
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
            if (!file_exists($cacheRealFilename) || filemtime($f) > filemtime($cacheRealFilename) || $forceRefreshCache) {
                ob_start();
                $s = file_get_contents($f);
                $r = Zandy_Template::parse($s, $tplDir);
                eval($r); // need GLOBALS var
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

            }
            if ($outMod & ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS) {
                return $r; // return html contents
            }
            return $cacheRealFilename; // return html filename
        } else {
            return false;
        }
    }

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
     * 核心处理方法
     * @createtime
     * @return
     * @throws       none
     * @author       Zandy
     * @modifiedby   $LastChangedBy: Zandy $
     * @parameter
     */
    public static function parse($s, $tplDir = '', $cacheDir = '')
    {
        $uniqueReplaceString = md5(serialize(microtime())) . "_TPL___________Zandy_20060218_Zandy__________TPL_" . time() . mt_rand(0, 999999);
        $EOB = "TPL___________Zandy_20060218_Zandy__________TPL_" . $uniqueReplaceString;
        $EOB = 'Z_' . md5($EOB) . '_Y';
        // 终（总？）有一天，你会明白我这里为什么不用 EOF
        $tplDir = str_replace("\\", "/", $tplDir);
        $tplDir = preg_replace("/[\\/]+/", "/", $tplDir);
        $cacheDir = str_replace("\\", "/", $cacheDir);
        $cacheDir = preg_replace("/[\\/]+/", "/", $cacheDir);
        // 去掉注释（模板语法的注释），具体语法为 <!--{*这是注释内容*}-->
        $s = preg_replace("/\\<\\!\\-\\-\\{\\*.*\\*\\}\\-\\-\\>/isU", '', $s);
        $s = "echo <<<$EOB\r\n" . $s . "\r\n$EOB;\r\n";
        // {{{ 处理模板包含：<!--{template header.htm}-->
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
        // {{{ php 代码块：<!--{php}-->...<!--{/php}-->
        // 支持块级 PHP 代码，浏览器预览时不会破坏 HTML 结构
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "php" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/php" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        // }}}
        // {{{ set 变量：<!--{set $var = value}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "set\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        // }}}
        // {{{ logic
        // 逻辑控制语句处理：按功能分组，按匹配优先级排序（从具体到抽象）

        // === 循环语句 ===
        // for 循环：<!--{for $i = 0; $i < 10; $i++}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "for\\s+(.+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nfor(\\1){echo <<<$EOB\r\n", $s);
        // foreach 循环：<!--{foreach $items as $item}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "foreach\\s+(.+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nforeach(\\1){echo <<<$EOB\r\n", $s);

        // loop 循环（简化语法，自动检查数组）：按从具体到抽象的顺序匹配
        // <!--{loop $arr AS $key => $value}--> (最具体：包含 AS 和 =>)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3 => \\4){echo <<<$EOB\r\n", $s);
        // <!--{loop $arr $key => $value}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3 => \\4){echo <<<$EOB\r\n", $s);
        // <!--{loop $arr $key $value}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3 => \\4){echo <<<$EOB\r\n", $s);
        // <!--{loop $arr AS $value}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3){echo <<<$EOB\r\n", $s);
        // <!--{loop $arr $value}--> (最抽象：只有数组和值)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3){echo <<<$EOB\r\n", $s);

        // 循环的 else 分支：<!--{loop-else}-->, <!--{foreach-else}-->, <!--{for-else}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop-else|foreach-else|for-else)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "(.*)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(loop|foreach|for)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif(isset(\$__i__))\$__i__++;}if(isset(\$__i__))unset(\$__i__);}else{echo <<<$EOB\r\n\\2\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // 循环结束标签
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(loop)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nif(isset(\$__i__))\$__i__++;}if(isset(\$__i__))unset(\$__i__);}echo <<<$EOB\r\n", $s);
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(for)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(foreach)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);

        // === 条件语句 ===
        // if 条件：<!--{if $condition}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "if (.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nif (\\1){echo <<<$EOB\r\n", $s);
        // elseif 条件：<!--{elseif $other}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "elseif (.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}elseif (\\1){echo <<<$EOB\r\n", $s);
        // else 条件：<!--{else}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(else)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}\\1{echo <<<$EOB\r\n", $s);
        // if 结束标签：<!--{/if}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/if" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);

        // === Switch 语句 ===
        // switch 开始：<!--{switch $value}--> (支持表达式，使用 .+? 而非 \S+)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "switch\\s+(.+?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nswitch(\\1){echo <<<$EOB\r\n", $s);
        // break-case：<!--{break-case $value}--> (必须在 case 之前匹配，更具体)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "break-case\\s+(.+?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nbreak;case \\1:echo <<<$EOB\r\n", $s);
        // break-default：<!--{break-default}--> (必须在 default 之前匹配，更具体；default 不需要参数)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "break-default" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nbreak;default:echo <<<$EOB\r\n", $s);
        // case：<!--{case $value}--> (支持表达式)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "case\\s+(.+?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\ncase \\1:echo <<<$EOB\r\n", $s);
        // default：<!--{default}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(default)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1:echo <<<$EOB\r\n", $s);
        // continue：<!--{continue}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(continue)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        // break：<!--{break}--> (必须在 break-case/break-default 之后匹配，更抽象)
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(break)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
        // switch 结束标签：<!--{/switch}-->
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/switch" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // }}}
        // {{{ 变量输出语法：使用 { } 分隔符
        // 注意：变量输出使用 { } 分隔符，不会破坏 HTML 结构

        // 对时间简写的支持 20060704
        $s = preg_replace("/\\{time\\}/si", "\r\n$EOB;\r\necho time();echo <<<$EOB\r\n", $s);
        $s = preg_replace("/\\{now\\}/si", "\r\n$EOB;\r\necho date(\"Y-m-d H:i:s\");echo <<<$EOB\r\n", $s);
        $s = preg_replace("/\\{date ([\"|'])([^'\"\\}]+)\\1\\}/is", "\r\n$EOB;\r\necho date(\\1\\2\\1);echo <<<$EOB\r\n", $s);

        // 输出 PHP 常量：{CONSTANT_NAME}
        $s = preg_replace("/\\{([A-Z_]+)\\}/s", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);

        // echo 表达式：{echo expression} - 直接输出表达式结果
        $s = preg_replace("/\\{echo\\s+(.*?)\\}/si", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);
        // }}}
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
        // 语言包：{LANG key}
        $s = preg_replace('/\{LANG (.+?)\}/si', "\r\n$EOB;\r\nif(isset(\$_LANG[\"\\1\"])){echo <<<$EOB\r\n{\$_LANG[\"\\1\"]}\r\n$EOB;\r\n}elseif(isset(\$GLOBALS['siteConf']['tpl_debug'])&&\$GLOBALS['siteConf']['tpl_debug']){echo <<<$EOB\r\n#\\1#\r\n$EOB;\r\n}else{echo <<<$EOB\r\n\\1\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
        // {{{ include/include_once：<!--{include file.php}-->
        // 包含php文件时也会对其内容进行处理（20060301 发现未必应该有这样的担忧）
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "include\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/is", "'\r\n$EOB;\r\ninclude \"\\1\";echo <<<$EOB\r\n'", $s);
        $s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "include_once\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/is", "'\r\n$EOB;\r\ninclude_once \"\\1\";echo <<<$EOB\r\n'", $s);
        // }}}
        // {{{
        /*
         * 下面这样可以让编译后的代码输出语句是用双引号引起来的，
         * 不要也没任何问题($GLOBALS['siteConf']['EOF'] 的需要设为 0 或 1)
         * 这里需要注意的是模板里的变量不能含有双引号了，{$al['article_id']} 不能写为 {$al["article_id"]}
        if (ZANDY_TEMPLATE_CACHE_SIMPLE ^ intval(isset($GLOBALS['siteConf']) && isset($GLOBALS['siteConf']['EOF']) && $GLOBALS['siteConf']['EOF']))
        {
            //$s = str_replace("/echo \<\<\<$EOB"."\r\n(.+)\r\n$EOB".";\r\n/iUs", "echo \"\\1\";", $s);
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
        //p($s);//die();
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
     * 实现了 php4 没有的递归创建目录
     * 下面是 mkdir 的 php5 的原型
     * mkdir ( string pathname [, int mode [, bool recursive [, resource context]]] )
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
     * adjustPath 整理类似“/a/b/c/d/.././e/f”成“a/b/c/e/f”
     * @createtime
     * @param
     * @return
     * @throws       none
     * @author       Zandy
     * @modifiedby   $LastChangedBy:  $
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

    public static function sendAlarmEmail($msg)
    {
        if (function_exists('send_mail')) {
            $title = '<title>[Sev-2]Template Engine Error</title>';
            $alarm_email = 'alarm2@tetx.com';
            if (isset($GLOBALS['ON_PRODUCT']) && $GLOBALS['ON_PRODUCT']) {
                $title = '<title>[Sev-1]Template Engine Error</title>';
                $alarm_email = 'alarm1@tetx.com';
            }
            $msg = $title . $msg;
            $msg .= "<hr><p>PST: " . date("Y-m-d H:i:s") . "</p><hr>";
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