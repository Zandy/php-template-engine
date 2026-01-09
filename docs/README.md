# 文档目录

本目录包含 Zandy_Template 模板引擎的详细文档。

## 用户文档

### 1. [使用指南](usage.md)
详细的使用说明和最佳实践：
- 安装和配置
- 快速开始
- 变量传递方式
- 使用场景（面向过程、函数/类方法）
- 最佳实践

### 2. [API 参考](api.md)
完整的 API 文档：
- 所有方法的详细说明
- 参数说明和返回值
- 使用示例

### 3. [语法参考](syntax.md)
完整的模板语法参考：
- 变量输出
- 循环语法（12种格式）
- 条件判断
- Switch 语句
- 模板包含
- 其他语法特性

### 4. [安全指南](security.md)
安全使用指南：
- 变量访问安全
- 代码执行安全
- 路径安全
- 最佳实践

## 内部文档

### [技术评估](INTERNAL/comprehensive_evaluation.md)
项目的全面技术评估报告：
- 架构设计评估
- 代码质量评估
- 安全性评估（已修复 extract($GLOBALS) 安全问题）
- 性能评估
- 可维护性评估

### [代码质量改进](INTERNAL/code_quality_improvements.md)
代码质量改进建议和实施状态：
- 已实施的改进（包括 extract($GLOBALS) 安全问题修复）
- 待改进的问题
- 实施优先级

### [引擎评估](INTERNAL/engine_evaluation.md)
引擎功能评估和改进建议。

## 快速参考

- **快速开始**: 查看根目录的 [README.md](../README.md)
- **使用指南**: 查看 [usage.md](usage.md)
- **API 文档**: 查看 [api.md](api.md)
- **语法参考**: 查看 [syntax.md](syntax.md)
- **安全指南**: 查看 [security.md](security.md)