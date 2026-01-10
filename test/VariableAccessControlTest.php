<?php
/**
 * 变量访问控制单元测试
 * 
 * 测试模板引擎的变量访问控制功能：
 * - open 模式（完全开放，默认）
 * - whitelist 模式（白名单）
 * - explicit 模式（显式传递）
 * - 变量泄露防护
 */

require_once __DIR__ . '/../Template.php';

// 抑制预期的 PHP Warning（未定义变量是测试的一部分）
// 这些警告不影响测试结果，但会让输出更清晰
$oldErrorReporting = error_reporting(E_ALL & ~E_WARNING);

class VariableAccessControlTest {
    private $tplDir;
    private $cacheDir;
    private $passed = 0;
    private $failed = 0;
    
    public function __construct() {
        $this->tplDir = __DIR__ . '/../examples/templates/';
        $this->cacheDir = sys_get_temp_dir() . '/zte_test_cache_' . uniqid() . '/';
        
        // 确保缓存目录存在
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        
        // 设置必要的配置
        // tplBaseDir 应该是包含 tplDir 的父目录
        $tplBaseDir = dirname($this->tplDir);
        $GLOBALS['siteConf'] = array(
            'tplBaseDir' => $tplBaseDir,
            'tplCacheBaseDir' => $this->cacheDir,
            'tplDir' => $this->tplDir,
        );
    }
    
    public function __destruct() {
        // 清理测试文件
        $this->cleanup();
        // 清理全局变量
        $this->cleanupGlobals();
    }
    
    /**
     * 测试 open 模式（完全开放）
     */
    public function testOpenMode() {
        // 清理之前的配置
        unset($GLOBALS['siteConf']['template_vars_mode']);
        unset($GLOBALS['siteConf']['template_vars_whitelist']);
        
        $GLOBALS['user'] = array('name' => '张三');
        $GLOBALS['data'] = array('title' => '测试');
        $GLOBALS['secret'] = '这是敏感信息';
        
        $html = Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir);
        
        // open 模式应该可以访问所有全局变量
        $vars = Zandy_Template::getTemplateVars();
        
        $this->assert(isset($vars['user']), 'open 模式：可以访问 user');
        $this->assert(isset($vars['data']), 'open 模式：可以访问 data');
        $this->assert(isset($vars['secret']), 'open 模式：可以访问 secret（所有全局变量）');
        
        unset($GLOBALS['user'], $GLOBALS['data'], $GLOBALS['secret']);
    }
    
    /**
     * 测试 whitelist 模式（白名单）
     */
    public function testWhitelistMode() {
        // 设置白名单模式
        $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
        $GLOBALS['siteConf']['template_vars_whitelist'] = array('user', 'data');
        
        $GLOBALS['user'] = array('name' => '李四');
        $GLOBALS['data'] = array('title' => '测试');
        $GLOBALS['secret'] = '这是敏感信息，不应该被访问';
        
        $vars = Zandy_Template::getTemplateVars();
        
        $this->assert(isset($vars['user']), 'whitelist 模式：可以访问白名单中的 user');
        $this->assert(isset($vars['data']), 'whitelist 模式：可以访问白名单中的 data');
        $this->assert(!isset($vars['secret']), 'whitelist 模式：不能访问白名单外的 secret');
        $this->assert(isset($vars['siteConf']), 'whitelist 模式：始终包含 siteConf');
        
        unset($GLOBALS['user'], $GLOBALS['data'], $GLOBALS['secret']);
        unset($GLOBALS['siteConf']['template_vars_mode'], $GLOBALS['siteConf']['template_vars_whitelist']);
    }
    
    /**
     * 测试 whitelist 模式 - 空白名单回退到 open 模式
     */
    public function testWhitelistModeEmpty() {
        // 设置白名单模式，但白名单为空
        $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
        $GLOBALS['siteConf']['template_vars_whitelist'] = array();
        
        $GLOBALS['user'] = array('name' => '王五');
        
        $vars = Zandy_Template::getTemplateVars();
        
        // 空白名单应该回退到完全开放模式
        $this->assert(isset($vars['user']), 'whitelist 模式（空）：回退到 open 模式');
        
        unset($GLOBALS['user']);
        unset($GLOBALS['siteConf']['template_vars_mode'], $GLOBALS['siteConf']['template_vars_whitelist']);
    }
    
    /**
     * 测试 explicit 模式（显式传递）
     */
    public function testExplicitMode() {
        // 设置 explicit 模式
        $GLOBALS['siteConf']['template_vars_mode'] = 'explicit';
        
        $GLOBALS['user'] = array('name' => '赵六');
        $GLOBALS['secret'] = '这是敏感信息，不应该被访问';
        
        $vars = Zandy_Template::getTemplateVars();
        
        $this->assert(!isset($vars['user']), 'explicit 模式：不能访问全局变量 user');
        $this->assert(!isset($vars['secret']), 'explicit 模式：不能访问全局变量 secret');
        $this->assert(isset($vars['siteConf']), 'explicit 模式：始终包含 siteConf');
        
        unset($GLOBALS['user'], $GLOBALS['secret']);
        unset($GLOBALS['siteConf']['template_vars_mode']);
    }
    
    /**
     * 测试显式传递变量（优先级最高）
     */
    public function testExplicitVarsPriority() {
        // 设置 whitelist 模式
        $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
        $GLOBALS['siteConf']['template_vars_whitelist'] = array('user', 'data');
        
        $GLOBALS['user'] = array('name' => '全局变量');
        $GLOBALS['data'] = array('title' => '全局数据');
        
        // 显式传递变量（应该优先于配置）
        $explicitVars = array(
            'user' => array('name' => '显式变量'),
            'custom' => array('value' => '自定义变量')
        );
        
        $vars = Zandy_Template::getTemplateVars($explicitVars);
        
        $this->assert(isset($vars['user']), '显式传递：包含显式传递的变量');
        $this->assert($vars['user']['name'] === '显式变量', '显式传递：优先级高于全局变量');
        $this->assert(isset($vars['custom']), '显式传递：包含显式传递的自定义变量');
        $this->assert(!isset($vars['data']), '显式传递：不包含全局变量（即使不在显式传递中）');
        $this->assert(isset($vars['siteConf']), '显式传递：始终包含 siteConf');
        
        unset($GLOBALS['user'], $GLOBALS['data']);
        unset($GLOBALS['siteConf']['template_vars_mode'], $GLOBALS['siteConf']['template_vars_whitelist']);
    }
    
    /**
     * 测试变量泄露防护 - open 模式
     */
    public function testVariableLeakProtectionOpen() {
        // 清理配置
        unset($GLOBALS['siteConf']['template_vars_mode']);
        
        $GLOBALS['sensitive_db_password'] = 'secret123';
        $GLOBALS['api_key'] = 'key456';
        
        // 使用 outString 应该可以访问（open 模式）
        $html = Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir);
        
        $vars = Zandy_Template::getTemplateVars();
        
        // open 模式下，所有变量都可以访问（这是设计特性）
        $this->assert(isset($vars['sensitive_db_password']), 'open 模式：可以访问敏感变量（设计特性）');
        
        unset($GLOBALS['sensitive_db_password'], $GLOBALS['api_key']);
    }
    
    /**
     * 测试变量泄露防护 - whitelist 模式
     */
    public function testVariableLeakProtectionWhitelist() {
        // 设置 whitelist 模式
        $GLOBALS['siteConf']['template_vars_mode'] = 'whitelist';
        $GLOBALS['siteConf']['template_vars_whitelist'] = array('user');
        
        $GLOBALS['user'] = array('name' => '用户');
        $GLOBALS['sensitive_db_password'] = 'secret123';
        $GLOBALS['api_key'] = 'key456';
        
        $vars = Zandy_Template::getTemplateVars();
        
        $this->assert(isset($vars['user']), 'whitelist 模式：可以访问白名单变量');
        $this->assert(!isset($vars['sensitive_db_password']), 'whitelist 模式：不能访问敏感变量');
        $this->assert(!isset($vars['api_key']), 'whitelist 模式：不能访问敏感变量');
        
        unset($GLOBALS['user'], $GLOBALS['sensitive_db_password'], $GLOBALS['api_key']);
        unset($GLOBALS['siteConf']['template_vars_mode'], $GLOBALS['siteConf']['template_vars_whitelist']);
    }
    
    /**
     * 测试变量泄露防护 - explicit 模式
     */
    public function testVariableLeakProtectionExplicit() {
        // 设置 explicit 模式
        $GLOBALS['siteConf']['template_vars_mode'] = 'explicit';
        
        $GLOBALS['sensitive_db_password'] = 'secret123';
        $GLOBALS['api_key'] = 'key456';
        
        // 显式传递变量
        $explicitVars = array(
            'user' => array('name' => '用户')
        );
        
        $vars = Zandy_Template::getTemplateVars($explicitVars);
        
        $this->assert(isset($vars['user']), 'explicit 模式：可以访问显式传递的变量');
        $this->assert(!isset($vars['sensitive_db_password']), 'explicit 模式：不能访问敏感变量');
        $this->assert(!isset($vars['api_key']), 'explicit 模式：不能访问敏感变量');
        
        unset($GLOBALS['sensitive_db_password'], $GLOBALS['api_key']);
        unset($GLOBALS['siteConf']['template_vars_mode']);
    }
    
    /**
     * 测试 outString() 的变量传递
     */
    public function testOutStringVariablePassing() {
        $GLOBALS['global_var'] = '全局变量';
        
        // 显式传递变量
        $explicitVars = array(
            'local_var' => '局部变量'
        );
        
        $html = Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir, false, $explicitVars);
        
        // 验证模板可以访问显式传递的变量
        $this->assert(!empty($html), 'outString() 显式传递变量');
        
        unset($GLOBALS['global_var']);
    }
    
    /**
     * 测试 includeTemplate() 的变量传递
     */
    public function testIncludeTemplateVariablePassing() {
        $GLOBALS['global_var'] = '全局变量';
        
        // 显式传递变量
        $explicitVars = array(
            'local_var' => '局部变量'
        );
        
        ob_start();
        Zandy_Template::includeTemplate('basic.htm', $this->tplDir, $this->cacheDir, false, $explicitVars);
        $output = ob_get_clean();
        
        $this->assert(!empty($output), 'includeTemplate() 显式传递变量');
        
        unset($GLOBALS['global_var']);
    }
    
    /**
     * 断言方法
     */
    private function assert($condition, $message) {
        if ($condition) {
            $this->passed++;
            echo "✓ PASS: $message\n";
        } else {
            $this->failed++;
            echo "✗ FAIL: $message\n";
        }
    }
    
    /**
     * 清理测试文件
     */
    private function cleanup() {
        if (file_exists($this->cacheDir)) {
            $this->rmdir($this->cacheDir);
        }
    }
    
    /**
     * 清理全局变量
     */
    private function cleanupGlobals() {
        unset($GLOBALS['siteConf']['template_vars_mode']);
        unset($GLOBALS['siteConf']['template_vars_whitelist']);
    }
    
    /**
     * 递归删除目录
     */
    private function rmdir($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->rmdir($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    /**
     * 运行所有测试
     */
    public function runAll() {
        echo "========================================\n";
        echo "Zandy_Template 变量访问控制单元测试\n";
        echo "========================================\n\n";
        
        $this->testOpenMode();
        $this->testWhitelistMode();
        $this->testWhitelistModeEmpty();
        $this->testExplicitMode();
        $this->testExplicitVarsPriority();
        $this->testVariableLeakProtectionOpen();
        $this->testVariableLeakProtectionWhitelist();
        $this->testVariableLeakProtectionExplicit();
        $this->testOutStringVariablePassing();
        $this->testIncludeTemplateVariablePassing();
        
        echo "\n========================================\n";
        echo "测试结果汇总\n";
        echo "========================================\n";
        echo "通过: {$this->passed}\n";
        echo "失败: {$this->failed}\n";
        echo "总计: " . ($this->passed + $this->failed) . "\n";
        
        if ($this->failed === 0) {
            echo "\n✓ 所有测试通过！\n";
            return true;
        } else {
            echo "\n✗ 有测试失败\n";
            return false;
        }
    }
}

// 运行测试
if (php_sapi_name() === 'cli') {
    $test = new VariableAccessControlTest();
    $result = $test->runAll();
    // 恢复 error_reporting
    error_reporting($oldErrorReporting);
    exit($result ? 0 : 1);
}
