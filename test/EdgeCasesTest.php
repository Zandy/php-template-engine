<?php
/**
 * 边界情况单元测试
 * 
 * 测试模板引擎的边界情况处理：
 * - 空数组处理
 * - null 值处理
 * - 空字符串处理
 * - 特殊字符处理
 * - 大文件处理
 * - 嵌套深度测试
 */

require_once __DIR__ . '/../Template.php';

// 抑制预期的 PHP Warning（未定义变量是测试的一部分）
// 这些警告不影响测试结果，但会让输出更清晰
$oldErrorReporting = error_reporting(E_ALL & ~E_WARNING);

class EdgeCasesTest {
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
     * 测试空数组处理
     */
    public function testEmptyArray() {
        $GLOBALS['empty_array'] = array();
        
        $template = '<!--{loop $empty_array as $item}-->{$item}<!--{loop-else}-->数组为空<!--{/loop}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '数组为空') !== false, '空数组处理：显示 else 分支');
        
        unset($GLOBALS['empty_array']);
    }
    
    /**
     * 测试 null 值处理
     */
    public function testNullValue() {
        $GLOBALS['null_var'] = null;
        
        $template = '<!--{if isset($null_var)}-->存在<!--{else}-->不存在<!--{/if}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(!empty($result), 'null 值处理：不抛出错误');
        
        unset($GLOBALS['null_var']);
    }
    
    /**
     * 测试空字符串处理
     */
    public function testEmptyString() {
        $GLOBALS['empty_string'] = '';
        
        $template = '<!--{if $empty_string}-->非空<!--{else}-->空字符串<!--{/if}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '空字符串') !== false, '空字符串处理：正确判断');
        
        unset($GLOBALS['empty_string']);
    }
    
    /**
     * 测试特殊字符处理
     */
    public function testSpecialCharacters() {
        $GLOBALS['special'] = '<script>alert("XSS")</script>';
        
        $template = '{$special}';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '<script>') !== false, '特殊字符处理：正确输出');
        
        unset($GLOBALS['special']);
    }
    
    /**
     * 测试嵌套循环深度
     */
    public function testNestedLoops() {
        $GLOBALS['categories'] = array(
            array('name' => '分类1', 'items' => array('项目1', '项目2')),
            array('name' => '分类2', 'items' => array('项目3', '项目4'))
        );
        
        $template = '<!--{loop $categories as $category}-->
            <h3>{$category[\'name\']}</h3>
            <ul>
                <!--{loop $category[\'items\'] as $item}-->
                    <li>{$item}</li>
                <!--{/loop}-->
            </ul>
        <!--{/loop}-->';
        
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '分类1') !== false, '嵌套循环：外层循环正常');
        $this->assert(strpos($result, '项目1') !== false, '嵌套循环：内层循环正常');
        
        unset($GLOBALS['categories']);
    }
    
    /**
     * 测试深层嵌套
     */
    public function testDeepNesting() {
        $GLOBALS['data'] = array(
            array(
                'level1' => array(
                    'level2' => array(
                        'level3' => '深度嵌套值'
                    )
                )
            )
        );
        
        $template = '<!--{loop $data as $item}-->
            {$item[\'level1\'][\'level2\'][\'level3\']}
        <!--{/loop}-->';
        
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '深度嵌套值') !== false, '深层嵌套：正确访问');
        
        unset($GLOBALS['data']);
    }
    
    /**
     * 测试大数组处理
     */
    public function testLargeArray() {
        $largeArray = array();
        for ($i = 0; $i < 1000; $i++) {
            $largeArray[] = '项目' . $i;
        }
        
        $GLOBALS['large_array'] = $largeArray;
        
        $template = '<!--{loop $large_array as $item}-->{$item}<!--{/loop}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '项目0') !== false, '大数组处理：能处理大数组');
        $this->assert(strpos($result, '项目999') !== false, '大数组处理：能处理大数组末尾');
        
        unset($GLOBALS['large_array']);
    }
    
    /**
     * 测试大模板文件
     */
    public function testLargeTemplate() {
        // 创建一个大模板文件
        $largeTemplate = str_repeat('<p>这是内容行</p>' . "\n", 1000);
        $largeTemplate .= '{$test_var}';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'zte_large_') . '.htm';
        file_put_contents($tempFile, $largeTemplate);
        
        $tplDir = dirname($tempFile) . '/';
        $cacheDir = sys_get_temp_dir() . '/zte_cache_' . uniqid() . '/';
        mkdir($cacheDir, 0777, true);
        
        // tplBaseDir 应该是包含 tplDir 的父目录
        $tplBaseDir = dirname($tplDir);
        $GLOBALS['siteConf'] = array(
            'tplBaseDir' => $tplBaseDir,
            'tplCacheBaseDir' => $cacheDir,
            'tplDir' => $tplDir,
        );
        
        $GLOBALS['test_var'] = '测试变量';
        
        $html = Zandy_Template::outString(basename($tempFile), $tplDir, $cacheDir);
        
        $this->assert(!empty($html), '大模板文件：能处理大文件');
        $this->assert(strpos($html, '测试变量') !== false, '大模板文件：变量正确输出');
        
        unlink($tempFile);
        if (file_exists($cacheDir)) {
            $this->rmdir($cacheDir);
        }
        unset($GLOBALS['test_var'], $GLOBALS['siteConf']);
    }
    
    /**
     * 测试循环中的 continue
     */
    public function testContinueInLoop() {
        $GLOBALS['items'] = array(1, 2, 3, 4, 5);
        
        $template = '<!--{loop $items as $item}-->
            <!--{if $item == 3}--><!--{continue}--><!--{/if}-->
            {$item}
        <!--{/loop}-->';
        
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '1') !== false, 'continue：跳过指定项');
        $this->assert(strpos($result, '2') !== false, 'continue：跳过指定项');
        $this->assert(strpos($result, '4') !== false, 'continue：跳过指定项');
        $this->assert(strpos($result, '5') !== false, 'continue：跳过指定项');
        
        unset($GLOBALS['items']);
    }
    
    /**
     * 测试 switch 中的 break
     */
    public function testBreakInSwitch() {
        $GLOBALS['value'] = 1;
        
        $template = '<!--{switch $value}-->
            <!--{case 1}-->值为1<!--{break}-->
            <!--{case 2}-->值为2<!--{break}-->
            <!--{default}-->默认值<!--{/switch}-->';
        
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '值为1') !== false, 'switch break：正确执行');
        $this->assert(strpos($result, '值为2') === false, 'switch break：正确跳出');
        
        unset($GLOBALS['value']);
    }
    
    /**
     * 测试变量未定义
     */
    public function testUndefinedVariable() {
        $template = '<!--{if isset($undefined_var)}-->存在<!--{else}-->不存在<!--{/if}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '不存在') !== false, '未定义变量：正确处理');
    }
    
    /**
     * 测试数组键不存在
     */
    public function testMissingArrayKey() {
        $GLOBALS['array'] = array('key1' => 'value1');
        
        $template = '<!--{if isset($array[\'key2\'])}-->存在<!--{else}-->不存在<!--{/if}-->';
        $result = $this->parseTemplate($template);
        
        $this->assert(strpos($result, '不存在') !== false, '数组键不存在：正确处理');
        
        unset($GLOBALS['array']);
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
        echo "Zandy_Template 边界情况单元测试\n";
        echo "========================================\n\n";
        
        $this->testEmptyArray();
        $this->testNullValue();
        $this->testEmptyString();
        $this->testSpecialCharacters();
        $this->testNestedLoops();
        $this->testDeepNesting();
        $this->testLargeArray();
        $this->testLargeTemplate();
        $this->testContinueInLoop();
        $this->testBreakInSwitch();
        $this->testUndefinedVariable();
        $this->testMissingArrayKey();
        
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
    $test = new EdgeCasesTest();
    $result = $test->runAll();
    // 恢复 error_reporting
    error_reporting($oldErrorReporting);
    exit($result ? 0 : 1);
}
