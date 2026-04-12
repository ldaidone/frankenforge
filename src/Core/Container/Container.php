<?php

declare(strict_types=1);

namespace FrankenForge\Core\Container;

use Closure;
use RuntimeException;
use Throwable;

final class Container
{
    /**
     * @var array<string, mixed>
     */
    private array $services = [];

    /**
     * @var array<string, Closure(self): mixed>
     */
    private array $factories = [];

    /**
     * Register an already-instantiated service.
     */
    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    /**
     * Register a factory closure that creates the service lazily on first access.
     *
     * The factory receives this container as its only argument, allowing it to
     * resolve other dependencies via $container->get(...).
     */
    public function factory(string $id, Closure $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * Resolve a service by id. Returns a cached instance if already created,
     * otherwise invokes the factory, caches the result, and returns it.
     *
     * @template T
     * @param class-string<T>|string $id
     * @return ($id is class-string ? T : mixed)
     * @throws RuntimeException if no service or factory is registered for the given id
     */
    public function get(string $id): mixed
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new RuntimeException("No service or factory registered for [{$id}].");
        }

        try {
            $service = ($this->factories[$id])($this);
        } catch (Throwable $e) {
            throw new RuntimeException("Failed to resolve service [{$id}]: {$e->getMessage()}", previous: $e);
        }

        $this->services[$id] = $service;

        return $service;
    }

    /**
     * Check if a service or factory is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }
}
