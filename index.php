<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>应用版本管理</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#0a1628;--card:#141e33;--border:#1e2d47;--accent:#1677FF;--accent2:#0d5bdb;
--green:#34d399;--red:#f87171;--yellow:#fbbf24;--text:#e2e8f0;--text2:#94a3b8;--text3:#64748b;
--radius:12px;--shadow:0 4px 24px rgba(0,0,0,.3)}
body{background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','PingFang SC','Hiragino Sans GB','Microsoft YaHei',sans-serif;min-height:100vh}
a{color:var(--accent);text-decoration:none}

/* Layout */
.container{max-width:960px;margin:0 auto;padding:20px}
.header{display:flex;align-items:center;justify-content:space-between;padding:20px 0;border-bottom:1px solid var(--border);margin-bottom:24px}
.header h1{font-size:20px;font-weight:700}
.header .actions{display:flex;gap:10px}

/* Cards */
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:16px}
.card-title{font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:16px;text-align:center}
.stat-num{font-size:28px;font-weight:700;color:var(--accent)}
.stat-label{font-size:12px;color:var(--text3);margin-top:4px}

/* Table */
table{width:100%;border-collapse:collapse}
th,td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--border);font-size:13px}
th{color:var(--text3);font-weight:500;font-size:12px;text-transform:uppercase}
td{color:var(--text2)}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:500;transition:all .15s}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover{background:var(--accent2)}
.btn-sm{padding:5px 10px;font-size:12px;border-radius:6px}
.btn-danger{background:rgba(248,113,113,.15);color:var(--red);border:1px solid rgba(248,113,113,.2)}
.btn-danger:hover{background:rgba(248,113,113,.25)}
.btn-ghost{background:rgba(255,255,255,.06);color:var(--text2);border:1px solid var(--border)}
.btn-ghost:hover{background:rgba(255,255,255,.1)}
.btn-success{background:rgba(52,211,153,.15);color:var(--green);border:1px solid rgba(52,211,153,.2)}
.btn-warn{background:rgba(251,191,36,.15);color:var(--yellow);border:1px solid rgba(251,191,36,.2)}

/* Forms */
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;color:var(--text3);margin-bottom:5px}
.form-group input,.form-group textarea,.form-group select{width:100%;padding:9px 12px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px;outline:none}
.form-group input:focus,.form-group textarea:focus{border-color:var(--accent)}
.form-group textarea{resize:vertical;min-height:70px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-check{display:flex;align-items:center;gap:8px}
.form-check input[type="checkbox"]{width:auto;accent-color:var(--accent)}

/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(4px)}
.modal-overlay.active{display:flex}
.modal{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;width:90%;max-width:480px;max-height:80vh;overflow-y:auto;box-shadow:var(--shadow)}
.modal h3{font-size:16px;margin-bottom:16px}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:18px}

/* Badge */
.badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
.badge-active{background:rgba(52,211,153,.15);color:var(--green)}
.badge-inactive{background:rgba(248,113,113,.15);color:var(--red)}
.badge-force{background:rgba(251,191,36,.15);color:var(--yellow)}

/* QR Modal */
.qr-wrap{display:flex;flex-direction:column;align-items:center;gap:16px}
.qr-wrap canvas{border-radius:8px}
.qr-link{word-break:break-all;font-size:12px;color:var(--text2);background:rgba(255,255,255,.04);padding:10px 12px;border-radius:8px;width:100%;text-align:center;user-select:all}

/* Login */
.login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh}
.login-box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px;width:90%;max-width:360px;text-align:center}
.login-box h2{margin-bottom:20px;font-size:20px}
.login-box input{margin-bottom:12px}

/* Empty */
.empty{text-align:center;padding:40px;color:var(--text3);font-size:14px}

/* Responsive */
@media(max-width:600px){.stats{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}.header{flex-direction:column;gap:12px;text-align:center}}
</style>
</head>
<body>

<!-- 登录页 -->
<div id="loginPage" class="login-wrap" style="display:none">
  <div class="login-box">
    <h2>🔐 版本管理</h2>
    <div class="form-group">
      <input type="password" id="loginPwd" placeholder="输入管理密码" onkeydown="if(event.key==='Enter')doLogin()">
    </div>
    <button class="btn btn-primary" style="width:100%" onclick="doLogin()">登录</button>
    <p id="loginErr" style="color:var(--red);margin-top:10px;font-size:13px"></p>
  </div>
</div>

<!-- 主页面 -->
<div id="mainPage" class="container" style="display:none">
  <div class="header">
    <h1>📱 应用版本管理</h1>
    <div class="actions">
      <button class="btn btn-primary" onclick="showAddApp()">+ 添加应用</button>
      <button class="btn btn-ghost" onclick="logout()">退出</button>
    </div>
  </div>

  <!-- 统计 -->
  <div class="stats">
    <div class="stat-card"><div class="stat-num" id="statApps">0</div><div class="stat-label">应用数量</div></div>
    <div class="stat-card"><div class="stat-num" id="statVersions">0</div><div class="stat-label">版本总数</div></div>
    <div class="stat-card"><div class="stat-num" id="statDownloads">0</div><div class="stat-label">总下载量</div></div>
  </div>

  <!-- 应用列表 -->
  <div id="appList"></div>
</div>

<!-- 添加应用弹窗 -->
<div class="modal-overlay" id="modalAddApp">
  <div class="modal">
    <h3>添加应用</h3>
    <div class="form-group"><label>应用标识（唯一, 如 beike）</label><input id="newAppKey" placeholder="beike"></div>
    <div class="form-group"><label>应用名称</label><input id="newAppName" placeholder="贝壳"></div>
    <div class="form-group"><label>应用图标</label><input type="file" id="newAppIcon" accept="image/*"><div id="iconPreview" style="margin-top:8px"></div></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal('modalAddApp')">取消</button>
      <button class="btn btn-primary" onclick="submitAddApp()">添加</button>
    </div>
  </div>
</div>

<!-- 上传版本弹窗 -->
<div class="modal-overlay" id="modalUploadVer">
  <div class="modal">
    <h3>发布新版本 — <span id="uploadAppName"></span></h3>
    <input type="hidden" id="uploadAppKey">
    <div class="form-row">
      <div class="form-group"><label>版本号（显示）</label><input id="verName" placeholder="1.0.1"></div>
      <div class="form-group"><label>版本号（数字）</label><input type="number" id="verCode" placeholder="101"></div>
    </div>
    <div class="form-group"><label>更新日志</label><textarea id="verLog" placeholder="本版本更新内容..."></textarea></div>
    <div class="form-group"><label>安装包文件 (APK/IPA/WGT)</label><input type="file" id="verFile" accept=".apk,.ipa,.wgt"></div>
    <div class="form-group"><label>或 外部下载链接（可选）</label><input id="verUrl" placeholder="https://..."></div>
    <div class="form-check"><input type="checkbox" id="verForce"><label>强制更新</label></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal('modalUploadVer')">取消</button>
      <button class="btn btn-primary" id="btnUpload" onclick="submitUploadVer()">发布</button>
    </div>
  </div>
</div>

<!-- 下载二维码弹窗 -->
<div class="modal-overlay" id="modalQR">
  <div class="modal">
    <h3 id="qrTitle">下载二维码</h3>
    <div class="qr-wrap">
      <canvas id="qrCanvas"></canvas>
      <div class="qr-link" id="qrLinkText"></div>
      <button class="btn btn-primary" onclick="copyQRLink()">复制链接</button>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal('modalQR')">关闭</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
const API = './api.php';
let token = localStorage.getItem('appmanager_token') || '';

// ===== Auth =====
function checkAuth() {
  if (token) {
    document.getElementById('loginPage').style.display = 'none';
    document.getElementById('mainPage').style.display = 'block';
    loadDashboard();
  } else {
    document.getElementById('loginPage').style.display = 'flex';
    document.getElementById('mainPage').style.display = 'none';
  }
}

async function doLogin() {
  const pwd = document.getElementById('loginPwd').value;
  const res = await api('login', { password: pwd }, 'POST');
  if (res.token) {
    token = res.token;
    localStorage.setItem('appmanager_token', token);
    checkAuth();
  } else {
    document.getElementById('loginErr').textContent = res.error || '登录失败';
  }
}

function logout() {
  token = '';
  localStorage.removeItem('appmanager_token');
  checkAuth();
}

// ===== API Helper =====
async function api(action, body, method = 'GET') {
  const opts = { headers: { 'X-Auth-Token': token } };
  let url = `${API}?action=${action}`;

  if (method === 'POST' && body instanceof FormData) {
    opts.method = 'POST';
    opts.body = body;
  } else if (method === 'POST') {
    opts.method = 'POST';
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(body);
  } else if (method === 'DELETE') {
    opts.method = 'DELETE';
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(body);
  } else if (body) {
    const params = new URLSearchParams(body);
    url += '&' + params.toString();
  }

  try {
    const r = await fetch(url, opts);
    const data = await r.json();
    if (r.status === 401) { logout(); return data; }
    return data;
  } catch (e) {
    return { error: e.message };
  }
}

// ===== Dashboard =====
async function loadDashboard() {
  const d = await api('dashboard');
  if (d.error) return;
  document.getElementById('statApps').textContent = d.total_apps;
  document.getElementById('statVersions').textContent = d.total_versions;
  document.getElementById('statDownloads').textContent = formatNum(d.total_downloads);
  renderAppList(d.apps);
}

function formatNum(n) {
  if (n >= 10000) return (n / 10000).toFixed(1) + 'w';
  if (n >= 1000) return (n / 1000).toFixed(1) + 'k';
  return String(n);
}

function formatSize(b) {
  if (!b) return '-';
  if (b > 1024*1024) return (b/1024/1024).toFixed(1) + ' MB';
  if (b > 1024) return (b/1024).toFixed(0) + ' KB';
  return b + ' B';
}

function renderAppList(apps) {
  const el = document.getElementById('appList');
  if (!apps || apps.length === 0) {
    el.innerHTML = '<div class="empty">暂无应用，点击上方按钮添加</div>';
    return;
  }
  el.innerHTML = apps.map(a => `
    <div class="card">
      <div class="card-title">
        <span>${a.icon_url ? '<img src="'+a.icon_url+'" style="width:24px;height:24px;border-radius:6px;vertical-align:middle;margin-right:8px">' : '📱'} ${a.app_name} <span style="color:var(--text3);font-size:12px;font-weight:400">${a.app_key}</span></span>
        <span>
          <button class="btn btn-sm btn-primary" onclick="showUploadVer('${a.app_key}','${a.app_name}','${a.latest_version}',${a.latest_version_code})">发布版本</button>
          <button class="btn btn-sm btn-ghost" onclick="toggleVersions('${a.app_key}')">版本列表</button>
          <button class="btn btn-sm btn-danger" onclick="deleteApp('${a.app_key}','${a.app_name}')">删除</button>
        </span>
      </div>
      <div style="display:flex;gap:20px;font-size:12px;color:var(--text3)">
        <span>最新版本: <b style="color:var(--text)">${a.latest_version}</b></span>
        <span>下载量: <b style="color:var(--text)">${formatNum(a.total_downloads)}</b></span>
      </div>
      <div id="ver_${a.app_key}" style="display:none;margin-top:16px"></div>
    </div>
  `).join('');
}

// ===== Apps =====
function showAddApp() {
  document.getElementById('newAppKey').value = '';
  document.getElementById('newAppName').value = '';
  document.getElementById('newAppIcon').value = '';
  document.getElementById('iconPreview').innerHTML = '';
  openModal('modalAddApp');
}

async function submitAddApp() {
  const key = document.getElementById('newAppKey').value.trim();
  const name = document.getElementById('newAppName').value.trim();
  if (!key || !name) return alert('标识和名称不能为空');

  const fd = new FormData();
  fd.append('app_key', key);
  fd.append('app_name', name);
  const iconInput = document.getElementById('newAppIcon');
  if (iconInput.files.length > 0) {
    fd.append('icon', iconInput.files[0]);
  }

  const res = await api('add_app', fd, 'POST');
  if (res.error) return alert(res.error);
  closeModal('modalAddApp');
  loadDashboard();
}

async function deleteApp(key, name) {
  if (!confirm(`确定删除应用「${name}」及其所有版本？`)) return;
  const res = await api('delete_app', { app_key: key }, 'POST');
  if (res.error) return alert(res.error);
  loadDashboard();
}

// ===== Versions =====
async function toggleVersions(appKey) {
  const el = document.getElementById('ver_' + appKey);
  if (el.style.display !== 'none') {
    el.style.display = 'none';
    return;
  }
  el.innerHTML = '<div style="padding:12px;color:var(--text3)">加载中...</div>';
  el.style.display = 'block';

  const res = await api('list_versions', { app_key: appKey });
  if (res.error) { el.innerHTML = `<div style="color:var(--red)">${res.error}</div>`; return; }

  if (!res.versions || res.versions.length === 0) {
    el.innerHTML = '<div style="padding:12px;color:var(--text3)">暂无版本</div>';
    return;
  }

  el.innerHTML = `<table>
    <tr><th>版本</th><th>代号</th><th>大小</th><th>下载</th><th>状态</th><th>操作</th></tr>
    ${res.versions.map(v => `<tr>
      <td><b>${v.version_name}</b></td>
      <td>${v.version_code}</td>
      <td>${formatSize(v.file_size)}</td>
      <td>${v.downloads}</td>
      <td>
        ${v.is_active ? '<span class="badge badge-active">启用</span>' : '<span class="badge badge-inactive">禁用</span>'}
        ${v.force_update ? ' <span class="badge badge-force">强制</span>' : ''}
      </td>
      <td>
        <button class="btn btn-sm btn-ghost" onclick="showQR(${v.id},'${v.version_name}')">二维码</button>
        <button class="btn btn-sm btn-ghost" onclick="copyLink(${v.id})">复制链接</button>
        <button class="btn btn-sm btn-ghost" onclick="toggleVer(${v.id},'${appKey}')">${v.is_active ? '禁用' : '启用'}</button>
        <button class="btn btn-sm btn-danger" onclick="deleteVer(${v.id},'${appKey}')">删除</button>
      </td>
    </tr>`).join('')}
  </table>`;
}

function bumpVersion(ver) {
  if (!ver || ver === '-') return '1.0.0';
  const parts = ver.split('.');
  const last = parseInt(parts[parts.length - 1]) || 0;
  parts[parts.length - 1] = String(last + 1);
  return parts.join('.');
}

function showUploadVer(appKey, appName, latestVersion, latestCode) {
  document.getElementById('uploadAppKey').value = appKey;
  document.getElementById('uploadAppName').textContent = appName;
  document.getElementById('verName').value = bumpVersion(latestVersion);
  document.getElementById('verCode').value = (latestCode || 0) + 1;
  document.getElementById('verLog').value = '';
  document.getElementById('verFile').value = '';
  document.getElementById('verUrl').value = '';
  document.getElementById('verForce').checked = true;
  openModal('modalUploadVer');
}

async function submitUploadVer() {
  const btn = document.getElementById('btnUpload');
  btn.disabled = true;
  btn.textContent = '上传中...';

  const fd = new FormData();
  fd.append('app_key', document.getElementById('uploadAppKey').value);
  fd.append('version_name', document.getElementById('verName').value.trim());
  fd.append('version_code', document.getElementById('verCode').value);
  fd.append('changelog', document.getElementById('verLog').value.trim());
  fd.append('download_url', document.getElementById('verUrl').value.trim());
  fd.append('force_update', document.getElementById('verForce').checked ? '1' : '0');

  const fileInput = document.getElementById('verFile');
  if (fileInput.files.length > 0) {
    fd.append('file', fileInput.files[0]);
  }

  const res = await api('upload_version', fd, 'POST');
  btn.disabled = false;
  btn.textContent = '发布';

  if (res.error) return alert(res.error);
  closeModal('modalUploadVer');
  loadDashboard();
}

async function toggleVer(id, appKey) {
  await api('toggle_version', { id }, 'POST');
  toggleVersions(appKey);
  // 重新折叠再展开以刷新
  const el = document.getElementById('ver_' + appKey);
  el.style.display = 'none';
  setTimeout(() => toggleVersions(appKey), 50);
}

async function deleteVer(id, appKey) {
  if (!confirm('确定删除此版本？')) return;
  await api('delete_version', { id }, 'POST');
  const el = document.getElementById('ver_' + appKey);
  el.style.display = 'none';
  toggleVersions(appKey);
  loadDashboard();
}

// ===== Modal =====
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('active'); });
});

// ===== Download Link & QR =====
let currentQRLink = '';

function getDownloadUrl(id) {
  const base = new URL(API, window.location.href);
  base.searchParams.set('action', 'download');
  base.searchParams.set('id', id);
  return base.href;
}

function copyLink(id) {
  const url = getDownloadUrl(id);
  navigator.clipboard.writeText(url).then(() => {
    alert('下载链接已复制');
  }).catch(() => {
    prompt('复制下载链接:', url);
  });
}

function showQR(id, versionName) {
  currentQRLink = getDownloadUrl(id);
  document.getElementById('qrTitle').textContent = `下载二维码 — v${versionName}`;
  document.getElementById('qrLinkText').textContent = currentQRLink;

  const canvas = document.getElementById('qrCanvas');
  generateQR(canvas, currentQRLink);
  openModal('modalQR');
}

function copyQRLink() {
  navigator.clipboard.writeText(currentQRLink).then(() => {
    alert('链接已复制');
  }).catch(() => {
    prompt('复制下载链接:', currentQRLink);
  });
}

// Render QR code to canvas using qrcode-generator
function generateQR(canvas, text) {
  const qr = qrcode(0, 'M');
  qr.addData(text);
  qr.make();
  const count = qr.getModuleCount();
  const scale = Math.max(4, Math.floor(240 / count));
  const border = 4;
  const size = (count + border * 2) * scale;
  canvas.width = size;
  canvas.height = size;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, size, size);
  ctx.fillStyle = '#000000';
  for (let y = 0; y < count; y++) {
    for (let x = 0; x < count; x++) {
      if (qr.isDark(y, x)) {
        ctx.fillRect((x + border) * scale, (y + border) * scale, scale, scale);
      }
    }
  }
}

// ===== Init =====
checkAuth();
</script>
</body>
</html>
