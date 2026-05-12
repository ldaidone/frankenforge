<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="min-h-screen flex flex-col items-center justify-center px-6 py-16">

    <!-- Logo -->
    <img src="/assets/logo.jpg" alt="FrankenForge" class="w-64 h-64 mb-8 rounded-xl shadow-lg">

    <!-- Heading -->
    <h1 class="text-4xl md:text-5xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight mb-3">
        FrankenForge
    </h1>

    <!-- Tagline -->
    <p class="text-lg md:text-xl text-zinc-500 dark:text-zinc-400 text-center max-w-xl mb-8 leading-relaxed">
        Zero-bloat FrankenPHP + HTMX kernel for staff engineers.<br>
        Absolute DDD control. Zero framework tax.
    </p>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-center gap-3 mb-12">
        <a href="/dashboard"
           class="px-6 py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-sm font-semibold transition shadow-sm">
            Open Dashboard →
        </a>
        <a href="/demo"
           class="px-6 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 rounded-lg text-sm font-semibold transition">
            Interactive Demo
        </a>
        <a href="https://github.com/ldaidone/frankenforge" target="_blank" rel="noopener"
           class="px-6 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 rounded-lg text-sm font-semibold transition">
            <i class="fa-brands fa-github mr-1"></i> GitHub
        </a>
    </div>

    <!-- Quick Links -->
    <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-zinc-400 dark:text-zinc-500 mb-10">
        <a href="https://github.com/ldaidone/frankenforge#readme" target="_blank" rel="noopener"
           class="hover:text-orange-500 transition underline underline-offset-4 decoration-zinc-300 dark:decoration-zinc-600">
            Docs
        </a>
        <span class="text-zinc-300 dark:text-zinc-700">·</span>
        <a href="https://packagist.org/packages/frankenforge/kernel" target="_blank" rel="noopener"
           class="hover:text-orange-500 transition underline underline-offset-4 decoration-zinc-300 dark:decoration-zinc-600">
            Packagist
        </a>
        <span class="text-zinc-300 dark:text-zinc-700">·</span>
        <a href="/demo"
           class="hover:text-orange-500 transition underline underline-offset-4 decoration-zinc-300 dark:decoration-zinc-600">
            Demo
        </a>
    </div>

    <!-- SSE Heartbeat -->
    <div id="ping-result"
         class="rounded-xl px-6 py-3 text-center min-h-[44px] flex items-center justify-center text-sm"
         style="background: var(--app-section); border: 1px solid var(--app-section-border)">
        <span class="text-zinc-400 dark:text-zinc-500">Connecting...</span>
    </div>
    <script>
      (function() {
        var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        var pingUrl = '/api/ping?tz=' + encodeURIComponent(tz);
        var el = document.getElementById('ping-result');

        var source = new EventSource(pingUrl);
        source.addEventListener('message', function(e) {
          el.innerHTML = e.data;
        });
        source.onerror = function() {
          el.innerHTML = '<span class="text-zinc-400 dark:text-zinc-500">Disconnected</span>';
        };
      })();
    </script>

    <!-- Footer -->
    <p class="mt-8 text-xs text-zinc-400 dark:text-zinc-600">
        Built with FrankenPHP · PHP 8.3+ · HTMX · Tailwind
    </p>

</div>
