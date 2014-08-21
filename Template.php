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
 * 说明：需要输出的变量请用{ }将其包含
 * 如 {$getAll[$k]['name']} 或这样 ${getAll[$k]['name']}
 * 但变量本身不可以再含 { } 了
 * 如果不是数组变量，也可以不要{ }括起来，直接 $var
 * 包含php文件时也会对其内容进行处理，这是不好的地方

// {{{ 下面是模板的样本 test.htm

<!--{include tpl/header.htm}-->

{$helloTPL}, $helloTPL

<!--{include tpl/footer.htm}-->

// }}}

// {{{ 输出方式可以有 3 种

// 1, include Zandy_Template::outCache($tplFileName = 'test.htm', $tplDir = $siteConf['tplDir']);

// 2, header("location: ".Zandy_Template::outHTML($tplFileName = 'test.htm', $tplDir = $siteConf['tplDir']));
// or include ("location: ".Zandy_Template::outHTML($tplFileName = 'test.htm', $tplDir = $siteConf['tplDir']));

// 3, eval(Zandy_Template::outEval($tplFileName = 'test.htm', $tplDir = $siteConf['tplDir']));

// }}}
 * 系列函数

// {{{ 当然也可以这样
1, include_once(Zandy_Template::out($tplName, $siteConf['tplDir'], '', false, ZANDY_TEMPLATE_CACHE_MOD_PHPC));
2, echo Zandy_Template::out($tplName, $siteConf['tplDir'], '', false, ZANDY_TEMPLATE_CACHE_MOD_HTML);
3, eval(Zandy_Template::out($tplName, $siteConf['tplDir'], '', false, ZANDY_TEMPLATE_CACHE_MOD_EVAL));
// }}}

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
define('ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT', '<!--{'); // 20060329
define('ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT', '}-->'); // 20060329
#define('ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT_QUOTE',  preg_quote(ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT)); // 20060329
#define('ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT_QUOTE', preg_quote(ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT)); // 20060329
define('ZANDY_TEMPLATE_DELIMITER_VAR_LEFT', '{'); // 20061226
define('ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT', '}'); // 20061226


#define('ZANDY_TEMPLATE_DELIMITER_VAR_LEFT_QUOTE',  preg_quote(ZANDY_TEMPLATE_DELIMITER_VAR_LEFT)); // 20061226
#define('ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT_QUOTE', preg_quote(ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT)); // 20061226
#defined('ZANDY_TEMPLATE_INONEFILE') || define('ZANDY_TEMPLATE_INONEFILE', FALSE); // 20060408
class Zandy_Template
{

	/**
	 * constructor
	 * @createtime
	 * @author       Zandy
	 * @modifiedby   $LastChangedBy:  $
	 * @parameter
	 * @return
	 * @throws       none
	 */
	public function __construct()
	{
	}

	/**
	 * constructor
	 * @createtime
	 * @author       Zandy
	 * @modifiedby   $LastChangedBy:  $
	 * @parameter
	 * @return
	 * @throws       none
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
		if ($send_email)
		{
			self::sendAlarmEmail($msg);
		}
		echo $msg;
		die();
	}

	public static function out($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $cacheMod = ZANDY_TEMPLATE_CACHE_MOD_PHPC)
	{
		$mods = ZANDY_TEMPLATE_CACHE_MOD_PHPC | ZANDY_TEMPLATE_CACHE_MOD_HTML | ZANDY_TEMPLATE_CACHE_MOD_EVAL;
		switch ($mods & $cacheMod)
		{
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
	 * @author       Zandy
	 * @modifiedby   $LastChangedBy:  $
	 * @parameter
	 * @return
	 * @throws       none
	 */
	public static function outCache($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false)
	{
		// {{{ 接管 error handler
		if (!function_exists('zte_error_handler'))
		{
			function zte_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
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
		set_error_handler("zte_error_handler", E_ALL ^ E_STRICT);
		// }}}
		
		if (substr($tplFileName, -4) != '.htm' && substr($tplFileName, -5) != '.html')
		{
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
		if (!$tplDir2 || !$tplBaseDir || false === stripos($tplDir2, $tplBaseDir))
		{
			self::halt('$tplDir("' . $tplDir . '") is not a valid tpl path', true);
		}
		$cacheDir2 = '' != $cacheDir ? $cacheDir : $siteConf['tplCacheBaseDir'];
		$cacheDir2 = realpath($cacheDir2) ? realpath($cacheDir2) . DIRECTORY_SEPARATOR : $cacheDir2;
		$cacheDir2 = '' != $cacheDir ? $cacheDir : $siteConf['tplCacheBaseDir'];
		// {{{ check
		if (empty($tplDir2) || empty($cacheDir2))
		{
			self::halt('lost parameter "$tplDir" or "$cacheDir"', true);
		}
		// }}}
		
		if (defined('PROJECT_NAME'))
		{
			$host = strtolower(PROJECT_NAME);
		}
		else
		{
			$host = str_replace(":", "_", $_SERVER['HTTP_HOST']);
		}
		$host = 'ztec/' . ($host == '' ? 'cli' : '') . $host;
		
		$index = substr(basename($tplFileName), 0, 1);
		$xx = str_replace($tplBaseDir, '', $tplDir2); // 取得文件相对目录层次以创建cache目录
		$cacheDir2 = $cacheDir2 . $host . '/' . $index . '/' . $xx;
		$cacheDir2 = preg_replace("/[\\\\\\/]+/", DIRECTORY_SEPARATOR, $cacheDir2);
		self::mkdir($cacheDir2, 0777, true);
		if (!$cacheDir2 || !$tplCacheBaseDir || false === stripos(realpath($cacheDir2), $tplCacheBaseDir))
		{
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
		if (is_readable($f))
		{
			$cacheRealFilename = $cacheDir2 . $tplFileName . '.' . md5($f) . '.php';
			$cacheRealDir = dirname($cacheRealFilename) . DIRECTORY_SEPARATOR;
			$cacheRealDir = Zandy_Template::adjustPath($cacheRealDir);
			if (!file_exists($cacheRealDir))
			{
				Zandy_Template::mkdir($cacheRealDir, 0777, true);
			}
			// tplCacheMaxTime default is one day
			$tplCacheMaxTime = isset($siteConf['tplCacheMaxTime']) && $siteConf['tplCacheMaxTime'] > 0 ? $siteConf['tplCacheMaxTime'] : 3 * 60 * 60;
			if (!file_exists($cacheRealFilename) || filemtime($cacheRealFilename) + $tplCacheMaxTime < time() || filemtime($f) > filemtime($cacheRealFilename) || $forceRefreshCache || (isset($GLOBALS['siteConf']['forceRefreshCache']) && $GLOBALS['siteConf']['forceRefreshCache']) || (defined('TPL_FORCE_CACHE') && TPL_FORCE_CACHE) || (isset($_SERVER['SCRIPT_FILENAME']) && filemtime($_SERVER['SCRIPT_FILENAME']) > filemtime($cacheRealFilename)))
			{
				$s = file_get_contents($f);
				if (function_exists('html_compress'))
				{
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
		}
		else
		{
			$msg = '<p>The template file <b>' . $f . '</b> does not exists!</p>';
			self::halt($msg, true);
			die();
			//return false;
		}
	}

	public static function check_syntax($filename, $tplName = '')
	{
		// {{{ define function: php_check_syntax
		if (!function_exists('php_check_syntax'))
		{
			function php_check_syntax($filename, &$error_message = null)
			{
				$tmpcontent = file_get_contents($filename);
				
				$evalstr = "return true; ?>" . $tmpcontent . "<?php ";
				
				// {{{ 以后注意这里是否有潜在bug
				ob_start();
				eval($evalstr);
				$obcontent = ob_get_clean();
				// }}}
				if ($obcontent)
				{
					preg_match('/on line (\<b\>)?(?P<line>\d+)/is', $obcontent, $mmm);
					if (isset($mmm['line']) && $mmm['line'] >= 0)
					{
						$line = $mmm['line'];
						$explode = explode("\n", $tmpcontent);
						$all = sizeof($explode);
						
						$ec = explode(" in ", $obcontent);
						
						$error_message = "{$ec[0]} in $filename on line $line";
						return false;
					}
				}
				
				return true;
			}
		}
		// }}}
		if (!php_check_syntax($filename, $error_message))
		{
			preg_match('/on line (?P<line>\d+)/is', $error_message, $mmm);
			if (isset($mmm['line']) && $mmm['line'] >= 0)
			{
				$line = $mmm['line'];
				$explode = explode("\n", file_get_contents($filename));
				
				$tplinfo = empty($tplName) ? '' : "template file is $tplName<br />";
				$msg = "<div style=\"border: 1px solid blue; padding: 3px; font-size: 12px;\">";
				$msg .= $tplinfo . "<hr size=\"1\" />" . $error_message;
				$msg .= "<div style=\"border: 1px solid red; padding: 3px;\">";
				/*$msg .= highlight_string("<?php\r\n" . $explode[$line - 1] . "\r\n?>", true);*/
				$msg .= "<strong>prev line:</strong>" . str_replace(" ", "&nbsp;", htmlspecialchars($explode[$line - 2])) . "<br />";
				$msg .= "<span style=\"color: blue;\"><strong style=\"color: red;\">error line:</strong>" . str_replace(" ", "&nbsp;", htmlspecialchars($explode[$line - 1])) . "</span>" . "<br />";
				$msg .= "<strong>next line:</strong>" . str_replace(" ", "&nbsp;", htmlspecialchars($explode[$line]));
				/*$msg .= highlight_string($explode[$line - 1], true);*/
				$msg .= "</div></div>";

				self::halt($msg, true);
			}
			else
			{
				self::halt($error_message, true);
			}
		}
	}

	public static function outHTML($tplFileName, $tplDir = '', $cacheDir = '', $forceRefreshCache = false, $outMod = ZANDY_TEMPLATE_CACHE_MOD_HTML)
	{
		//global $siteConf;
		$siteConf = isset($GLOBALS['siteConf']) ? $GLOBALS['siteConf'] : array();
		extract($GLOBALS);
		$tplDir = '' != $tplDir ? $tplDir : $siteConf['tplDir'];
		if ($cacheDir)
		{
			$a = pathinfo($tplFileName);
			$cacheRealFilename = substr($cacheDir . $a['basename'], 0, -1 * (strlen($a['extension']) + 1)) . '.htm';
		}
		else
		{
			$cacheDir = '' != $cacheDir ? $cacheDir : $siteConf['cacheHTMLDir'];
			$a = pathinfo($cacheDir . $tplFileName);
			$cacheRealFilename = substr($cacheDir . $tplFileName, 0, -1 * (strlen($a['extension']) + 1)) . '.htm';
		}
		$f = $tplDir . $tplFileName;
		if (is_file($f))
		{
			if (!file_exists($cacheRealFilename) || filemtime($f) > filemtime($cacheRealFilename) || $forceRefreshCache)
			{
				ob_start();
				$s = file_get_contents($f);
				$r = Zandy_Template::parse($s, $tplDir);
				eval($r); // need GLOBALS var
				$r = ob_get_clean();
				$cacheRealDir = dirname($cacheDir . $tplFileName);
				if (!file_exists($cacheRealDir))
				{
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
			if ($outMod & ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS)
			{
				return $r; // return html contents
			}
			return $cacheRealFilename; // return html filename
		}
		else
		{
			return false;
		}
	}

	public static function outEval($tplFileName, $tplDir = '')
	{
		//global $siteConf;
		$siteConf = isset($GLOBALS['siteConf']) ? $GLOBALS['siteConf'] : array();
		$tplDir = '' != $tplDir ? $tplDir : $siteConf['tplDir'];
		$f = $tplDir . $tplFileName;
		if (is_file($f))
		{
			$s = file_get_contents($f);
			$r = Zandy_Template::parse($s, $tplDir);
			return $r;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 核心处理方法
	 * @createtime
	 * @author       Zandy
	 * @modifiedby   $LastChangedBy: Zandy $
	 * @parameter
	 * @return
	 * @throws       none
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
		// {{{ 处理这样的模板包含： <!--{template header.htm}-->    20060519 | 20061226 补充，为了向前兼容故保留
		$m = array();
		preg_match_all("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "template\\s+([^\\}^\\s]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/ies", $s, $m);
		if (is_array($m[0]) && is_array($m[1]))
		{
			foreach ($m[1] as $k => $v)
			{
				//$s = str_replace($m[0][$k], "\r\n$EOB;\r\ninclude Zandy_Template::outCache(\"" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . "\");echo <<<$EOB\r\n", $s);
				$tmp_md5 = uniqid('zte') . mt_rand(1, 999999);
				$s = str_replace($m[0][$k], "\r\n$EOB;\r\n\$tpl_$tmp_md5 = Zandy_Template::outCache(\"" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . "\");if(empty(\$tpl_$tmp_md5)){Zandy_Template::halt('template file: ' . \$tpl_$tmp_md5 . '; gettype: ' . gettype(\$tpl_$tmp_md5) . '; params:(" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . ")');}\$$tmp_md5 = require \$tpl_$tmp_md5;if(\$$tmp_md5===false){Zandy_Template::halt('template file: \'" . $tplDir . $v . "\'<br><br>include compiled file \'' . \$tpl_$tmp_md5 . '\' failed.<br>\$_SERVER[\"SERVER_ADDR\"]: " . $_SERVER["SERVER_ADDR"] . "<br>\$server_host: " . (isset($GLOBALS['server_host']) ? $GLOBALS['server_host'] : '') . "', true);}echo <<<$EOB\r\n", $s);
			}
		}
		// 处理这样的模板包含： {template header.htm}
		$m = array();
		preg_match_all("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "template\\s+([^\\}^\\s]+)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/ies", $s, $m);
		if (is_array($m[0]) && is_array($m[1]))
		{
			foreach ($m[1] as $k => $v)
			{
				//$s = str_replace($m[0][$k], "\r\n$EOB;\r\ninclude Zandy_Template::outCache(\"" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . "\");echo <<<$EOB\r\n", $s);
				$tmp_md5 = uniqid('zte') . mt_rand(1, 999999);
				$s = str_replace($m[0][$k], "\r\n$EOB;\r\n\$tpl_$tmp_md5 = Zandy_Template::outCache(\"" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . "\");if(empty(\$tpl_$tmp_md5)){Zandy_Template::halt('template file: ' . \$tpl_$tmp_md5 . '; gettype: ' . gettype(\$tpl_$tmp_md5) . '; params:(" . $v . "\", \"" . $tplDir . "\", \"" . $cacheDir . ")');}\$$tmp_md5 = require \$tpl_$tmp_md5;if(\$$tmp_md5===false){Zandy_Template::halt('template file: \'" . $tplDir . $v . "\'<br><br>include compiled file \'' . \$tpl_$tmp_md5 . '\' failed.<br>\$_SERVER[\"SERVER_ADDR\"]: " . $_SERVER["SERVER_ADDR"] . "<br>\$server_host: " . (isset($GLOBALS['server_host']) ? $GLOBALS['server_host'] : '') . "', true);}echo <<<$EOB\r\n", $s);
			}
		}
		// }}}
		// {{{ php 代码
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "php\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "php\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		// }}}
		// {{{ set 变量
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "set\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "set\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		// }}}
		// {{{ eval 执行 php 代码，同上面的 php
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "eval\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "eval\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		// }}}
		// {{{ echo
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "echo\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "%echo\\s(.*?)%" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/si", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "echo\\s(.*?)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/si", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);
		// }}}
		// {{{ logic
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "for\\s+(.+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nfor(\\1){echo <<<$EOB\r\n", $s);
		
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop|foreach)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3){echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop|foreach)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3){echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop|foreach)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3 => \\4){echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop|foreach)\\s+(\\S+)\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3 => \\4){echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loop|foreach)\\s+(\\S+)\\s+AS\\s+(\\S+)\\s*\\=\\>\\s*(\\S+)\\s*" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif (is_array(\\2)&&sizeof(\\2)>0){\$__i__=0;foreach(\\2 as \\3 => \\4){echo <<<$EOB\r\n", $s);
		
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(loopelse|elseloop|forelse|elsefor|foreachelse)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "(.*)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(loop|for)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/siU", "\r\n$EOB;\r\nif(isset(\$__i__))\$__i__++;}if(isset(\$__i__))unset(\$__i__);}else{echo <<<$EOB\r\n\\2\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(loop|foreach)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nif(isset(\$__i__))\$__i__++;}if(isset(\$__i__))unset(\$__i__);}echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/(for)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
		
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "if (.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nif (\\1){echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "elseif (.*?)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}elseif (\\1){echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(else)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}\\1{echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/if" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "switch (\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nswitch(\\1){echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "case (\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\ncase \\1:echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "break case (\\S+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\nbreak;case \\1:echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(default)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1 :echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(continue)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "(break)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n\\1;echo <<<$EOB\r\n", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "\\/switch" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/si", "\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
		// }}}
		// 换行符号
		$s = preg_replace("/\\{LF\\}/si", "\r\n", $s);
		// {{{ 对时间简写的支持 20060704
		$s = preg_replace("/\\{(time|now)\\}/si", "\r\n$EOB;\r\necho time();echo <<<$EOB\r\n", $s);
		$s = preg_replace("/\\{date ([\"|'][^\"\\}]+[\"|'])( [^\\}]*)\\}/is", "\r\n$EOB;\r\necho date(\\1, \\2);echo <<<$EOB\r\n", $s);
		$s = preg_replace("/\\{date ([\"|'][^\"\\}]+[\"|'])\\}/is", "\r\n$EOB;\r\necho date(\\1);echo <<<$EOB\r\n", $s);
		// }}}
		// 输入 php 常量
		$s = preg_replace("/\\{([A-Z_]+)\\}/s", "\r\n$EOB;\r\necho \\1;echo <<<$EOB\r\n", $s);
		/*
		// {{{ 数组的简单访问方式支持 e.g. {arr key1 num2 key3} 解析后为 {$arr['key1'][num2]['key3']}
		$m = array();
		preg_match_all("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(( [a-zA-Z0-9_\x7f-\xff]*)*)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/ies", $s, $m);
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
		#preg_match_all("/".ZANDY_TEMPLATE_DELIMITER_VAR_LEFT."[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*((\.[a-zA-Z0-9_\x7f-\xff]*(\([^\)]*\))?)+)".ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT."/ies", $s, $m);
		preg_match_all("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\.[a-zA-Z0-9_\x7f-\xff]*([a-zA-Z0-9_\x7f-\xff\"'\(\)\[\]\=\>\$, -]*))+" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/ies", $s, $m);
		if (is_array($m[0]) && is_array($m[1]))
		{
			foreach ($m[0] as $k => $v)
			{
				$s = str_replace($m[0][$k], Zandy_Template::parseObject(substr($v, strlen(ZANDY_TEMPLATE_DELIMITER_VAR_LEFT), -1 * strlen(ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT))), $s);
			}
		}
		// }}}
		*/
		//$s = preg_replace('/\{LANG (.+?)\}/si', "{\$_LANG['\\1']}", $s);
		$s = preg_replace('/\{LANG (.+?)\}/si', "\r\n$EOB;\r\nif(isset(\$_LANG[\"\\1\"])){echo <<<$EOB\r\n{\$_LANG[\"\\1\"]}\r\n$EOB;\r\n}elseif(isset(\$GLOBALS['siteConf']['tpl_debug'])&&\$GLOBALS['siteConf']['tpl_debug']){echo <<<$EOB\r\n#\\1#\r\n$EOB;\r\n}else{echo <<<$EOB\r\n\\1\r\n$EOB;\r\n}echo <<<$EOB\r\n", $s);
		// 包含php文件时也会对其内容进行处理，这是不好的地方（20060301 发现未必应该有这样的担忧）
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "include\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/ies", "'\r\n$EOB;\r\ninclude \"\\1\";echo <<<$EOB\r\n'", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "include\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/ies", "'\r\n$EOB;\r\ninclude \"\\1\";echo <<<$EOB\r\n'", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_LOGIC_LEFT . "include_once\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_LOGIC_RIGHT . "/ies", "'\r\n$EOB;\r\ninclude_once \"\\1\";echo <<<$EOB\r\n'", $s);
		$s = preg_replace("/" . ZANDY_TEMPLATE_DELIMITER_VAR_LEFT . "include_once\\s+([^\\}]+)" . ZANDY_TEMPLATE_DELIMITER_VAR_RIGHT . "/ies", "'\r\n$EOB;\r\ninclude_once \"\\1\";echo <<<$EOB\r\n'", $s);
		// {{{
		/**
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

	/**
	 * 对对象的访问的简单支持
	function parseObject($var)
	{
		$a = str_replace('.', '->', $var);
		$r = '{$' . $a . '}';
		return $r;
	}
	 */
	
	/**
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
		if (file_exists($pathname))
		{
			return true;
		}
		$pathname = Zandy_Template::adjustDir($pathname);
		if (PHP_VERSION >= '5.0.0')
		{
			$m = (null != $context ? mkdir($pathname, $mode, $recursive, $context) : ($recursive ? mkdir($pathname, $mode, $recursive) : (null != $mode ? mkdir($pathname, $mode) : mkdir($pathname))));
			@chmod($pathname, $mode);
		}
		else
		{
			if ($recursive)
			{
				$a = explode(DIRECTORY_SEPARATOR, $pathname);
				$b = substr($pathname, 0, 1) == DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '';
				foreach ($a as $v)
				{
					$b .= $v . DIRECTORY_SEPARATOR;
					if (!@file_exists($b))
					{
						@mkdir($b, $mode);
						@chmod($b, $mode);
					}
				}
				return true;
			}
			elseif (null != $mode)
			{
				$m = mkdir($pathname, $mode);
				@chmod($pathname, $mode);
			}
			elseif ($pathname && PHP_VERSION < '4.2.0')
			{
				$m = mkdir($pathname, 0777);
				@chmod($pathname, 0777);
			}
			else
			{
				$m = mkdir($pathname);
			}
		}
		return $m;
	}

	/**
	 * adjustPath 整理类似“/a/b/c/d/.././e/f”成“a/b/c/e/f”
	 * @createtime
	 * @author       Zandy
	 * @modifiedby   $LastChangedBy:  $
	 * @param
	 * @return
	 * @throws       none
	 */
	public static function adjustPath($path)
	{
		$b = explode(DIRECTORY_SEPARATOR, Zandy_Template::adjustDir($path));
		$c = array();
		if (substr($b[0], -1) == ':')
		{
			for ($i = 0; $i < sizeof($b); $i++)
			{
				$v = $b[$i];
				if ($i > 1 && $v == '.')
				{
					continue;
				}
				elseif ($i > 1 && $v == '..' && sizeof($c) > 1)
				{
					array_pop($c);
				}
				else
				{
					$c[] = $v;
				}
			}
		}
		elseif ($b[0] == '')
		{
			$b[1] = DIRECTORY_SEPARATOR . $b[1];
			for ($i = 1; $i < sizeof($b); $i++)
			{
				$v = $b[$i];
				if ($i > 1 && $v == '.')
				{
					continue;
				}
				elseif ($i > 1 && $v == '..' && sizeof($c) > 1)
				{
					array_pop($c);
				}
				else
				{
					$c[] = $v;
				}
			}
		}
		else
		{
			for ($i = 0; $i < sizeof($b); $i++)
			{
				$v = $b[$i];
				if ($i > 0 && $v == '.')
				{
					continue;
				}
				elseif ($i > 0 && $v == '..' && sizeof($c) > 0)
				{
					array_pop($c);
				}
				else
				{
					$c[] = $v;
				}
			}
		}
		$d = join(DIRECTORY_SEPARATOR, $c);
		return $d;
	}
	
	public static function sendAlarmEmail($msg)
	{
		if (function_exists('send_mail'))
		{
			$title = '<title>[Sev-2]Template Engine Error</title>';
			$alarm_email = 'alarm2@tetx.com';
			if (isset($GLOBALS['ON_PRODUCT']) && $GLOBALS['ON_PRODUCT'])
			{
				$title = '<title>[Sev-1]Template Engine Error</title>';
				$alarm_email = 'alarm1@tetx.com';
			}
			$msg = $title . $msg;
			$msg .= "<hr><p>PST: " . date("Y-m-d H:i:s") . "</p><hr>";
			if (file_exists('/var/job/hostname.conf'))
			{
				$msg .= file_get_contents('/var/job/hostname.conf');
			}
			if (isset($GLOBALS['server_host']))
			{
				$msg .= "<br>" . $GLOBALS['server_host'];
			}
			
			$msg .= "<br>" . print_r(debug_backtrace(), true);
			
			@send_mail($alarm_email, $msg, '', NOTICE_EMAIL, 'SYSTEM', '');
		}
	}

	public static function friendlyErrorType($type)
	{
		switch ($type)
		{
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
			case E_STRICT: // 2048 //
				return 'E_STRICT';
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


/**

// reg test:

$a = '\/';
var_dump($a, preg_quote($a, '/'), str_replace('\\', '\\\\', preg_quote($a, '/')));


// usage:

$getAll = array(
	0 => array('id' => 1, 'name' => 'a'),
	1 => array('id' => 2, 'name' => 'b'),
	2 => array('id' => 3, 'name' => 'c'),
	3 => array('id' => 4, 'name' => 'd'),
	4 => array('id' => 5, 'name' => 'e'),
	5 => array('id' => 6, 'name' => 'f'),
);

$filename = "tpl.php";
$contents = file_get_contents($filename);
//include Zandy_Template::outCache($contents);

// or
ob_start();
eval(Zandy_Template::outEval($contents));
$final_html = ob_get_clean();

echo $final_html;


function p($s){
	echo '<xmp>';
	print_r($s);
	echo '</xmp><hr>';
}

 */
/**

// file tpl.php

<table align="" valign="" bgcolor="" width="100%" height="" border="1" cellspacing="0" cellpadding="3" frame="box">
<tr bgcolor="">
	<td nowrap>id</td>
	<td nowrap>name</td>
</tr>
<!--{loop $getAll $k $v}-->
<tr bgcolor="">
	<!--{loop $v $vv}-->
	<td nowrap><!--{if $v == 3}-->l{$vv}ll<!--{else}-->r{$vv}rr<!--{/if}--></td>
	<!--{/loop}-->
</tr>
<!--{/loop}-->
</table>

// 模板里注释用法：<!--{*这是注释内容*}-->

 */
/**
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
