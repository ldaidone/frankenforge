<?php /* FrankenForge — frankenforge/kernel | Leo Daidone <leo.daidone@gmail.com> | Apache 2.0 */ ?>
<div class="px-6 pt-6">
    <a href="/" class="text-orange-500 hover:text-orange-400 text-sm underline font-medium">← Back</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-6 pb-6">
    <!-- Live Counter: demonstrates HTMX polling -->
    <div class="rounded-xl p-6" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
        <h2 class="text-lg font-bold text-orange-400 mb-4">⚡ Live Counter</h2>
        <div id="counter-display"
             hx-get="/api/counter"
             hx-trigger="every 2s"
             hx-swap="innerHTML"
             class="text-4xl font-mono text-green-400 tabular-nums">
            0
        </div>
        <div class="flex gap-2 mt-4">
            <button hx-post="/api/counter/increment"
                    hx-target="#counter-display"
                    hx-swap="innerHTML"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-sm font-bold text-white transition">
                + Increment
            </button>
            <button hx-post="/api/counter/reset"
                    hx-target="#counter-display"
                    hx-swap="innerHTML"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-bold text-white transition">
                Reset
            </button>
        </div>
    </div>

    <!-- Feature Toggle: demonstrates HTMX out-of-band swap -->
    <div class="rounded-xl p-6" style="background: var(--app-section); border: 1px solid var(--app-section-border)">
        <h2 class="text-lg font-bold text-orange-400 mb-4">🔧 Feature Toggles</h2>
        <div id="toggle-list"
             hx-get="/demo/toggles"
             hx-trigger="load"
             hx-swap="innerHTML"
             class="space-y-3">
            <span class="text-sm" style="color: var(--app-text-muted-on-section)">Loading...</span>
        </div>
    </div>
</div>
