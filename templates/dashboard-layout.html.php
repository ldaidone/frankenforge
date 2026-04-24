<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon.png">
    <title><?= htmlspecialchars($title ?? 'FrankenForge') ?></title>
    <script>
        const _ffTheme = localStorage.getItem('frankenforge-theme') || 'dark';
        if (_ffTheme === 'dark') document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
    </script>
    <script src="https://unpkg.com/htmx.org@2"></script>
    <script src="https://unpkg.com/htmx-ext-sse@2.2.1/sse.js"></script>
    <?php require __DIR__ . '/theme-config.html.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 font-mono">
    <!-- Mobile overlay (closes sidebar on tap) -->
    <div id="sidebar-overlay"
         class="fixed inset-0 bg-black/60 z-30 hidden lg:hidden"
         onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden');"></div>

    <!-- Mobile top bar -->
    <header class="lg:hidden fixed top-0 inset-x-0 h-14 bg-zinc-900 border-b border-zinc-800 z-20 flex items-center px-4 gap-3">
        <button onclick="document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('sidebar-overlay').classList.remove('hidden');"
                class="text-zinc-400 hover:text-zinc-100 p-1" aria-label="Open menu">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <span class="text-orange-500 font-bold text-sm truncate">⚡ <?= htmlspecialchars($title ?? 'FrankenForge') ?></span>
    </header>

    <div class="flex min-h-screen">
        <?php $sidebarPartial = __DIR__ . '/../src/Shared/UI/sidebar.html.php'; ?>
        <?php if (file_exists($sidebarPartial)): require $sidebarPartial; endif; ?>
        <main class="flex-1 min-h-screen mt-14 lg:mt-0">
            <?= $content ?? '' ?>
        </main>
    </div>
</body>
</html>
