<?php
/**
 * FrankenForge — FrankenForge\Core\Error
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */

/** @var array{code:int, title:string, message:string, backUrl:string} */
$code = $data['code'] ?? 500;
$title = $data['title'] ?? 'Error';
$message = $data['message'] ?? 'An error occurred.';
$backUrl = $data['backUrl'] ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — FrankenForge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen flex items-center justify-center">
    <div class="text-center max-w-md mx-auto px-6">
        <div class="text-8xl font-bold text-zinc-800 mb-4"><?= $code ?></div>
        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($title) ?></h1>
        <p class="text-zinc-400 mb-8"><?= htmlspecialchars($message) ?></p>
        <a href="<?= htmlspecialchars($backUrl) ?>"
           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg font-semibold transition">
            <i class="fa-solid fa-arrow-left"></i>
            Go Back
        </a>
    </div>
</body>
</html>
