<?php

declare(strict_types=1);

/**
 * Flash messages component.
 *
 * Renders bootstrap-style alerts from session flash data.
 *
 * @param array<array{type:string, message:string}> $flash
 */
$flash ??= [];
?>

<?php foreach ($flash as $f): ?>
<div class="mb-4 p-4 rounded-lg flex items-center gap-3 animate-fade-in"
     hx-swap=" settle 0.3s"
     style="background: <?= match($f['type']) {
         'success' => 'rgba(34, 197, 94, 0.15)',
         'error' => 'rgba(239, 68, 68, 0.15)',
         'warning' => 'rgba(250, 204, 21, 0.15)',
         default => 'rgba(59, 130, 246, 0.15)',
     } ?>; border: 1px solid <?= match($f['type']) {
         'success' => 'rgba(34, 197, 94, 0.3)',
         'error' => 'rgba(239, 68, 68, 0.3)',
         'warning' => 'rgba(250, 204, 21, 0.3)',
         default => 'rgba(59, 130, 246, 0.3)',
     } ?>">
    <i class="fa-solid <?= match($f['type']) {
        'success' => 'fa-check-circle text-green-400',
        'error' => 'fa-xmark-circle text-red-400',
        'warning' => 'fa-exclamation-triangle text-yellow-400',
        default => 'fa-info-circle text-blue-400',
    } ?>"></i>
    <span style="color: var(--app-text)"><?= htmlspecialchars($f['message']) ?></span>
</div>
<?php endforeach; ?>