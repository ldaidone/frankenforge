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
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen font-mono">
    <?= $content ?>
</body>
</html>
