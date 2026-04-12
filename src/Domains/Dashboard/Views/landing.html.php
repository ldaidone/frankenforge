<div class="max-w-4xl mx-auto p-8">
    <h1 class="text-6xl font-bold text-orange-500 mb-4">🔥 FrankenForge</h1>
    <p class="text-2xl mb-8">The monster is alive.</p>

    <div class="mb-6">
        <a href="/demo" class="text-orange-400 hover:text-orange-300 underline">→ View Interactive Demo</a>
    </div>

    <div id="ping-result"
         hx-ext="sse"
         sse-connect="/api/ping"
         sse-swap="message"
         class="bg-zinc-900 border border-orange-500/30 rounded-xl p-6 text-center min-h-[60px] flex items-center justify-center">
        <span class="text-green-400 font-bold">Waiting for connection...</span>
    </div>
</div>
