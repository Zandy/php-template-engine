
本模板系统属于编译型的，最大特色是变量直接是用 php 的变量，毫无学习成本；其他的运行速度快，开发效率高等等的就不吹了。

#返回填充数据后的纯html字符串。
echo Zandy_Template::outString('goods.htm', $siteConf['tplDir'], $siteConf['cacheDir']);

#相当于smarty的display，直接显示结果。
include Zandy_Template::outCache('goods.htm', $siteConf['tplDir'], $siteConf['cacheDir']);

循环用法：
<!--{loop $all $k $v}-->{$k}={$v}<!--{/loop}-->

<!--{loop array(1,2) $v}-->{$v}<!--{/loop}-->

<!--{loop array(1,2) as $v}-->{$v}<!--{/loop}-->

<!--{loop array(1,2) $k $v}-->{$v}<!--{/loop}-->

<!--{loop array(1,2) $k => $v}-->{$v}<!--{/loop}-->

<!--{loop array(1,2) as $k => $v}-->{$v}<!--{/loop}-->

<!--{loop array() as $v}-->{$v}<!--{loopelse}-->haha<!--{/loop}-->

<!--{for $i = 0; $i < 100; $i++}-->{$i}<!--{/for}-->

条件用法：
<!--{if $xxx}--> <a href="?">aaa</a><!--{elseif $yyy['xxx'] > 100}--> xxx <!--{else}--> dududu <!--{/if}-->


其他语法：
{echo xxx($aaa, $bbb);}
{echo date("Y-m-d H:i:s", time())}
{echo $a ? 'a' : 'b'}
{php} if ($a) { echo 'a'; } {/php}
<!--{set $a = "aaa"}-->
{set $a = "bbb"}

{eval var_dump($aaa)}
{eval var_dump($aaa);print_r('aaa')}
{$_LANG['title']} == {LANG title}

单纯的变量、数组、对象（标准的php里双引号、heredoc里面的变量的写法）：
{$aaa}
{$bbb['aaa']}
{$ccc['aaa']['bbb']['ccc']['ddd'][$aaa][$aa['bb']['cc']]}
{$ddd->tmp}
{$ddd->ok['ok']->ok}
{$ddd->oo['xx']->ooxx()}

直接包含 php 文件：
<!--{include xxx.php}-->
{include yyy.php}

包含模板(子模板的路径按父模板的相对路径来的)：
比如如下目录结构
common/header.htm
common/footer.htm
index/index.htm
index/index.menu.htm
css.htm
那么在 index/index.htm 里这么包含其他模板
<html><body><head>
{template ../common/header.htm}
{template ../css.htm}
</head><body>
{template index.menu.htm}
<div>ooxx</div>
{template ../common/footer.htm}
</body></html>

更多等待你发现or提需求
