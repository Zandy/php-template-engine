<?php
/**
 * 函数和类方法内部使用示例
 * 
 * 展示在函数和类方法内部使用模板引擎的最佳实践
 */

require_once __DIR__ . '/../Template.php';

$tplDir = __DIR__ . '/templates/';
$cacheDir = __DIR__ . '/cacheztec/';

// ============================================
// 场景1：函数内部使用 outString()
// ============================================
echo "=== 场景1: 函数内部使用 outString() ===\n\n";

function renderUserProfile($userId) {
    $tplDir = __DIR__ . '/templates/';
    $cacheDir = __DIR__ . '/cacheztec/';
    
    // 获取数据（局部变量）
    $user = array(
        'id' => $userId,
        'name' => '用户' . $userId,
        'email' => 'user' . $userId . '@example.com',
    );
    $posts = array(
        array('title' => '文章1', 'content' => '内容1'),
        array('title' => '文章2', 'content' => '内容2'),
    );
    
    // 方式1：赋值到全局（不推荐，污染全局变量）
    echo "方式1：赋值到全局（不推荐）\n";
    $GLOBALS['user'] = $user;
    $GLOBALS['posts'] = $posts;
    $html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir);
    echo $html;
    echo "\n";
    
    // 清理全局变量
    unset($GLOBALS['user'], $GLOBALS['posts']);
    
    // 方式2：显式传递（推荐）
    echo "方式2：显式传递（推荐）\n";
    $html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir, false, [
        'user' => $user,
        'posts' => $posts,
    ]);
    echo $html;
    echo "\n\n";
}

renderUserProfile(123);

// ============================================
// 场景2：函数内部使用 includeTemplate()
// ============================================
echo "=== 场景2: 函数内部使用 includeTemplate() ===\n\n";

function renderPageDirect() {
    $tplDir = __DIR__ . '/templates/';
    $cacheDir = __DIR__ . '/cacheztec/';
    
    // 获取数据（局部变量）
    $title = '页面标题';
    $content = '页面内容';
    
    // 推荐：使用 includeTemplate()，显式传递变量
    echo "使用 includeTemplate()，显式传递变量（推荐）\n";
    Zandy_Template::includeTemplate('basic.htm', $tplDir, $cacheDir, false, [
        'title' => $title,
        'content' => $content,
    ]);
    echo "\n\n";
}

renderPageDirect();

// ============================================
// 场景3：函数内部使用 outCache() + getTemplateVars()
// ============================================
echo "=== 场景3: 函数内部使用 outCache() + getTemplateVars() ===\n\n";

function renderPageManual() {
    $tplDir = __DIR__ . '/templates/';
    $cacheDir = __DIR__ . '/cacheztec/';
    
    // 获取数据（局部变量）
    $title = '手动控制';
    $content = '使用 getTemplateVars() 手动控制';
    
    // 使用 getTemplateVars() 辅助函数
    echo "使用 getTemplateVars() 手动控制\n";
    $cacheFile = Zandy_Template::outCache('basic.htm', $tplDir, $cacheDir);
    extract(Zandy_Template::getTemplateVars([
        'title' => $title,
        'content' => $content,
    ]));
    include $cacheFile;
    echo "\n\n";
}

renderPageManual();

// ============================================
// 场景4：类方法内部使用
// ============================================
echo "=== 场景4: 类方法内部使用 ===\n\n";

class PageController {
    private $tplDir;
    private $cacheDir;
    
    public function __construct() {
        $this->tplDir = __DIR__ . '/templates/';
        $this->cacheDir = __DIR__ . '/cacheztec/';
    }
    
    public function render() {
        // 获取数据（局部变量）
        $user = $this->getUser();
        $data = $this->getData();
        
        // 推荐：显式传递
        echo "类方法：使用 outString()，显式传递变量（推荐）\n";
        return Zandy_Template::outString('basic.htm', $this->tplDir, $this->cacheDir, false, [
            'user' => $user,
            'data' => $data,
        ]);
    }
    
    public function renderDirect() {
        // 获取数据（局部变量）
        $user = $this->getUser();
        $data = $this->getData();
        
        // 推荐：使用 includeTemplate()
        echo "类方法：使用 includeTemplate()，显式传递变量（推荐）\n";
        Zandy_Template::includeTemplate('basic.htm', $this->tplDir, $this->cacheDir, false, [
            'user' => $user,
            'data' => $data,
        ]);
    }
    
    private function getUser() {
        return array(
            'name' => '类用户',
            'email' => 'class@example.com',
        );
    }
    
    private function getData() {
        return array(
            'title' => '类数据',
            'items' => array('项目1', '项目2'),
        );
    }
}

$controller = new PageController();
echo $controller->render();
echo "\n";
$controller->renderDirect();
echo "\n\n";

// ============================================
// 场景5：对比：面向过程 vs 函数内部
// ============================================
echo "=== 场景5: 对比：面向过程 vs 函数内部 ===\n\n";

// 面向过程使用（文件顶层）
echo "面向过程使用（文件顶层）\n";
$GLOBALS['title'] = '顶层变量';
$html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir);
echo $html;
echo "\n";

// 函数内部使用
function renderInFunction() {
    $tplDir = __DIR__ . '/templates/';
    $cacheDir = __DIR__ . '/cacheztec/';
    
    // 局部变量，不在 $GLOBALS 中
    $title = '函数内变量';
    
    // 如果使用 $GLOBALS，需要先赋值
    // $GLOBALS['title'] = $title;  // 不推荐：污染全局变量
    
    // 推荐：显式传递
    $html = Zandy_Template::outString('basic.htm', $tplDir, $cacheDir, false, [
        'title' => $title,
    ]);
    return $html;
}

echo "函数内部使用（推荐显式传递）\n";
echo renderInFunction();
echo "\n\n";

echo "=== 最佳实践总结 ===\n";
echo "1. 函数/类方法内部：强烈推荐使用显式传递变量\n";
echo "2. 避免在函数内部使用 \$GLOBALS，防止污染全局变量\n";
echo "3. 使用 includeTemplate() 替代 outCache() + include\n";
echo "4. 使用 getTemplateVars() 辅助函数进行精细控制\n";
