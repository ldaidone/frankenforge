<?php

declare(strict_types=1);

/**
 * Shared sidebar navigation partial.
 *
 * Expected variables:
 *   - $navItems: array of ['label' => string, 'href' => string, 'icon' => string, 'active' => bool]
 *   - $brandLabel: string (logo text)
 */

$navItems ??= [];
$brandLabel ??= 'FrankenForge';
?>

<style>
    /* Sidebar transition */
    .sidebar-expanded { width: 16rem; }
    .sidebar-collapsed { width: 4rem; }
    .sidebar-collapsed .sidebar-label,
    .sidebar-collapsed .sidebar-divider,
    .sidebar-collapsed .sidebar-footer-text,
    .sidebar-collapsed .sidebar-theme-label { display: none; }
    .sidebar-collapsed .sidebar-nav-item { justify-content: center; padding-left: 0.75rem; padding-right: 0.75rem; }
    .sidebar-collapsed .sidebar-brand { justify-content: center; padding-left: 0; }
    .sidebar-collapsed .sidebar-user-info { display: none; }
    .sidebar-collapsed .sidebar-avatar { margin: 0 auto; }
</style>

<aside id="sidebar"
       class="sidebar-expanded bg-zinc-900 border-r border-zinc-800 flex flex-col min-h-screen shrink-0
              fixed lg:relative z-40 h-screen lg:h-auto
              -translate-x-full lg:translate-x-0
              transition-transform duration-200">
    <!-- Brand -->
    <div class="h-16 flex items-center px-5 border-b border-zinc-800 sidebar-brand">
        <span class="text-lg font-bold text-orange-500 whitespace-nowrap">⚡ <span class="sidebar-label"><?= htmlspecialchars($brandLabel) ?></span></span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 space-y-0.5 px-3">
        <?php foreach ($navItems as $item): ?>
            <?php if (!empty($item['divider'])): ?>
                <div class="sidebar-divider border-t border-zinc-800 my-3"></div>
            <?php else: ?>
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                          <?= ($item['active'] ?? false)
                              ? 'bg-orange-500/10 text-orange-400'
                              : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800' ?>">
                    <i class="fa-solid <?= htmlspecialchars($item['icon']) ?> w-4 text-center"></i>
                    <span class="sidebar-label"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="px-3 border-t border-zinc-800">
        <!-- Theme Toggle -->
        <div class="sidebar-theme-label flex items-center justify-between px-3 py-3 mt-2">
            <span class="text-xs text-zinc-500">Theme</span>
            <button id="theme-toggle"
                    onclick="toggleTheme()"
                    class="relative w-10 h-5 rounded-full bg-zinc-700 dark:bg-zinc-600 transition-colors duration-200 cursor-pointer"
                    aria-label="Toggle dark/light theme">
                <span id="theme-knob"
                      class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-orange-400 shadow-md transition-transform duration-200 flex items-center justify-center text-[8px] text-zinc-900 font-bold">
                    <i class="fa-solid fa-moon" id="theme-icon"></i>
                </span>
            </button>
        </div>

        <!-- User -->
        <div class="p-4 flex items-center gap-3 border-t border-zinc-800">
            <div class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-400 text-xs font-bold sidebar-avatar">
                FF
            </div>
            <div class="text-xs sidebar-user-info sidebar-footer-text">
                <div class="text-zinc-300 font-medium">Admin</div>
                <div class="text-zinc-500">admin@example.com</div>
            </div>
        </div>
    </div>
</aside>

<script>
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    const knob = document.getElementById('theme-knob');
    const icon = document.getElementById('theme-icon');

    if (isDark) {
        html.classList.remove('dark');
        knob.classList.remove('translate-x-5');
        icon.className = 'fa-solid fa-sun';
        localStorage.setItem('frankenforge-theme', 'light');
    } else {
        html.classList.add('dark');
        knob.classList.add('translate-x-5');
        icon.className = 'fa-solid fa-moon';
        localStorage.setItem('frankenforge-theme', 'dark');
    }
}

// Set initial toggle state on load
(function() {
    const isDark = document.documentElement.classList.contains('dark');
    const knob = document.getElementById('theme-knob');
    const icon = document.getElementById('theme-icon');
    if (!isDark) {
        icon.className = 'fa-solid fa-sun';
    } else {
        knob.classList.add('translate-x-5');
    }
})();

// Sidebar collapse toggle (desktop only)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (window.innerWidth < 1024) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    } else {
        sidebar.classList.toggle('sidebar-expanded');
        sidebar.classList.toggle('sidebar-collapsed');
    }
}

// Close sidebar on mobile when clicking overlay
(function() {
    const overlay = document.getElementById('sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }
})();
</script>
