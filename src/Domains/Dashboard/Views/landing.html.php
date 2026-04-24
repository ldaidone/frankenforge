<div class="max-w-4xl mx-auto p-8">
    <h1 class="text-6xl font-bold text-orange-500 mb-4">🔥 FrankenForge</h1>
    <p class="text-2xl mb-8" style="color: var(--app-text)">The monster is alive.</p>

    <div class="flex flex-wrap gap-3 mb-6">
        <a href="/demo" class="px-4 py-2 rounded-lg text-sm font-bold transition text-white"
           style="background: var(--app-section)"
           onmouseover="this.style.background='var(--app-section-hover)'"
           onmouseout="this.style.background='var(--app-section)'">
            → Interactive Demo
        </a>
        <a href="/dashboard" class="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-sm font-bold transition">
            Open Dashboard →
        </a>
    </div>

    <div id="ping-result"
         hx-ext="sse"
         sse-connect="/api/ping"
         sse-swap="message"
         class="rounded-xl p-6 text-center min-h-[60px] flex items-center justify-center"
         style="background: var(--app-section); border: 1px solid var(--app-section-border)">
        <span class="text-green-400 font-bold">Waiting for connection...</span>
    </div>
</div>
