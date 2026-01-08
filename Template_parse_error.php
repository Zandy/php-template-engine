<?php
/**
 * ParseError 处理 - PHP 5.6 版本
 * 
 * PHP 5.6 中语法错误不会抛出异常，只会输出到缓冲区
 * 注意：@eval 会抑制错误输出，但 error_get_last() 可以获取错误信息
 */

// 清除之前的错误
if (function_exists('error_clear_last'))
{
	error_clear_last();
}

@eval($evalstr);

// 检查是否有语法错误
$error = error_get_last();
if ($error && ($error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR))
{
	// 语法错误，错误信息会通过 ob_get_clean() 获取
	// 但 @eval 会抑制输出，所以需要使用 error_get_last()
	// 这里我们设置一个标志，让主代码知道有错误
	$GLOBALS['_zte_eval_error'] = $error;
}
