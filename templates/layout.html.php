<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon.png">
    <title><?= $title ?? 'FrankenForge' ?></title>
    <script src="https://unpkg.com/htmx.org@2"></script>
    <script src="https://unpkg.com/htmx-ext-sse@2.2.1/sse.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen font-mono">
    <?= $content ?>
</body>
</html>
