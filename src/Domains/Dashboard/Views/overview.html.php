<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
/**
 * Dashboard overview — the default landing view inside the sidebar layout.
 *
 * Expected variables: (none — all data loaded via HTMX)
 */

$component = __DIR__ . '/Components';
?>

<!-- Page Header -->
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b" style="border-color: var(--app-border)">
    <h1 class="text-xl sm:text-2xl font-bold" style="color: var(--app-text)">Overview</h1>
    <p class="text-xs sm:text-sm mt-1" style="color: var(--app-text-muted)">Real-time system metrics and activity</p>
</div>

<?php
$flashMessages = $flash ?? [];
?>

<!-- Content Area -->
<div class="p-4 sm:p-6 lg:p-8 space-y-6 lg:space-y-8">

    <div id="flash-container">
        <?php if (!empty($flashMessages)): ?>
        <?php include __DIR__ . '/Components/flash-messages.html.php'; ?>
        <?php endif; ?>
    </div>

    <!-- Stat Cards — HTMX loaded + polled every 30s -->
    <div class="relative">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs sm:text-sm font-semibold uppercase tracking-wider" style="color: var(--app-text-muted)">Metrics</h2>
            <span class="htmx-indicator opacity-0 text-xs transition-opacity duration-200" style="color: var(--app-text-muted)">
                <i class="fa-solid fa-circle-notch fa-spin mr-1"></i><span class="hidden sm:inline">Refreshing…</span>
            </span>
        </div>
        <div id="stat-cards"
             hx-get="/dashboard/stats"
             hx-trigger="load, every 30s"
             hx-swap="innerHTML swap:200ms"
             hx-indicator=".htmx-indicator">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-xl p-4 sm:p-5 animate-pulse" style="background: var(--app-section)">
                <div class="h-4 w-20 rounded" style="background: var(--app-section-hover)"></div>
                <div class="h-8 w-24 mt-3 rounded" style="background: var(--app-section-hover)"></div>
            </div>
            <div class="rounded-xl p-4 sm:p-5 animate-pulse" style="background: var(--app-section)">
                <div class="h-4 w-24 rounded" style="background: var(--app-section-hover)"></div>
                <div class="h-8 w-16 mt-3 rounded" style="background: var(--app-section-hover)"></div>
            </div>
            <div class="rounded-xl p-4 sm:p-5 animate-pulse" style="background: var(--app-section)">
                <div class="h-4 w-20 rounded" style="background: var(--app-section-hover)"></div>
                <div class="h-8 w-10 mt-3 rounded" style="background: var(--app-section-hover)"></div>
            </div>
            <div class="rounded-xl p-4 sm:p-5 animate-pulse" style="background: var(--app-section)">
                <div class="h-4 w-14 rounded" style="background: var(--app-section-hover)"></div>
                <div class="h-8 w-20 mt-3 rounded" style="background: var(--app-section-hover)"></div>
            </div>
        </div>
    </div>

    <!-- Two-Column Row: Users + Invoices -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

        <!-- Users Table -->
        <div>
            <div class="flex items-center justify-between mb-3 mt-3">
                <h2 class="text-xs sm:text-sm font-semibold uppercase tracking-wider" style="color: var(--app-text-muted)">Users</h2>
                <span class="htmx-indicator opacity-0 text-xs transition-opacity duration-200" style="color: var(--app-text-muted)">
                    <i class="fa-solid fa-circle-notch fa-spin mr-1"></i>
                </span>
            </div>
            <div id="users-table"
                 hx-get="/dashboard/users"
                 hx-trigger="load"
                 hx-swap="innerHTML swap:200ms"
                 hx-indicator=".htmx-indicator">
                <div class="rounded-xl p-4 animate-pulse" style="background: var(--app-section)">
                    <div class="h-8 w-full rounded mb-2" style="background: var(--app-section-hover)"></div>
                    <div class="h-8 w-full rounded mb-2" style="background: var(--app-section-hover)"></div>
                    <div class="h-8 w-full rounded" style="background: var(--app-section-hover)"></div>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div>
            <div class="flex items-center justify-between mb-3 mt-3">
                <h2 class="text-xs sm:text-sm font-semibold uppercase tracking-wider" style="color: var(--app-text-muted)">Invoices</h2>
                <span class="htmx-indicator opacity-0 text-xs transition-opacity duration-200" style="color: var(--app-text-muted)">
                    <i class="fa-solid fa-circle-notch fa-spin mr-1"></i>
                </span>
            </div>
            <div id="invoices-table"
                 hx-get="/dashboard/invoices"
                 hx-trigger="load"
                 hx-swap="innerHTML swap:200ms"
                 hx-indicator=".htmx-indicator">
                <div class="rounded-xl p-4 animate-pulse" style="background: var(--app-section)">
                    <div class="h-8 w-full rounded mb-2" style="background: var(--app-section-hover)"></div>
                    <div class="h-8 w-full rounded mb-2" style="background: var(--app-section-hover)"></div>
                    <div class="h-8 w-full rounded" style="background: var(--app-section-hover)"></div>
                </div>
            </div>
        </div>
    </div>

<!-- Quick Links -->
    <div class="rounded-xl p-4 sm:p-6" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
        <h2 class="text-xs sm:text-sm font-semibold uppercase tracking-wider mb-4" style="color: var(--app-text-on-section)">Quick Links</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
            <a href="/demo" class="flex items-center gap-2 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg text-xs sm:text-sm transition"
               style="background: var(--app-quick-link)"
               onmouseover="this.style.background='var(--app-quick-link-hover)'"
               onmouseout="this.style.background='var(--app-quick-link)'">
                <i class="fa-solid fa-bolt text-orange-400"></i>
                <span style="color: var(--app-text-on-section)">Demo Page</span>
            </a>
            <a href="/" class="flex items-center gap-2 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg text-xs sm:text-sm transition"
               style="background: var(--app-quick-link)"
               onmouseover="this.style.background='var(--app-quick-link-hover)'"
               onmouseout="this.style.background='var(--app-quick-link)'">
                <i class="fa-solid fa-house text-orange-400"></i>
                <span style="color: var(--app-text-on-section)">Landing</span>
            </a>
            <a href="/api/ping" class="flex items-center gap-2 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg text-xs sm:text-sm transition"
               style="background: var(--app-quick-link)"
               onmouseover="this.style.background='var(--app-quick-link-hover)'"
               onmouseout="this.style.background='var(--app-quick-link)'">
                <i class="fa-solid fa-satellite-dish text-orange-400"></i>
                <span style="color: var(--app-text-on-section)">Ping API</span>
            </a>
            <a href="/api/counter" class="flex items-center gap-2 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg text-xs sm:text-sm transition"
               style="background: var(--app-quick-link)"
               onmouseover="this.style.background='var(--app-quick-link-hover)'"
               onmouseout="this.style.background='var(--app-quick-link)'">
                <i class="fa-solid fa-hashtag text-orange-400"></i>
                <span style="color: var(--app-text-on-section)">Counter API</span>
            </a>
        </div>
        <div class="mt-4 pt-4 border-t" style="border-color: var(--app-section-border)">
            <h3 class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--app-text-muted)">Flash Message Demo</h3>
            <div class="flex flex-wrap gap-2">
                <button hx-post="/flash/success" hx-target="#flash-container" hx-swap="innerHTML"
                        class="px-3 py-1.5 rounded text-xs font-medium bg-green-500/20 text-green-400 hover:bg-green-500/30 transition">
                    <i class="fa-solid fa-check-circle mr-1"></i>Success
                </button>
                <button hx-post="/flash/error" hx-target="#flash-container" hx-swap="innerHTML"
                        class="px-3 py-1.5 rounded text-xs font-medium bg-red-500/20 text-red-400 hover:bg-red-500/30 transition">
                    <i class="fa-solid fa-xmark-circle mr-1"></i>Error
                </button>
                <button hx-post="/flash/warning" hx-target="#flash-container" hx-swap="innerHTML"
                        class="px-3 py-1.5 rounded text-xs font-medium bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30 transition">
                    <i class="fa-solid fa-exclamation-triangle mr-1"></i>Warning
                </button>
                <button hx-post="/flash/info" hx-target="#flash-container" hx-swap="innerHTML"
                        class="px-3 py-1.5 rounded text-xs font-medium bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 transition">
                    <i class="fa-solid fa-info-circle mr-1"></i>Info
                </button>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
