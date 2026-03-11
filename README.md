# appmanager

一个基于 PHP 和 SQLite 的轻量级应用版本管理系统，用于统一维护应用信息、版本包、更新日志和下载入口。

## 项目概览

- 项目名称：appmanager
- 项目类型：单体 PHP Web 应用
- 创建时间：2026-03-11
- 适用场景：企业内部分发、测试包托管、版本更新检查、下载地址统一管理

## 核心功能

- 管理端登录后可创建应用、编辑应用信息、上传图标
- 支持为每个应用发布多个版本，并维护版本号、版本编码、更新日志、强制更新状态
- 支持本地上传安装包，也支持配置外部下载链接
- 提供公开更新检查接口，可返回最新版本信息和下载地址
- 提供下载接口并自动累计下载次数
- 管理首页内置应用数、版本数、下载量统计
- 支持生成下载二维码，便于移动端扫码安装

## 技术实现

- 后端使用原生 PHP 编写，无框架依赖
- 数据存储使用 SQLite，首次运行时自动创建 `apps` 和 `versions` 表
- 管理认证基于管理员密码和 SHA-256 Token
- 前端为单页管理界面，使用原生 HTML、CSS、JavaScript 实现
- 二维码生成功能通过前端库 `qrcode-generator` 完成

## 代码结构

- `index.php`：管理后台页面与前端交互逻辑
- `api.php`：统一 API 入口，负责登录、应用管理、版本管理、更新检查和下载
- `lib/auth.php`：管理员认证与 JSON 输出封装
- `lib/db.php`：SQLite 连接、建表与基础数据库操作封装
- `config.example.php`：项目配置模板

## 运行要求

- 支持 PDO 与 SQLite 扩展的 PHP 环境
- 启用 PDO 和 SQLite 扩展
- Web 服务进程对 `data/` 与 `uploads/` 目录具备写权限
- 部署时需根据 `config.example.php` 创建本地 `config.php`

## 配置项

- `admin_password`：管理员登录密码
- `allowed_extensions`：允许上传的安装包扩展名
- `max_upload_size`：单文件上传大小限制
- `site_title`：后台页面标题
- `cors_origins`：允许跨域访问的来源列表
