<?php
/**
 * 错误级别配置
 * 
 * PHP 8.4+ 中 E_STRICT 已被废弃，此文件用于 PHP 8.4 以下版本
 * PHP 8.4+ 版本直接使用 E_ALL
 */

// PHP 8.4 以下版本：排除 E_STRICT
$error_level = E_ALL ^ E_STRICT;

