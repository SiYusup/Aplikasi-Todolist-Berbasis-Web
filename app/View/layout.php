<?php
/** @var string $title */ /** @var string $content */ /** @var object|null $user */
$title = $title ?? 'To-Do List';
$isGuest = !isset($user) || !$user;
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
function navActive(string $href, string $path): string {
    if ($href === '/') return $path === '/' ? 'app-nav-active' : '';
    return str_starts_with($path, $href) ? 'app-nav-active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title) ?> — To-Do List</title>
  <script>try{if(localStorage.getItem('theme')==='dark'){document.documentElement.classList.add('app-dark')}}catch(e){}</script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/franken-ui@1/dist/css/zinc.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios@1/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1/src/toastify.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/toastify-js@1/src/toastify.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/franken-ui@1/dist/js/core.iife.js" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/franken-ui@1/dist/js/icon.iife.js" type="module"></script>
  <style>
    [x-cloak] { display: none !important; }
    svg.lucide { width: 18px; height: 18px; stroke-width: 1.8; }
    .ic-sm svg.lucide { width: 14px; height: 14px; }
    .ic-lg svg.lucide { width: 22px; height: 22px; }
    .app-shell { max-width: 1024px; margin: 0 auto; padding: 32px 20px 72px; }
    .app-nav { background: #fff; border-bottom: 1px solid #e4e4e7; position: sticky; top: 0; z-index: 50; }
    .app-nav-inner { max-width: 1024px; margin: 0 auto; padding: 0 20px; height: 60px; display: flex; align-items: center; gap: 28px; }
    .app-logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; color: #18181b; text-decoration: none; }
    .app-logo-mark { width: 32px; height: 32px; border-radius: 9px; background: #18181b; color: #fff; display: inline-flex; align-items: center; justify-content: center; }
    .app-links { display: flex; gap: 4px; margin-left: 8px; }
    .app-links a { display: inline-flex; align-items: center; gap: 7px; padding: 8px 13px; border-radius: 8px; font-size: 14px; color: #52525b; text-decoration: none; }
    .app-links a:hover { background: #f4f4f5; color: #18181b; }
    .app-links a.app-nav-active { background: #f4f4f5; color: #18181b; font-weight: 600; }
    .app-user { margin-left: auto; display: inline-flex; align-items: center; gap: 9px; padding: 4px 12px 4px 4px; border-radius: 999px; text-decoration: none; color: #18181b; font-weight: 600; font-size: 14px; }
    .app-user:hover { background: #f4f4f5; color: #18181b; text-decoration: none; }
    .app-avatar { width: 30px; height: 30px; border-radius: 999px; background: #18181b; color: #fff; display: inline-flex; align-items: center; justify-content: center; }
    .app-avatar svg.lucide { width: 16px; height: 16px; }
    .app-page-head { margin: 4px 0 22px; }
    .app-page-head h2 { margin: 0 0 4px; font-size: 24px; font-weight: 700; letter-spacing: -0.01em; }
    .app-page-head p { margin: 0; }
    /* modal overlay mandiri (tidak tergantung JS FrankenUI) */
    .app-overlay { position: fixed; inset: 0; z-index: 1000; background: rgba(9, 9, 11, 0.45); display: flex; align-items: flex-start; justify-content: center; padding: 56px 16px 24px; overflow-y: auto; }
    .app-modal { width: 100%; max-width: 540px; border-radius: 14px; }
    dialog.app-dialog { border: none; border-radius: 14px; padding: 0; max-width: 420px; width: calc(100% - 32px); margin: auto; }
    dialog.app-dialog::backdrop { background: rgba(9, 9, 11, 0.45); }
    /* auth */
    .auth-wrap { min-height: calc(100vh - 48px); display: flex; align-items: center; justify-content: center; padding: 32px 16px; }
    .auth-card { width: 100%; max-width: 400px; border-radius: 16px; }
    .auth-brand { width: 44px; height: 44px; border-radius: 13px; background: #18181b; color: #fff; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
    /* tabel */
    table.app-table thead th { font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.06em; color: #71717a; font-weight: 600; border-bottom: 1px solid #e4e4e7; }
    table.app-table tbody td { padding-top: 13px; padding-bottom: 13px; vertical-align: middle; }
    .dot { width: 9px; height: 9px; border-radius: 999px; display: inline-block; margin-right: 8px; background: #d4d4d8; border: 1px solid rgba(0,0,0,0.08); }
    /* dark mode */
    html.app-dark body { background: #09090b; color: #fafafa; }
    html.app-dark .app-nav { background: #09090b; border-color: #27272a; }
    html.app-dark .app-logo { color: #fafafa; }
    html.app-dark .app-links a { color: #a1a1aa; }
    html.app-dark .app-links a:hover { background: #27272a; color: #fafafa; }
    html.app-dark .app-links a.app-nav-active { background: #27272a; color: #fafafa; }
    html.app-dark .app-user { color: #fafafa; }
    html.app-dark .app-user:hover { background: #27272a; color: #fafafa; }
    html.app-dark .app-avatar { background: #fafafa; color: #09090b; }
    html.app-dark .uk-card-default { background: #18181b; border-color: #27272a; color: #fafafa; }
    html.app-dark .uk-card-title, html.app-dark h2, html.app-dark h4 { color: #fafafa; }
    html.app-dark .uk-text-meta { color: #a1a1aa !important; }
    html.app-dark .uk-input, html.app-dark .uk-select, html.app-dark .uk-textarea { background: #27272a; border-color: #3f3f46; color: #fafafa; }
    html.app-dark .uk-button-default { background: #27272a; border-color: #3f3f46; color: #fafafa; }
    html.app-dark table.app-table thead th { color: #a1a1aa; border-color: #27272a; }
    html.app-dark .uk-table td, html.app-dark .uk-table th { border-color: #27272a; }
    html.app-dark .task-foot { border-color: #27272a !important; }
    html.app-dark dialog.app-dialog { background: #18181b; color: #fafafa; }
    html.app-dark code { background: #27272a; color: #fafafa; }
    /* card tugas seragam */
    .task-grid > div { display: flex; }
    .task-card { flex: 1; display: flex; flex-direction: column; }
    .task-desc { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3em; }
    .task-foot { margin-top: auto; }
    #donut { min-height: 260px; }
  </style>
</head>
<body class="uk-background-muted" <?= $isGuest ? '' : 'x-data="reminderPoller()"' ?>>
  <?php if (!$isGuest): ?>
  <header class="app-nav">
    <div class="app-nav-inner">
      <a class="app-logo" href="/">
        <span class="app-logo-mark"><i data-lucide="list-checks"></i></span>
        <span>To-Do</span>
      </a>
      <nav class="app-links">
        <a href="/" class="<?= navActive('/', $path) ?>"><i data-lucide="layout-dashboard"></i><span data-i18n="nav.dashboard">Dashboard</span></a>
        <a href="/tasks" class="<?= navActive('/tasks', $path) ?>"><i data-lucide="square-check-big"></i><span data-i18n="nav.tasks">Tugas</span></a>
        <a href="/categories" class="<?= navActive('/categories', $path) ?>"><i data-lucide="tags"></i><span data-i18n="nav.categories">Kategori</span></a>
        <a href="/history" class="<?= navActive('/history', $path) ?>"><i data-lucide="history"></i><span data-i18n="nav.history">History</span></a>
        <a href="/settings" class="<?= navActive('/settings', $path) ?>"><i data-lucide="settings"></i><span data-i18n="nav.settings">Pengaturan</span></a>
      </nav>
      <a href="/settings" class="app-user" title="Pengaturan akun">
        <span class="app-avatar"><i data-lucide="circle-user-round"></i></span>
        <span><?= htmlspecialchars($user->username) ?></span>
      </a>
    </div>
  </header>
  <?php endif; ?>

  <main class="<?= $isGuest ? '' : 'app-shell' ?>">
    <?php require $content; ?>
  </main>

  <script src="/assets/app.js"></script>
  <script>lucide.createIcons();</script>
  <?php if (!$isGuest): ?>
  <script>
    function reminderPoller() {
      return {
        init() { this.check(); setInterval(() => this.check(), 60000); },
        async check() {
          try {
            const { data } = await axios.get('/api/tasks/reminders');
            (data.data || []).forEach(t => toast('Pengingat: ' + t.title, 'warning'));
          } catch (e) { /* abaikan saat offline */ }
        }
      };
    }
  </script>
  <?php endif; ?>
</body>
</html>
