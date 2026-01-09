#!/bin/bash
# 运行所有单元测试

cd "$(dirname "$0")"

echo "=========================================="
echo "运行 Zandy_Template 单元测试套件"
echo "=========================================="
echo ""

# 运行基本功能测试
echo "1. 运行基本功能测试..."
php BasicFeaturesTest.php
echo ""

# 运行命名循环测试
echo "2. 运行命名循环测试..."
php NamedLoopTest.php
echo ""

# 运行语法检查测试
echo "3. 运行语法检查测试..."
php CheckSyntaxTest.php
echo ""

echo "=========================================="
echo "所有测试完成"
echo "=========================================="

