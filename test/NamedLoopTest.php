<?php
/**
 * 命名循环功能单元测试
 */

require_once __DIR__ . '/../Template.php';

class NamedLoopTest {
    private $tplDir;
    private $cacheDir;
    
    public function __construct() {
        $this->tplDir = __DIR__ . '/../examples/templates/';
        $this->cacheDir = __DIR__ . '/../examples/cacheztec/';
    }
    
    /**
     * 测试1: 不指定 name（向后兼容）
     */
    public function testWithoutName() {
        $items = array('苹果', '香蕉', '橙子');
        $GLOBALS['items'] = $items;
        
        $html = Zandy_Template::outString('named_loop_test1.htm', $this->tplDir, $this->cacheDir);
        
        // 验证：应该正常输出，不包含循环信息变量
        assert(strpos($html, '苹果') !== false, '应该包含"苹果"');
        assert(strpos($html, '香蕉') !== false, '应该包含"香蕉"');
        assert(strpos($html, '橙子') !== false, '应该包含"橙子"');
        
        unset($GLOBALS['items']);
        echo "✓ 测试1通过：不指定 name（向后兼容）\n";
    }
    
    /**
     * 测试2: 指定 name（单层循环）
     */
    public function testWithName() {
        $items = array('苹果', '香蕉', '橙子');
        $GLOBALS['items'] = $items;
        
        $html = Zandy_Template::outString('named_loop_test2.htm', $this->tplDir, $this->cacheDir);
        
        // 验证：应该包含循环信息
        assert(strpos($html, '[0]') !== false, '应该包含索引0');
        assert(strpos($html, '[1]') !== false, '应该包含索引1');
        assert(strpos($html, '[2]') !== false, '应该包含索引2');
        assert(strpos($html, '(第一个)') !== false, '应该包含"第一个"');
        assert(strpos($html, '(最后一个)') !== false, '应该包含"最后一个"');
        assert(strpos($html, '迭代: 1') !== false, '应该包含迭代信息');
        assert(strpos($html, '总数: 3') !== false, '应该包含总数信息');
        
        unset($GLOBALS['items']);
        echo "✓ 测试2通过：指定 name（单层循环）\n";
    }
    
    /**
     * 测试3: 嵌套循环（都指定 name）
     */
    public function testNestedLoops() {
        $users = array(
            array('name' => 'Alice', 'posts' => array('Post 1', 'Post 2')),
            array('name' => 'Bob', 'posts' => array('Post 1')),
            array('name' => 'Charlie', 'posts' => array('Post 1', 'Post 2', 'Post 3')),
        );
        $GLOBALS['users'] = $users;
        
        $html = Zandy_Template::outString('named_loop_test3.htm', $this->tplDir, $this->cacheDir);
        
        // 验证：应该包含嵌套循环信息
        assert(strpos($html, '用户 [0]') !== false, '应该包含用户索引0');
        assert(strpos($html, '用户 [1]') !== false, '应该包含用户索引1');
        assert(strpos($html, '用户 [2]') !== false, '应该包含用户索引2');
        assert(strpos($html, '文章索引: 0') !== false, '应该包含文章索引0');
        assert(strpos($html, '文章索引: 1') !== false, '应该包含文章索引1');
        assert(strpos($html, '文章索引: 2') !== false, '应该包含文章索引2');
        
        unset($GLOBALS['users']);
        echo "✓ 测试3通过：嵌套循环（都指定 name）\n";
    }
    
    /**
     * 测试4: 混合（外层命名，内层不命名）
     */
    public function testMixedLoops() {
        $users = array(
            array('name' => 'Alice', 'posts' => array('Post 1', 'Post 2')),
            array('name' => 'Bob', 'posts' => array('Post 1')),
        );
        $GLOBALS['users'] = $users;
        
        $html = Zandy_Template::outString('named_loop_test5.htm', $this->tplDir, $this->cacheDir);
        
        // 验证：外层应该有循环信息，内层没有
        assert(strpos($html, '用户 [0]') !== false, '应该包含用户索引0');
        assert(strpos($html, '用户 [1]') !== false, '应该包含用户索引1');
        assert(strpos($html, 'Post 1') !== false, '应该包含文章内容');
        
        unset($GLOBALS['users']);
        echo "✓ 测试4通过：混合（外层命名，内层不命名）\n";
    }
    
    /**
     * 测试5: 语法检查
     */
    public function testSyntaxCheck() {
        $cacheFiles = glob($this->cacheDir . '**/*.php');
        $errors = array();
        
        foreach ($cacheFiles as $file) {
            $output = array();
            $return = 0;
            exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
            if ($return !== 0) {
                $errors[] = $file . ': ' . implode("\n", $output);
            }
        }
        
        if (count($errors) > 0) {
            echo "✗ 测试5失败：发现语法错误\n";
            foreach ($errors as $error) {
                echo "  " . $error . "\n";
            }
            return false;
        }
        
        echo "✓ 测试5通过：所有编译后的文件语法正确\n";
        return true;
    }
    
    /**
     * 运行所有测试
     */
    public function runAll() {
        echo "开始运行命名循环功能单元测试...\n\n";
        
        try {
            $this->testWithoutName();
            $this->testWithName();
            $this->testNestedLoops();
            $this->testMixedLoops();
            $this->testSyntaxCheck();
            
            echo "\n所有测试通过！\n";
            return true;
        } catch (Exception $e) {
            echo "\n测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// 运行测试
if (php_sapi_name() === 'cli') {
    $test = new NamedLoopTest();
    $test->runAll();
}
