<?php
// 版本管理器配置模板
// 使用说明：复制此文件为 config.php 并修改管理员密码
//   cp config.example.php config.php

return [
    // 管理员密码（请务必修改）
    'admin_password' => 'your_password_here',

    // 允许上传的文件类型
    'allowed_extensions' => ['apk', 'ipa', 'wgt'],

    // 单文件上传限制（100MB）
    'max_upload_size' => 100 * 1024 * 1024,

    // 站点标题
    'site_title' => '应用版本管理',

    // 允许跨域的域名（空数组=不限制）
    'cors_origins' => [],
];
