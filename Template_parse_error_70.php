<?php
/**
 * ParseError 处理 - PHP 7.0+ 版本
 * 
 * PHP 7.0+ 中语法错误会抛出 ParseError 异常
 */

try
{
	@eval($evalstr);
}
catch (ParseError $e)
{
	$parse_error = $e;
}
catch (Error $e)
{
	$parse_error = $e;
}
catch (Exception $e)
{
	$parse_error = $e;
}
