<?php
/**
 * 性能测试
 * 
 * 测试模板引擎的性能：
 * - 编译性能
 * - 运行时性能
 * - 内存使用
 * 
 * 注意：性能测试结果会因环境而异，主要用于相对比较
 */

require_once __DIR__ . '/../Template.php';

// 抑制预期的 PHP Warning（未定义变量是测试的一部分）
// 这些警告不影响测试结果，但会让输出更清晰
$oldErrorReporting = error_reporting(E_ALL & ~E_WARNING);

class PerformanceTest {
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
     * 测试编译性能
     */
    public function testCompilePerformance() {
        $template = str_repeat('<p>测试内容 {$var}</p>' . "\n", 100);
        $template .= '<!--{loop $items as $item}--><li>{$item}</li><!--{/loop}-->';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'zte_perf_') . '.htm';
        file_put_contents($tempFile, $template);
        
        $tplDir = dirname($tempFile) . '/';
        
        $start = microtime(true);
        $code = Zandy_Template::outEval(basename($tempFile), $tplDir);
        $end = microtime(true);
        
        $compileTime = ($end - $start) * 1000; // 转换为毫秒
        
        $this->assert(!empty($code), '编译性能：能正常编译');
        $this->assert($compileTime < 1000, '编译性能：编译时间小于 1000ms（实际: ' . round($compileTime, 2) . 'ms）');
        
        echo "  编译时间: " . round($compileTime, 2) . "ms\n";
        
        unlink($tempFile);
    }
    
    /**
     * 测试运行时性能
     */
    public function testRuntimePerformance() {
        $GLOBALS['items'] = array();
        for ($i = 0; $i < 1000; $i++) {
            $GLOBALS['items'][] = '项目' . $i;
        }
        
        $template = '<!--{loop $items as $item}--><li>{$item}</li><!--{/loop}-->';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'zte_perf_') . '.htm';
        file_put_contents($tempFile, $template);
        
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
        
        // 首次编译
        $cacheFile = Zandy_Template::outCache(basename($tempFile), $tplDir, $cacheDir);
        
        // 测试运行时性能（使用缓存）
        $iterations = 100;
        $start = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            ob_start();
            extract(Zandy_Template::getTemplateVars());
            include $cacheFile;
            ob_end_clean();
        }
        
        $end = microtime(true);
        $runtimeTime = (($end - $start) / $iterations) * 1000; // 每次运行的平均时间（毫秒）
        
        $this->assert($runtimeTime < 10, '运行时性能：单次运行时间小于 10ms（实际: ' . round($runtimeTime, 2) . 'ms）');
        
        echo "  平均运行时间: " . round($runtimeTime, 2) . "ms/次\n";
        
        unlink($tempFile);
        if (file_exists($cacheDir)) {
            $this->rmdir($cacheDir);
        }
        unset($GLOBALS['items']);
    }
    
    /**
     * 测试内存使用
     */
    public function testMemoryUsage() {
        $largeArray = array();
        for ($i = 0; $i < 10000; $i++) {
            $largeArray[] = '项目' . $i;
        }
        
        $GLOBALS['large_array'] = $largeArray;
        
        $template = '<!--{loop $large_array as $item}--><li>{$item}</li><!--{/loop}-->';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'zte_perf_') . '.htm';
        file_put_contents($tempFile, $template);
        
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
        
        $memoryBefore = memory_get_usage();
        
        $html = Zandy_Template::outString(basename($tempFile), $tplDir, $cacheDir);
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // 转换为 MB
        
        $this->assert(!empty($html), '内存使用：能正常处理大数组');
        $this->assert($memoryUsed < 50, '内存使用：内存增长小于 50MB（实际: ' . round($memoryUsed, 2) . 'MB）');
        
        echo "  内存使用: " . round($memoryUsed, 2) . "MB\n";
        
        unlink($tempFile);
        if (file_exists($cacheDir)) {
            $this->rmdir($cacheDir);
        }
        unset($GLOBALS['large_array']);
    }
    
    /**
     * 测试缓存效果
     */
    public function testCacheEffectiveness() {
        $template = '<p>测试内容 {$var}</p>';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'zte_perf_') . '.htm';
        file_put_contents($tempFile, $template);
        
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
        $GLOBALS['var'] = '测试';
        
        // 首次编译（包含编译时间）
        $start1 = microtime(true);
        $html1 = Zandy_Template::outString(basename($tempFile), $tplDir, $cacheDir);
        $time1 = (microtime(true) - $start1) * 1000;
        
        // 第二次使用缓存（不包含编译时间）
        $start2 = microtime(true);
        $html2 = Zandy_Template::outString(basename($tempFile), $tplDir, $cacheDir);
        $time2 = (microtime(true) - $start2) * 1000;
        
        $this->assert(!empty($html1) && !empty($html2), '缓存效果：两次都能正常输出');
        $this->assert($time2 < $time1, '缓存效果：使用缓存比首次编译快（首次: ' . round($time1, 2) . 'ms, 缓存: ' . round($time2, 2) . 'ms）');
        
        echo "  首次编译: " . round($time1, 2) . "ms\n";
        echo "  使用缓存: " . round($time2, 2) . "ms\n";
        echo "  性能提升: " . round(($time1 - $time2) / $time1 * 100, 1) . "%\n";
        
        unlink($tempFile);
        if (file_exists($cacheDir)) {
            $this->rmdir($cacheDir);
        }
        unset($GLOBALS['var']);
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
        echo "Zandy_Template 性能测试\n";
        echo "========================================\n";
        echo "注意：性能测试结果会因环境而异，主要用于相对比较\n\n";
        
        $this->testCompilePerformance();
        $this->testRuntimePerformance();
        $this->testMemoryUsage();
        $this->testCacheEffectiveness();
        
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
    $test = new PerformanceTest();
    $result = $test->runAll();
    // 恢复 error_reporting
    error_reporting($oldErrorReporting);
    exit($result ? 0 : 1);
}
