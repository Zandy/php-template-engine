<?php
/**
 * API 方法单元测试
 * 
 * 测试模板引擎的所有 API 方法：
 * - outString() - 返回 HTML 字符串
 * - outCache() - 返回缓存文件路径
 * - includeTemplate() - 安全地 include 模板文件
 * - getTemplateVars() - 获取模板变量
 * - out() - 通用输出方法
 * - outHTML() - 返回 HTML 文件路径或内容
 * - outEval() - 返回可 eval 的字符串
 */

require_once __DIR__ . '/../Template.php';

// 抑制预期的 PHP Warning（未定义变量是测试的一部分）
// 这些警告不影响测试结果，但会让输出更清晰
$oldErrorReporting = error_reporting(E_ALL & ~E_WARNING);

class ApiMethodsTest {
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
    }
    
    /**
     * 测试 outString() - 基本用法
     */
    public function testOutStringBasic() {
        $GLOBALS['test_var'] = '测试变量';
        
        $html = Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir);
        
        $this->assert(!empty($html) && is_string($html), 'outString() 基本用法');
        unset($GLOBALS['test_var']);
    }
    
    /**
     * 测试 outString() - 显式传递变量
     */
    public function testOutStringWithVars() {
        $vars = array(
            'user' => array('name' => '张三', 'age' => 25),
            'data' => array('title' => '测试标题')
        );
        
        $html = Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir, false, $vars);
        
        $this->assert(!empty($html) && is_string($html), 'outString() 显式传递变量');
    }
    
    /**
     * 测试 outString() - 强制刷新缓存
     */
    public function testOutStringForceRefresh() {
        $GLOBALS['test_var'] = '第一次';
        $html1 = Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir);
        
        $GLOBALS['test_var'] = '第二次';
        $html2 = Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir, true);
        
        $this->assert(!empty($html2), 'outString() 强制刷新缓存');
        unset($GLOBALS['test_var']);
    }
    
    /**
     * 测试 outCache() - 基本用法
     */
    public function testOutCacheBasic() {
        $cacheFile = Zandy_Template::outCache('basic.htm', $this->tplDir, $this->cacheDir);
        
        $this->assert(!empty($cacheFile) && file_exists($cacheFile), 'outCache() 基本用法');
        $this->assert(strpos($cacheFile, '.php') !== false, 'outCache() 返回 PHP 文件');
    }
    
    /**
     * 测试 outCache() - 缓存文件存在性
     */
    public function testOutCacheFileExists() {
        $cacheFile1 = Zandy_Template::outCache('basic.htm', $this->tplDir, $this->cacheDir);
        $cacheFile2 = Zandy_Template::outCache('basic.htm', $this->tplDir, $this->cacheDir);
        
        $this->assert($cacheFile1 === $cacheFile2, 'outCache() 缓存文件一致性');
    }
    
    /**
     * 测试 includeTemplate() - 基本用法
     */
    public function testIncludeTemplateBasic() {
        $GLOBALS['test_var'] = 'includeTemplate 测试';
        
        ob_start();
        Zandy_Template::includeTemplate('basic.htm', $this->tplDir, $this->cacheDir);
        $output = ob_get_clean();
        
        $this->assert(!empty($output), 'includeTemplate() 基本用法');
        unset($GLOBALS['test_var']);
    }
    
    /**
     * 测试 includeTemplate() - 显式传递变量
     */
    public function testIncludeTemplateWithVars() {
        $vars = array(
            'user' => array('name' => '李四', 'age' => 30)
        );
        
        ob_start();
        Zandy_Template::includeTemplate('basic.htm', $this->tplDir, $this->cacheDir, false, $vars);
        $output = ob_get_clean();
        
        $this->assert(!empty($output), 'includeTemplate() 显式传递变量');
    }
    
    /**
     * 测试 getTemplateVars() - 完全开放模式
     */
    public function testGetTemplateVarsOpenMode() {
        $GLOBALS['test_var1'] = '变量1';
        $GLOBALS['test_var2'] = '变量2';
        
        $vars = Zandy_Template::getTemplateVars();
        
        $this->assert(is_array($vars), 'getTemplateVars() 返回数组');
        $this->assert(isset($vars['test_var1']), 'getTemplateVars() open 模式包含全局变量');
        $this->assert(isset($vars['test_var2']), 'getTemplateVars() open 模式包含全局变量');
        
        unset($GLOBALS['test_var1'], $GLOBALS['test_var2']);
    }
    
    /**
     * 测试 getTemplateVars() - 显式传递变量
     */
    public function testGetTemplateVarsExplicit() {
        $explicitVars = array(
            'user' => array('name' => '王五'),
            'data' => array('title' => '测试')
        );
        
        $vars = Zandy_Template::getTemplateVars($explicitVars);
        
        $this->assert(is_array($vars), 'getTemplateVars() 显式传递返回数组');
        $this->assert(isset($vars['user']), 'getTemplateVars() 显式传递包含传递的变量');
        $this->assert(isset($vars['data']), 'getTemplateVars() 显式传递包含传递的变量');
        $this->assert(!isset($vars['test_var1']), 'getTemplateVars() 显式传递不包含全局变量');
    }
    
    /**
     * 测试 out() - PHPC 模式
     */
    public function testOutPhpcMode() {
        $result = Zandy_Template::out('basic.htm', $this->tplDir, $this->cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_PHPC);
        
        $this->assert(!empty($result) && file_exists($result), 'out() PHPC 模式');
        $this->assert(strpos($result, '.php') !== false, 'out() PHPC 模式返回 PHP 文件');
    }
    
    /**
     * 测试 out() - HTML 模式
     */
    public function testOutHtmlMode() {
        $GLOBALS['test_var'] = 'HTML 模式测试';
        
        $result = Zandy_Template::out('basic.htm', $this->tplDir, $this->cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML);
        
        $this->assert(!empty($result), 'out() HTML 模式');
        unset($GLOBALS['test_var']);
    }
    
    /**
     * 测试 out() - EVAL 模式
     */
    public function testOutEvalMode() {
        $result = Zandy_Template::out('basic.htm', $this->tplDir, $this->cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_EVAL);
        
        $this->assert(!empty($result) && is_string($result), 'out() EVAL 模式');
        $this->assert(strpos($result, '<?php') !== false || strpos($result, 'echo') !== false, 'out() EVAL 模式返回 PHP 代码');
    }
    
    /**
     * 测试 outHTML() - 返回文件路径
     */
    public function testOutHtmlFile() {
        $GLOBALS['test_var'] = 'outHTML 测试';
        
        $result = Zandy_Template::outHTML('basic.htm', $this->tplDir, $this->cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML);
        
        $this->assert(!empty($result), 'outHTML() 返回文件路径');
        unset($GLOBALS['test_var']);
    }
    
    /**
     * 测试 outHTML() - 返回内容
     */
    public function testOutHtmlContents() {
        $GLOBALS['test_var'] = 'outHTML 内容测试';
        
        $result = Zandy_Template::outHTML('basic.htm', $this->tplDir, $this->cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS);
        
        $this->assert(!empty($result) && is_string($result), 'outHTML() 返回内容');
        unset($GLOBALS['test_var']);
    }
    
    /**
     * 测试 outHTML() - 显式传递变量
     */
    public function testOutHtmlWithVars() {
        $vars = array(
            'user' => array('name' => '赵六')
        );
        
        $result = Zandy_Template::outHTML('basic.htm', $this->tplDir, $this->cacheDir, false, ZANDY_TEMPLATE_CACHE_MOD_HTML_CONTENTS, $vars);
        
        $this->assert(!empty($result) && is_string($result), 'outHTML() 显式传递变量');
    }
    
    /**
     * 测试 outEval() - 基本用法
     */
    public function testOutEvalBasic() {
        $code = Zandy_Template::outEval('basic.htm', $this->tplDir);
        
        $this->assert(!empty($code) && is_string($code), 'outEval() 基本用法');
        $this->assert(strpos($code, '<?php') !== false || strpos($code, 'echo') !== false, 'outEval() 返回 PHP 代码');
    }
    
    /**
     * 测试 outEval() - 执行结果
     */
    public function testOutEvalExecute() {
        $GLOBALS['test_var'] = 'outEval 执行测试';
        
        $code = Zandy_Template::outEval('basic.htm', $this->tplDir);
        
        ob_start();
        eval($code);
        $output = ob_get_clean();
        
        $this->assert(!empty($output), 'outEval() 执行结果');
        unset($GLOBALS['test_var']);
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
        echo "Zandy_Template API 方法单元测试\n";
        echo "========================================\n\n";
        
        $this->testOutStringBasic();
        $this->testOutStringWithVars();
        $this->testOutStringForceRefresh();
        $this->testOutCacheBasic();
        $this->testOutCacheFileExists();
        $this->testIncludeTemplateBasic();
        $this->testIncludeTemplateWithVars();
        $this->testGetTemplateVarsOpenMode();
        $this->testGetTemplateVarsExplicit();
        $this->testOutPhpcMode();
        $this->testOutHtmlMode();
        $this->testOutEvalMode();
        $this->testOutHtmlFile();
        $this->testOutHtmlContents();
        $this->testOutHtmlWithVars();
        $this->testOutEvalBasic();
        $this->testOutEvalExecute();
        
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
    $test = new ApiMethodsTest();
    $result = $test->runAll();
    // 恢复 error_reporting
    error_reporting($oldErrorReporting);
    exit($result ? 0 : 1);
}
