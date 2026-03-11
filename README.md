# appmanager

应用版本管理站点代码仓库。

## 仓库位置

- Git 工作目录：`/www/wwwroot/apps.hetao.us`
- 远端仓库：`git@github.com:duanxinyua/appmanager.git`

## 迁移说明

- 2026-03-11，代码从 `/www/wwwroot/beike/appmanager` 迁移到 `/www/wwwroot/apps.hetao.us`
- `/www/wwwroot/beike` 本身不是 Git 仓库，因此删除 `/www/wwwroot/beike/appmanager` 属于本地目录清理，不会形成 Git 的文件删除记录
- 当前 Git 仓库只管理 `/www/wwwroot/apps.hetao.us` 下的代码内容

## 配置说明

- 运行配置文件为 `config.php`
- `config.php` 已在 `.gitignore` 中忽略，不进入版本控制
