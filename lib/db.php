<?php
// SQLite 数据库封装

class DB {
    private static $pdo = null;

    public static function get() {
        if (self::$pdo === null) {
            $dbDir = __DIR__ . '/../data';
            self::ensureDirectory($dbDir, '数据库目录');

            $dbPath = $dbDir . '/appmanager.db';
            if (file_exists($dbPath) && !is_writable($dbPath)) {
                throw new RuntimeException('数据库文件不可写: ' . $dbPath . '，请确认 Web 服务进程对该文件有写权限');
            }

            try {
                self::$pdo = new PDO('sqlite:' . $dbPath);
            } catch (PDOException $e) {
                throw new RuntimeException(
                    '无法打开 SQLite 数据库文件: ' . $dbPath . '，请确认 data/ 目录存在且 Web 服务进程可写',
                    0,
                    $e
                );
            }

            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->exec('PRAGMA journal_mode=WAL');
            self::$pdo->exec('PRAGMA foreign_keys=ON');

            self::initTables();
        }
        return self::$pdo;
    }

    private static function initTables() {
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS apps (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                app_key TEXT UNIQUE NOT NULL,
                app_name TEXT NOT NULL,
                icon_url TEXT DEFAULT '',
                created_at DATETIME DEFAULT (datetime('now','localtime'))
            )
        ");

        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS versions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                app_key TEXT NOT NULL,
                version_name TEXT NOT NULL,
                version_code INTEGER NOT NULL,
                changelog TEXT DEFAULT '',
                download_url TEXT DEFAULT '',
                file_name TEXT DEFAULT '',
                file_size INTEGER DEFAULT 0,
                force_update INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                downloads INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT (datetime('now','localtime')),
                FOREIGN KEY (app_key) REFERENCES apps(app_key) ON DELETE CASCADE
            )
        ");

        self::$pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_versions_app ON versions(app_key, version_code DESC)
        ");
    }

    // 便捷方法
    public static function query($sql, $params = []) {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll($sql, $params = []) {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne($sql, $params = []) {
        return self::query($sql, $params)->fetch();
    }

    public static function insert($sql, $params = []) {
        self::query($sql, $params);
        return self::get()->lastInsertId();
    }

    public static function execute($sql, $params = []) {
        return self::query($sql, $params)->rowCount();
    }

    private static function ensureDirectory($dir, $label) {
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            throw new RuntimeException($label . '创建失败: ' . $dir . '，请确认 Web 服务进程对该路径有写权限');
        }

        if (!is_writable($dir)) {
            throw new RuntimeException($label . '不可写: ' . $dir . '，请确认 Web 服务进程对该路径有写权限');
        }
    }
}
