<?php

declare(strict_types=1);

namespace FrankenForge\Core;

/**
 * Simple in-memory demo state.
 *
 * Lives for the duration of the FrankenPHP worker process.
 * In a real app, this would be backed by a database.
 */
final class DemoState
{
    public int $counter = 0;

    /** @var array<array{id:string, label:string, enabled:bool}> */
    public array $toggles = [
        ['id' => 'dark_mode', 'label' => 'Dark Mode', 'enabled' => true],
        ['id' => 'notifications', 'label' => 'Notifications', 'enabled' => false],
        ['id' => 'analytics', 'label' => 'Analytics', 'enabled' => false],
    ];
}
