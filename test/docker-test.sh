#!/bin/bash
# Docker 多版本 PHP 测试脚本

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

echo "=========================================="
echo "Docker 多版本 PHP 测试"
echo "=========================================="
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 测试的 PHP 版本列表
# 注意: PHP 5.3-5.5 使用旧镜像格式，新版本 Docker 可能不支持
# 默认只测试支持的版本，如需测试旧版本请参考 DOCKER_TEST.md
PHP_VERSIONS=(
    # "5.3"  # 旧镜像格式，新 Docker 不支持
    # "5.4"  # 旧镜像格式，新 Docker 不支持
    # "5.5"  # 旧镜像格式，新 Docker 不支持
    "5.6"
    "7.0"
    "7.1"
    "7.2"
    "7.3"
    "7.4"
    "8.0"
    "8.1"
    "8.2"
    "8.3"
    "8.4"
)

# 如果提供了参数，只测试指定的版本
if [ $# -gt 0 ]; then
    PHP_VERSIONS=("$@")
fi

PASSED=0
FAILED=0
SKIPPED=0

# 测试单个 PHP 版本
test_php_version() {
    local version=$1
    local image="php:${version}-cli"
    
    echo "----------------------------------------"
    echo "测试 PHP ${version}..."
    echo "----------------------------------------"
    
    # 检查镜像是否存在，不存在则尝试拉取
    if ! docker image inspect "$image" >/dev/null 2>&1; then
        echo -e "${YELLOW}⚠ 镜像 $image 不存在，尝试拉取...${NC}"
        if docker pull "$image" >/dev/null 2>&1; then
            echo -e "${GREEN}✓ 镜像拉取成功${NC}"
        else
            echo -e "${YELLOW}⚠ 镜像 $image 拉取失败，跳过${NC}"
            SKIPPED=$((SKIPPED + 1))
            return
        fi
    fi
    
    # 运行测试
    if docker run --rm \
        -v "$PROJECT_DIR:/app" \
        -w /app \
        "$image" \
        bash test/run_tests.sh 2>&1; then
        echo -e "${GREEN}✓ PHP ${version} 测试通过${NC}"
        PASSED=$((PASSED + 1))
    else
        echo -e "${RED}✗ PHP ${version} 测试失败${NC}"
        FAILED=$((FAILED + 1))
    fi
    
    echo ""
}

# 测试所有版本
for version in "${PHP_VERSIONS[@]}"; do
    test_php_version "$version"
done

# 输出总结
echo "=========================================="
echo "测试总结"
echo "=========================================="
echo -e "${GREEN}通过: ${PASSED}${NC}"
echo -e "${RED}失败: ${FAILED}${NC}"
echo -e "${YELLOW}跳过: ${SKIPPED}${NC}"
echo "总计: $((PASSED + FAILED + SKIPPED))"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ 所有测试通过！${NC}"
    exit 0
else
    echo -e "${RED}✗ 有 ${FAILED} 个测试失败${NC}"
    exit 1
fi

