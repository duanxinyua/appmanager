<?php
// 管理员认证

function get_config() {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config.php';
    }
    return $config;
}

function check_admin_auth() {
    $config = get_config();
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? ($_GET['token'] ?? '');

    if (!$token) {
        return false;
    }

    $expected = hash('sha256', $config['admin_password'] . '_appmanager');
    return hash_equals($expected, $token);
}

function require_admin() {
    if (!check_admin_auth()) {
        http_response_code(401);
        echo json_encode(['error' => '未授权，请先登录'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function generate_token($password) {
    $config = get_config();
    if ($password !== $config['admin_password']) {
        return null;
    }
    return hash('sha256', $config['admin_password'] . '_appmanager');
}

function json_out($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
