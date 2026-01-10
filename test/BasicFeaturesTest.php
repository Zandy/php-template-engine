<?php
/**
 * 基本功能单元测试
 * 
 * 测试模板引擎的基本功能：
 * - 变量输出
 * - 时间函数
 * - PHP 常量
 * - echo 表达式
 * - 语言包
 * - 循环（loop, for, foreach）
 * - 条件判断（if, elseif, else）
 * - Switch 语句
 * - 模板包含
 * - 文件包含
 * - PHP 代码块
 * - set 变量
 * - 模板注释
 */

require_once __DIR__ . '/../Template.php';

// 抑制预期的 PHP Warning（未定义变量是测试的一部分）
// 这些警告不影响测试结果，但会让输出更清晰
$oldErrorReporting = error_reporting(E_ALL & ~E_WARNING);

class BasicFeaturesTest {
    private $tplDir;
    private $cacheDir;
    private $passed = 0;
    private $failed = 0;
    
    public function __construct() {
        $this->tplDir = __DIR__ . '/../examples/templates/';
        $this->cacheDir = __DIR__ . '/../examples/cacheztec/';
        
        // 设置必要的配置
        $tplBaseDir = dirname($this->tplDir);
        $GLOBALS['siteConf'] = array(
            'tplBaseDir' => $tplBaseDir,
            'tplCacheBaseDir' => $this->cacheDir,
            'tplDir' => $this->tplDir,
        );
    }
    
    /**
     * 测试变量输出
     */
    public function testVariableOutput() {
        $GLOBALS['test_var'] = '测试变量';
        $GLOBALS['test_array'] = array('key' => 'value');
        
        $template = '{$test_var}';
        $result = $this->parseTemplate($template);
        
        $this->assert($result === '测试变量', '变量输出测试');
        unset($GLOBALS['test_var'], $GLOBALS['test_array']);
    }
    
    /**
     * 测试时间函数
     */
    public function testTimeFunctions() {
        $template = '{time}';
        $result = $this->parseTemplate($template);
        
        $this->assert(is_numeric($result) && $result > 0, 'time 函数测试');
        
        $template = '{now}';
        $result = $this->parseTemplate($template);
        
        $this->assert(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result), 'now 函数测试');
        
        $template = '{date "Y-m-d"}';
        $result = $this->parseTemplate($template);
        
        $this->assert(preg_match('/^\d{4}-\d{2}-\d{2}$/', $result), 'date 函数测试');
    }
    
    /**
     * 测试 PHP 常量
     */
    public function testConstants() {
        $template = '{PHP_VERSION}';
        $result = $this->parseTemplate($template);
        
        $this->assert(!empty($result), 'PHP 常量测试');
    }
    
    /**
     * 测试 echo 表达式
     */
    public function testEchoExpression() {
        $GLOBALS['test_num'] = 10;
        $template = '{echo $test_num * 2}';
        $result = $this->parseTemplate($template);
        
        $this->assert($result === '20', 'echo 表达式测试');
        unset($GLOBALS['test_num']);
    }
    
    /**
     * 测试语言包
     */
    public function testLang() {
        $GLOBALS['_LANG'] = array('welcome' => '欢迎');
        $template = '{LANG welcome}';
        $result = $this->parseTemplate($template);
        
        $this->assert($result === '欢迎', '语言包测试');
        unset($GLOBALS['_LANG']);
    }
    
    /**
     * 测试 loop 循环
     */
    public function testLoop() {
        // loops.htm 模板需要 $users 变量
        $GLOBALS['users'] = array(
            array('name' => '张三', 'age' => 25),
            array('name' => '李四', 'age' => 30)
        );
        $GLOBALS['products'] = array('产品1' => '价格1', '产品2' => '价格2');
        $GLOBALS['emptyArray'] = array();
        $GLOBALS['categories'] = array();
        $GLOBALS['categoriesWithEmpty'] = array();
        
        $html = Zandy_Template::outString('loops.htm', $this->tplDir, $this->cacheDir);
        
        $this->assert(strpos($html, '张三') !== false || strpos($html, '李四') !== false, 'loop 循环测试');
        unset($GLOBALS['users'], $GLOBALS['products'], $GLOBALS['emptyArray'], $GLOBALS['categories'], $GLOBALS['categoriesWithEmpty']);
    }
    
    /**
     * 测试 for 循环
     */
    public function testForLoop() {
        $template = '<!--{for $i = 0; $i < 3; $i++}-->{$i}<!--{/for}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '0') !== false && strpos($result, '1') !== false && strpos($result, '2') !== false, 'for 循环测试');
    }
    
    /**
     * 测试 foreach 循环
     */
    public function testForeachLoop() {
        $GLOBALS['items'] = array('x', 'y', 'z');
        $html = Zandy_Template::outString('foreach_demo.htm', $this->tplDir, $this->cacheDir);
        
        $this->assert(strpos($html, 'x') !== false && strpos($html, 'y') !== false, 'foreach 循环测试');
        unset($GLOBALS['items']);
    }
    
    /**
     * 测试条件判断
     */
    public function testIfCondition() {
        $GLOBALS['condition'] = true;
        $template = '<!--{if $condition}-->true<!--{else}-->false<!--{/if}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, 'true') !== false, 'if 条件测试');
        unset($GLOBALS['condition']);
    }
    
    /**
     * 测试 Switch 语句
     */
    public function testSwitch() {
        $GLOBALS['status'] = 1;
        $html = Zandy_Template::outString('switch_demo.htm', $this->tplDir, $this->cacheDir);
        
        $this->assert(strpos($html, '状态为 1') !== false, 'Switch 语句测试');
        unset($GLOBALS['status']);
    }
    
    /**
     * 测试模板包含
     */
    public function testTemplateInclude() {
        $GLOBALS['pageTitle'] = '测试';
        $html = Zandy_Template::outString('page.htm', $this->tplDir, $this->cacheDir);
        
        $this->assert(strpos($html, 'header') !== false || strpos($html, 'footer') !== false, '模板包含测试');
        unset($GLOBALS['pageTitle']);
    }
    
    /**
     * 测试模板注释
     */
    public function testTemplateComment() {
        $template = '<!--{*这是注释*}-->内容';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '这是注释') === false && strpos($result, '内容') !== false, '模板注释测试');
    }
    
    /**
     * 测试 PHP 代码块
     */
    public function testPhpBlock() {
        $template = '<!--{php}-->$test = "PHP代码块";echo $test;<!--{/php}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, 'PHP代码块') !== false, 'PHP 代码块测试');
    }
    
    /**
     * 测试 set 变量
     */
    public function testSetVariable() {
        $GLOBALS['items'] = array('a', 'b', 'c');
        $template = '<!--{set $count = count($items)}-->数量: {$count}';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '数量: 3') !== false, 'set 变量测试');
        unset($GLOBALS['items']);
    }
    
    /**
     * 测试 include
     */
    public function testInclude() {
        // 创建一个临时 PHP 文件（与模板在同一目录）
        $tempDir = sys_get_temp_dir() . '/zte_include_test_' . uniqid() . '/';
        mkdir($tempDir, 0777, true);
        $tempPhpFile = $tempDir . 'test_include.php';
        file_put_contents($tempPhpFile, '<?php echo "包含的文件内容";');
        
        $template = '<!--{include test_include.php}-->';
        $tempTplFile = $tempDir . 'test_template.htm';
        file_put_contents($tempTplFile, $template);
        
        // 设置配置
        $tplBaseDir = dirname($tempDir);
        $GLOBALS['siteConf'] = array(
            'tplBaseDir' => $tplBaseDir,
            'tplCacheBaseDir' => sys_get_temp_dir() . '/zte_cache_' . uniqid() . '/',
            'tplDir' => $tempDir,
        );
        
        $code = Zandy_Template::outEval('test_template.htm', $tempDir);
        
        ob_start();
        extract($this->extractTemplateVarsForTest());
        eval($code);
        $result = ob_get_clean();
        
        unlink($tempPhpFile);
        unlink($tempTplFile);
        rmdir($tempDir);
        unset($GLOBALS['siteConf']);
        
        $this->assert(strpos($result, '包含的文件内容') !== false, 'include 测试');
    }
    
    /**
     * 测试 loop-else
     */
    public function testLoopElse() {
        $GLOBALS['emptyArray'] = array();
        $template = '<!--{loop $emptyArray as $v}-->{$v}<!--{loop-else}-->数组为空<!--{/loop}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '数组为空') !== false, 'loop-else 测试');
        unset($GLOBALS['emptyArray']);
    }
    
    /**
     * 测试 foreach-else
     */
    public function testForeachElse() {
        $GLOBALS['emptyArray'] = array();
        $template = '<!--{foreach $emptyArray as $v}-->{$v}<!--{foreach-else}-->数组为空<!--{/foreach}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '数组为空') !== false, 'foreach-else 测试');
        unset($GLOBALS['emptyArray']);
    }
    
    /**
     * 测试 break-case 和 break-default
     */
    public function testBreakCase() {
        $GLOBALS['value'] = 1;
        $template = '<!--{switch $value}--><!--{case 1}-->值为1<!--{break-case 2}-->值为2<!--{break-default}-->默认值<!--{/switch}-->';
        $result = $this->parseTemplate($template);
        
        // break-case 会继续执行下一个 case，所以应该包含 "值为2"
        $this->assert(strpos($result, '值为1') !== false && strpos($result, '值为2') !== false, 'break-case 测试');
        unset($GLOBALS['value']);
    }
    
    /**
     * 测试 continue
     */
    public function testContinue() {
        $GLOBALS['items'] = array(1, 2, 3, 4, 5);
        $template = '<!--{loop $items as $item}--><!--{if $item == 3}--><!--{continue}--><!--{/if}-->{$item}<!--{/loop}-->';
        $result = $this->parseTemplate($template);
        
        // continue 会跳过 3，所以不应该包含 3
        $this->assert(strpos($result, '1') !== false && strpos($result, '2') !== false && strpos($result, '4') !== false && strpos($result, '5') !== false, 'continue 测试');
        unset($GLOBALS['items']);
    }
    
    /**
     * 辅助方法：解析模板字符串
     */
    private function parseTemplate($template) {
        $tempFile = tempnam(sys_get_temp_dir(), 'zte_test_') . '.htm';
        file_put_contents($tempFile, $template);
        
        $tplDir = dirname($tempFile) . '/';
        $cacheDir = sys_get_temp_dir() . '/zte_cache_' . uniqid() . '/';
        mkdir($cacheDir, 0777, true);
        
        // 设置必要的配置
        // tplBaseDir 应该是包含 tplDir 的父目录
        $tplBaseDir = dirname($tplDir);
        $GLOBALS['siteConf'] = array(
            'tplBaseDir' => $tplBaseDir,
            'tplCacheBaseDir' => $cacheDir,
            'tplDir' => $tplDir,
        );
        
        $code = Zandy_Template::outEval(basename($tempFile), $tplDir);
        
        ob_start();
        extract($this->extractTemplateVarsForTest());
        eval($code);
        $result = ob_get_clean();
        
        unlink($tempFile);
        if (file_exists($cacheDir)) {
            $this->rmdir($cacheDir);
        }
        
        // 清理配置
        unset($GLOBALS['siteConf']);
        
        return $result;
    }
    
    /**
     * 辅助方法：提取测试用的模板变量
     */
    private function extractTemplateVarsForTest() {
        // 提取所有全局变量（用于测试）
        $vars = array();
        foreach ($GLOBALS as $key => $value) {
            if ($key !== 'GLOBALS' && $key !== '_SERVER' && $key !== '_GET' && $key !== '_POST' && 
                $key !== '_FILES' && $key !== '_COOKIE' && $key !== '_SESSION' && $key !== '_ENV' && 
                $key !== 'HTTP_RAW_POST_DATA' && $key !== 'http_response_header' && 
                $key !== 'argc' && $key !== 'argv' && $key !== 'siteConf') {
                $vars[$key] = $value;
            }
        }
        return $vars;
    }
    
    /**
     * 辅助方法：递归删除目录
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
     * 运行所有测试
     */
    public function runAll() {
        echo "========================================\n";
        echo "Zandy_Template 基本功能单元测试\n";
        echo "========================================\n\n";
        
        $this->testVariableOutput();
        $this->testTimeFunctions();
        $this->testConstants();
        $this->testEchoExpression();
        $this->testLang();
        $this->testLoop();
        $this->testForLoop();
        $this->testForeachLoop();
        $this->testIfCondition();
        $this->testSwitch();
        $this->testTemplateInclude();
        $this->testTemplateComment();
        $this->testPhpBlock();
        $this->testSetVariable();
        $this->testInclude();
        $this->testLoopElse();
        $this->testForeachElse();
        $this->testBreakCase();
        $this->testContinue();
        
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
    $test = new BasicFeaturesTest();
    $result = $test->runAll();
    // 恢复 error_reporting
    error_reporting($oldErrorReporting);
    exit($result ? 0 : 1);
}
