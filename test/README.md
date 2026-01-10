# Zandy_Template 单元测试说明

## 概述

本测试套件包含多个测试文件，用于验证 Zandy_Template 模板引擎的各项功能：

1. **BasicFeaturesTest.php** - 基本功能测试（变量输出、循环、条件、Switch、时间函数、常量等）
2. **NamedLoopTest.php** - 命名循环功能测试
3. **CheckSyntaxTest.php** - 语法检查方法测试
4. **ApiMethodsTest.php** - API 方法测试（outString、outCache、includeTemplate 等）
5. **VariableAccessControlTest.php** - 变量访问控制测试（open/whitelist/explicit 模式）
6. **EdgeCasesTest.php** - 边界情况测试（空数组、null 值、大文件等）
7. **PerformanceTest.php** - 性能测试（编译性能、运行时性能、内存使用）

## BasicFeaturesTest.php

测试模板引擎的基本功能，包括：
- 变量输出
- 时间函数（{time}, {now}, {date}）
- PHP 常量（{CONSTANT_NAME}）
- echo 表达式（{echo ...}）
- 语言包（{LANG key}）
- 循环（loop, for, foreach）
- 循环 else 分支（loop-else, foreach-else, for-else）
- 条件判断（if, elseif, else）
- Switch 语句（switch, case, default, break-case, break-default, break, continue）
- 模板包含（template）
- 文件包含（include, include_once）
- PHP 代码块（<!--{php}-->...<!--{/php}-->）
- set 变量（<!--{set ...}-->）
- 模板注释（<!--{*...*}-->）

**运行方法：**
```bash
php test/BasicFeaturesTest.php
```

## NamedLoopTest.php

测试命名循环功能，包括：
- 不指定 name（向后兼容）
- 指定 name（单层循环）
- 嵌套循环（都指定 name）
- 混合（外层命名，内层不命名）
- 语法检查

**运行方法：**
```bash
php test/NamedLoopTest.php
```

## CheckSyntaxTest.php

本测试套件用于验证 `Zandy_Template::check_syntax()` 方法及其相关改进的正确性，确保：
1. 优先级逻辑正确（opcache > php-cli > eval）
2. 三种检查方法都能正常工作
3. 错误信息格式一致（包含编译后文件名、原模板文件名、出错行数）
4. 跨平台路径处理正确
5. PHP 5-8 兼容性

## ApiMethodsTest.php

测试模板引擎的所有 API 方法：
- `outString()` - 返回 HTML 字符串（基本用法、显式传递变量、强制刷新缓存）
- `outCache()` - 返回缓存文件路径（基本用法、缓存文件存在性）
- `includeTemplate()` - 安全地 include 模板文件（基本用法、显式传递变量）
- `getTemplateVars()` - 获取模板变量（open 模式、显式传递）
- `out()` - 通用输出方法（PHPC 模式、HTML 模式、EVAL 模式）
- `outHTML()` - 返回 HTML 文件路径或内容（返回文件路径、返回内容、显式传递变量）
- `outEval()` - 返回可 eval 的字符串（基本用法、执行结果）

**运行方法：**
```bash
php test/ApiMethodsTest.php
```

## VariableAccessControlTest.php

测试模板引擎的变量访问控制功能：
- **open 模式**（完全开放，默认）- 可以访问所有全局变量
- **whitelist 模式**（白名单）- 只允许访问白名单中的变量
- **explicit 模式**（显式传递）- 只使用显式传递的变量
- **变量泄露防护** - 验证敏感变量不会被意外访问
- **显式传递优先级** - 验证显式传递变量优先于配置模式

**运行方法：**
```bash
php test/VariableAccessControlTest.php
```

## EdgeCasesTest.php

测试模板引擎的边界情况处理：
- 空数组处理
- null 值处理
- 空字符串处理
- 特殊字符处理
- 嵌套循环深度
- 深层嵌套
- 大数组处理
- 大模板文件
- 循环中的 continue
- switch 中的 break
- 变量未定义
- 数组键不存在

**运行方法：**
```bash
php test/EdgeCasesTest.php
```

## PerformanceTest.php

测试模板引擎的性能：
- **编译性能** - 测试模板编译速度
- **运行时性能** - 测试使用缓存后的运行速度
- **内存使用** - 测试处理大数组时的内存占用
- **缓存效果** - 验证缓存机制的性能提升

**注意**：性能测试结果会因环境而异，主要用于相对比较。

**运行方法：**
```bash
php test/PerformanceTest.php
```

## 快速开始

### 本地测试
```bash
# 运行所有测试
./test/run_tests.sh

# 或单独运行
php test/BasicFeaturesTest.php
php test/NamedLoopTest.php
php test/CheckSyntaxTest.php
php test/ApiMethodsTest.php
php test/VariableAccessControlTest.php
php test/EdgeCasesTest.php
php test/PerformanceTest.php
```

### Docker 多版本测试（推荐用于兼容性测试）
```bash
# 测试所有默认版本（推荐，已自动跳过不兼容的旧版本）
./test/docker-test.sh

# 测试单个版本
./test/docker-test.sh 8.1

# 测试指定版本（支持多个）
./test/docker-test.sh 7.4 8.0 8.1
```

**注意**: PHP 5.3-5.5 使用旧镜像格式，新版本 Docker 不支持，已自动跳过。

## 测试覆盖

### 1. 优先级逻辑测试
- 验证方法选择优先级：opcache > php-cli > eval

### 2. opcache 方法测试（3个）
- ✅ 正确的 PHP 语法
- ✅ 错误的 PHP 语法
- ✅ 语法错误检测

**注意**: 如果 OPcache 扩展未启用，相关测试会被跳过。

### 3. PHP CLI 方法测试（3个）
- ✅ 正确的 PHP 语法
- ✅ 错误的 PHP 语法
- ✅ 不存在的文件处理

**注意**: 如果 `exec()` 函数不可用，相关测试会被跳过。

### 4. eval 方法测试（3个，兜底方案）
- ✅ 正确的 PHP 语法
- ✅ 错误的 PHP 语法
- ✅ 错误信息格式（包含行号）

### 5. 错误信息格式一致性测试
- 验证所有方法返回的错误信息格式一致
- 确保错误信息包含文件名和行号

### 6. 跨平台路径处理测试
- Windows: 测试 `.exe` 扩展名处理
- Unix/Linux/macOS: 测试标准路径处理

**总计**: 约 13-15 个测试用例（根据环境不同可能跳过部分测试）

### 7. API 方法测试（16个）
- ✅ outString() 基本用法
- ✅ outString() 显式传递变量
- ✅ outString() 强制刷新缓存
- ✅ outCache() 基本用法
- ✅ outCache() 缓存文件存在性
- ✅ includeTemplate() 基本用法
- ✅ includeTemplate() 显式传递变量
- ✅ getTemplateVars() open 模式
- ✅ getTemplateVars() 显式传递
- ✅ out() PHPC 模式
- ✅ out() HTML 模式
- ✅ out() EVAL 模式
- ✅ outHTML() 返回文件路径
- ✅ outHTML() 返回内容
- ✅ outHTML() 显式传递变量
- ✅ outEval() 基本用法和执行结果

### 8. 变量访问控制测试（10个）
- ✅ open 模式（完全开放）
- ✅ whitelist 模式（白名单）
- ✅ whitelist 模式（空白名单回退）
- ✅ explicit 模式（显式传递）
- ✅ 显式传递变量优先级
- ✅ 变量泄露防护 - open 模式
- ✅ 变量泄露防护 - whitelist 模式
- ✅ 变量泄露防护 - explicit 模式
- ✅ outString() 的变量传递
- ✅ includeTemplate() 的变量传递

### 9. 边界情况测试（12个）
- ✅ 空数组处理
- ✅ null 值处理
- ✅ 空字符串处理
- ✅ 特殊字符处理
- ✅ 嵌套循环深度
- ✅ 深层嵌套
- ✅ 大数组处理
- ✅ 大模板文件
- ✅ 循环中的 continue
- ✅ switch 中的 break
- ✅ 变量未定义
- ✅ 数组键不存在

### 10. 性能测试（4个）
- ✅ 编译性能
- ✅ 运行时性能
- ✅ 内存使用
- ✅ 缓存效果

**总计**: 约 70+ 个测试用例（根据环境不同可能跳过部分测试）

## Docker 测试详细说明

### 支持的 PHP 版本

**完全支持**: PHP 5.6, 7.0, 7.1, 7.2, 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4

**不支持**: PHP 5.3-5.5（旧镜像格式，新 Docker 不支持）

### 使用方法

#### 方法 1: 使用测试脚本（推荐）
```bash
# 测试所有默认版本（已自动跳过不兼容的旧版本）
./test/docker-test.sh

# 测试单个版本
./test/docker-test.sh 8.1

# 测试指定版本（支持多个）
./test/docker-test.sh 7.4 8.1 8.2
```

#### 方法 2: 使用 Docker Compose
```bash
# 测试所有版本
docker-compose up

# 测试单个版本
docker-compose run php74
```

#### 方法 3: 直接使用 Docker
```bash
docker run --rm -v $(pwd):/app -w /app php:8.1-cli php test/CheckSyntaxTest.php
```

### Docker 测试注意事项

1. **首次运行**: 会自动下载 Docker 镜像，可能需要几分钟
2. **权限问题**: 确保脚本有执行权限 `chmod +x test/*.sh`
3. **Windows 用户**: 使用 Git Bash 或 WSL 运行脚本
4. **性能**: Docker 测试比本地测试慢，建议开发时用本地测试，CI/CD 时用 Docker

### Docker 故障排除

**问题**: 旧版本镜像格式错误
- **原因**: PHP 5.3-5.5 使用旧镜像格式，新 Docker 不支持
- **解决**: `docker-test.sh` 已自动跳过这些版本（在版本列表中已注释）

**问题**: Docker 命令未找到
- **解决**: 安装 Docker Desktop 或 Docker Engine

**问题**: 权限被拒绝（Linux）
- **解决**: `sudo usermod -aG docker $USER` 然后重新登录

## 测试环境要求

- PHP 5.3+ （推荐 PHP 7.0+）
- 可选：OPcache 扩展（用于 opcache 测试）
- 可选：exec() 函数可用（用于 php-cli 测试）
- 可选：Docker（用于多版本测试）

## 测试特点

1. **使用反射测试私有方法** - 通过 ReflectionClass 访问私有方法
2. **自动清理测试文件** - 测试在临时目录创建文件，结束后自动清理
3. **智能跳过不可用的测试** - 根据环境自动跳过不可用的测试
4. **错误处理完善** - 捕获所有异常，确保测试套件完整运行

## 注意事项

1. **OPcache 未启用**: 如果 OPcache 扩展未启用，opcache 相关测试会被跳过，这是正常的
2. **exec() 不可用**: 如果 `exec()` 函数被禁用，php-cli 测试会被跳过
3. **临时文件**: 测试会在系统临时目录创建文件，测试结束后会自动清理
4. **测试环境差异**: 根据环境不同，部分测试可能被跳过，这是正常行为

## 与原始实现的兼容性

本测试确保新的实现与原始 `eval()` 实现的行为一致：
- ✅ 返回相同的布尔值（true/false）
- ✅ 错误信息格式保持一致
- ✅ 错误信息包含编译后文件名、原模板文件名、出错行数
- ✅ 能够正确处理各种语法错误

## 测试输出示例

```
========================================
Zandy_Template check_syntax 单元测试
========================================

✓ PASS: 测试优先级逻辑：opcache > php-cli > eval
✓ PASS: opcache: 测试正确的 PHP 语法
⊘ SKIP: exec 不可用，跳过 PHP CLI 测试
✓ PASS: eval: 测试正确的 PHP 语法
...

========================================
测试结果汇总
========================================
通过: 11
失败: 0
总计: 11

✓ 所有测试通过！
```

## CI/CD 集成示例

### GitHub Actions
```yaml
name: PHP Multi-version Test
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: ['7.4', '8.0', '8.1', '8.2']
    steps:
      - uses: actions/checkout@v2
      - name: Run tests
        run: |
          docker run --rm -v $PWD:/app -w /app \
            php:${{ matrix.php-version }}-cli \
            php test/CheckSyntaxTest.php
```

## 相关文件

- `test/CheckSyntaxTest.php` - 测试主文件
- `test/run_tests.sh` - 本地测试脚本
- `test/docker-test.sh` - Docker 测试脚本（支持单版本、多版本、全部版本，已自动跳过不兼容的旧版本）
- `docker-compose.yml` - Docker Compose 配置
