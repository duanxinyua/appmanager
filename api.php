<?php
// api.php — 版本管理 API 统一入口

header('Content-Type: application/json; charset=utf-8');

// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // ===== 公开接口 =====
        case 'check':
            action_check();
            break;
        case 'download':
            action_download();
            break;

        // ===== 管理接口 =====
        case 'login':
            action_login();
            break;
        case 'dashboard':
            require_admin();
            action_dashboard();
            break;
        case 'list_apps':
            require_admin();
            action_list_apps();
            break;
        case 'add_app':
            require_admin();
            action_add_app();
            break;
        case 'update_app':
            require_admin();
            action_update_app();
            break;
        case 'delete_app':
            require_admin();
            action_delete_app();
            break;
        case 'list_versions':
            require_admin();
            action_list_versions();
            break;
        case 'upload_version':
            require_admin();
            action_upload_version();
            break;
        case 'toggle_version':
            require_admin();
            action_toggle_version();
            break;
        case 'delete_version':
            require_admin();
            action_delete_version();
            break;

        default:
            json_out(['error' => '未知操作: ' . $action], 400);
    }
} catch (Exception $e) {
    json_out(['error' => $e->getMessage()], 500);
}

function ensure_runtime_dir($dir, $label) {
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        json_out(['error' => $label . '创建失败，请确认 Web 服务进程对该目录有写权限: ' . $dir], 500);
    }

    if (!is_writable($dir)) {
        json_out(['error' => $label . '不可写，请确认目录权限: ' . $dir], 500);
    }
}

// ===== 公开接口实现 =====

function action_check() {
    $app_key = $_GET['app_key'] ?? '';
    $version_code = intval($_GET['version_code'] ?? 0);
    $version_name = trim($_GET['version_name'] ?? '');

    if (!$app_key) {
        json_out(['error' => '缺少 app_key'], 400);
    }

    $latest = DB::fetchOne(
        "SELECT * FROM versions WHERE app_key = ? AND is_active = 1 ORDER BY version_code DESC LIMIT 1",
        [$app_key]
    );

    if (!$latest) {
        json_out(['has_update' => false]);
    }

    $latestCode = intval($latest['version_code'] ?? 0);
    $latestName = trim($latest['version_name'] ?? '');

    // 优先使用 version_name 进行严格的语义化版本号比对，能防范 version_code 在不同基座下混乱的问题
    if ($version_name !== '' && $latestName !== '') {
        // 如果本地版本号 >= 线上最新版本号，则不需要更新
        if (version_compare($version_name, $latestName, '>=')) {
            json_out(['has_update' => false]);
        }
    } else {
        // 退化到仅用 version_code 比较
        if ($latestCode <= $version_code) {
            json_out(['has_update' => false]);
        }
    }

    // 构造完整下载 URL
    $baseUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['SCRIPT_NAME']), '/');

    $downloadUrl = $latest['download_url'];
    if ($latest['file_name'] && !$downloadUrl) {
        $downloadUrl = $baseUrl . '/uploads/' . $latest['file_name'];
    }

    json_out([
        'has_update' => true,
        'latest' => [
            'id' => (int)$latest['id'],
            'version_name' => $latest['version_name'],
            'version_code' => (int)$latest['version_code'],
            'changelog' => $latest['changelog'],
            'download_url' => $downloadUrl,
            'file_size' => (int)$latest['file_size'],
            'force_update' => (bool)$latest['force_update'],
        ]
    ]);
}

function action_download() {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        json_out(['error' => '缺少版本 ID'], 400);
    }

    $ver = DB::fetchOne("SELECT * FROM versions WHERE id = ?", [$id]);
    if (!$ver) {
        json_out(['error' => '版本不存在'], 404);
    }

    // 增加下载计数
    DB::execute("UPDATE versions SET downloads = downloads + 1 WHERE id = ?", [$id]);

    // 如果是本地文件
    if ($ver['file_name']) {
        $filePath = __DIR__ . '/uploads/' . $ver['file_name'];
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $ver['file_name'] . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
    }

    // 否则重定向到外部 URL
    if ($ver['download_url']) {
        header('Location: ' . $ver['download_url'], true, 302);
        exit;
    }

    json_out(['error' => '文件不存在'], 404);
}

// ===== 管理接口实现 =====

function action_login() {
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? ($_POST['password'] ?? '');

    $token = generate_token($password);
    if (!$token) {
        json_out(['error' => '密码错误'], 401);
    }

    json_out(['success' => true, 'token' => $token]);
}

function action_dashboard() {
    $apps = DB::fetchAll("SELECT * FROM apps ORDER BY created_at DESC");
    $totalApps = count($apps);
    $totalVersions = DB::fetchOne("SELECT COUNT(*) as cnt FROM versions")['cnt'];
    $totalDownloads = DB::fetchOne("SELECT COALESCE(SUM(downloads), 0) as cnt FROM versions")['cnt'];

    // 每个应用的最新版本
    $appStats = [];
    foreach ($apps as $app) {
        $latest = DB::fetchOne(
            "SELECT version_name, version_code, downloads FROM versions WHERE app_key = ? AND is_active = 1 ORDER BY version_code DESC LIMIT 1",
            [$app['app_key']]
        );
        $totalDl = DB::fetchOne(
            "SELECT COALESCE(SUM(downloads), 0) as cnt FROM versions WHERE app_key = ?",
            [$app['app_key']]
        )['cnt'];

        $appStats[] = [
            'app_key' => $app['app_key'],
            'app_name' => $app['app_name'],
            'icon_url' => $app['icon_url'],
            'latest_version' => $latest ? $latest['version_name'] : '-',
            'latest_version_code' => $latest ? (int)$latest['version_code'] : 0,
            'total_downloads' => (int)$totalDl,
        ];
    }

    json_out([
        'total_apps' => $totalApps,
        'total_versions' => (int)$totalVersions,
        'total_downloads' => (int)$totalDownloads,
        'apps' => $appStats,
    ]);
}

function action_list_apps() {
    $apps = DB::fetchAll("SELECT * FROM apps ORDER BY created_at DESC");
    json_out(['apps' => $apps]);
}

function action_add_app() {
    $app_key = trim($_POST['app_key'] ?? '');
    $app_name = trim($_POST['app_name'] ?? '');

    if (!$app_key || !$app_name) {
        json_out(['error' => 'app_key 和 app_name 不能为空'], 400);
    }

    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $app_key)) {
        json_out(['error' => 'app_key 只能包含字母、数字、下划线和横线'], 400);
    }

    $existing = DB::fetchOne("SELECT id FROM apps WHERE app_key = ?", [$app_key]);
    if ($existing) {
        json_out(['error' => '应用标识已存在'], 400);
    }

    $icon_url = handle_icon_upload($app_key);

    $id = DB::insert(
        "INSERT INTO apps (app_key, app_name, icon_url) VALUES (?, ?, ?)",
        [$app_key, $app_name, $icon_url]
    );

    json_out(['success' => true, 'id' => (int)$id]);
}

function action_update_app() {
    $app_key = $_POST['app_key'] ?? '';
    $app_name = trim($_POST['app_name'] ?? '');

    if (!$app_key || !$app_name) {
        json_out(['error' => '参数不完整'], 400);
    }

    $icon_url = handle_icon_upload($app_key);

    if ($icon_url) {
        // 删除旧图标
        $old = DB::fetchOne("SELECT icon_url FROM apps WHERE app_key = ?", [$app_key]);
        if ($old && $old['icon_url']) {
            $oldFile = __DIR__ . '/' . ltrim(parse_url($old['icon_url'], PHP_URL_PATH), '/');
            // 只删除本地上传的图标
            $iconsDir = realpath(__DIR__ . '/uploads/icons');
            if ($iconsDir && strpos(realpath(dirname($oldFile)), $iconsDir) === 0 && file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }
        DB::execute("UPDATE apps SET app_name = ?, icon_url = ? WHERE app_key = ?", [$app_name, $icon_url, $app_key]);
    } else {
        DB::execute("UPDATE apps SET app_name = ? WHERE app_key = ?", [$app_name, $app_key]);
    }

    json_out(['success' => true]);
}

function handle_icon_upload($app_key) {
    if (!isset($_FILES['icon']) || $_FILES['icon']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $allowed = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico'];
    $ext = strtolower(pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        json_out(['error' => '不支持的图标格式: ' . $ext], 400);
    }

    $iconDir = __DIR__ . '/uploads/icons';
    ensure_runtime_dir($iconDir, '图标目录');

    $fileName = $app_key . '_icon.' . $ext;
    $destPath = $iconDir . '/' . $fileName;

    if (!move_uploaded_file($_FILES['icon']['tmp_name'], $destPath)) {
        json_out(['error' => '图标保存失败'], 500);
    }

    // 返回相对 URL
    $baseUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['SCRIPT_NAME']), '/');

    return $baseUrl . '/uploads/icons/' . $fileName;
}

function action_delete_app() {
    $input = json_decode(file_get_contents('php://input'), true);
    $app_key = $input['app_key'] ?? '';

    if (!$app_key) {
        json_out(['error' => '缺少 app_key'], 400);
    }

    // 删除关联的文件
    $versions = DB::fetchAll("SELECT file_name FROM versions WHERE app_key = ?", [$app_key]);
    foreach ($versions as $v) {
        if ($v['file_name']) {
            $path = __DIR__ . '/uploads/' . $v['file_name'];
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    DB::execute("DELETE FROM versions WHERE app_key = ?", [$app_key]);
    DB::execute("DELETE FROM apps WHERE app_key = ?", [$app_key]);

    json_out(['success' => true]);
}

function action_list_versions() {
    $app_key = $_GET['app_key'] ?? '';
    if (!$app_key) {
        json_out(['error' => '缺少 app_key'], 400);
    }

    $versions = DB::fetchAll(
        "SELECT * FROM versions WHERE app_key = ? ORDER BY version_code DESC",
        [$app_key]
    );

    json_out(['versions' => $versions]);
}

function action_upload_version() {
    $app_key = $_POST['app_key'] ?? '';
    $version_name = trim($_POST['version_name'] ?? '');
    $version_code = intval($_POST['version_code'] ?? 0);
    $changelog = trim($_POST['changelog'] ?? '');
    $force_update = intval($_POST['force_update'] ?? 0);
    $download_url = trim($_POST['download_url'] ?? '');

    if (!$app_key || !$version_name || !$version_code) {
        json_out(['error' => 'app_key、version_name、version_code 不能为空'], 400);
    }

    // 检查应用是否存在
    $app = DB::fetchOne("SELECT id FROM apps WHERE app_key = ?", [$app_key]);
    if (!$app) {
        json_out(['error' => '应用不存在'], 404);
    }

    // 检查版本号是否重复
    $existing = DB::fetchOne(
        "SELECT id FROM versions WHERE app_key = ? AND version_code = ?",
        [$app_key, $version_code]
    );
    if ($existing) {
        json_out(['error' => '版本号已存在'], 400);
    }

    $file_name = '';
    $file_size = 0;

    // 处理文件上传
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $config = get_config();
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $config['allowed_extensions'])) {
            json_out(['error' => '不允许的文件类型: ' . $ext], 400);
        }

        if ($_FILES['file']['size'] > $config['max_upload_size']) {
            json_out(['error' => '文件超过大小限制'], 400);
        }

        $uploadDir = __DIR__ . '/uploads';
        ensure_runtime_dir($uploadDir, '上传目录');

        $file_name = $app_key . '_v' . $version_name . '.' . $ext;
        $destPath = $uploadDir . '/' . $file_name;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
            json_out(['error' => '文件保存失败'], 500);
        }

        $file_size = filesize($destPath);
    }

    $id = DB::insert(
        "INSERT INTO versions (app_key, version_name, version_code, changelog, download_url, file_name, file_size, force_update) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$app_key, $version_name, $version_code, $changelog, $download_url, $file_name, $file_size, $force_update]
    );

    json_out(['success' => true, 'id' => (int)$id]);
}

function action_toggle_version() {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if (!$id) {
        json_out(['error' => '缺少版本 ID'], 400);
    }

    DB::execute("UPDATE versions SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?", [$id]);
    $ver = DB::fetchOne("SELECT is_active FROM versions WHERE id = ?", [$id]);

    json_out(['success' => true, 'is_active' => (bool)$ver['is_active']]);
}

function action_delete_version() {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if (!$id) {
        json_out(['error' => '缺少版本 ID'], 400);
    }

    $ver = DB::fetchOne("SELECT file_name FROM versions WHERE id = ?", [$id]);
    if ($ver && $ver['file_name']) {
        $path = __DIR__ . '/uploads/' . $ver['file_name'];
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    DB::execute("DELETE FROM versions WHERE id = ?", [$id]);
    json_out(['success' => true]);
}
