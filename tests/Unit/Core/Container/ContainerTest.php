<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Tests\Unit\Core\Container;

use FrankenForge\Core\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContainerTest extends TestCase
{
    #[Test]
    public function it_binds_and_resolves_a_service_via_set_and_get(): void
    {
        $c = new Container();
        $c->set('db', new \stdClass());

        self::assertSame($c->get('db'), $c->get('db'));
    }

    #[Test]
    public function it_returns_cached_instance_on_multiple_gets(): void
    {
        $c = new Container();
        $counter = 0;
        $c->factory('service', function () use (&$counter) {
            $counter++;
            return new \stdClass();
        });

        $c->get('service');
        $c->get('service');

        self::assertSame(1, $counter);
    }

    #[Test]
    public function it_resolves_via_factory_make_returns_fresh_instance(): void
    {
        $c = new Container();
        $c->factory('fresh', fn() => new \stdClass());

        $a = $c->make('fresh');
        $b = $c->make('fresh');

        self::assertNotSame($a, $b);
    }

    #[Test]
    public function make_throws_when_no_factory_registered(): void
    {
        $c = new Container();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No factory registered for [missing]');
        $c->make('missing');
    }

    #[Test]
    public function get_throws_when_no_service_or_factory_registered(): void
    {
        $c = new Container();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No service or factory registered for [missing]');
        $c->get('missing');
    }

    #[Test]
    public function get_wraps_factory_exception(): void
    {
        $c = new Container();
        $c->factory('broken', function () {
            throw new \InvalidArgumentException('inner error');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve service');
        $c->get('broken');
    }

    #[Test]
    public function factory_receives_container_instance(): void
    {
        $c = new Container();
        $c->factory('dep', fn() => new \stdClass());

        $c->factory('parent', function (Container $container) {
            return $container->get('dep');
        });

        self::assertInstanceOf(\stdClass::class, $c->get('parent'));
    }

    #[Test]
    public function has_returns_true_for_registered_service(): void
    {
        $c = new Container();
        $c->set('foo', 'bar');

        self::assertTrue($c->has('foo'));
    }

    #[Test]
    public function has_returns_true_for_registered_factory(): void
    {
        $c = new Container();
        $c->factory('baz', fn() => 'qux');

        self::assertTrue($c->has('baz'));
    }

    #[Test]
    public function has_returns_false_for_unregistered_id(): void
    {
        $c = new Container();

        self::assertFalse($c->has('nothing'));
    }

    #[Test]
    public function get_after_factory_caches_the_instance(): void
    {
        $c = new Container();
        $c->factory('single', fn() => new \stdClass());

        $first = $c->get('single');
        $second = $c->get('single');

        self::assertSame($first, $second);
    }

    #[Test]
    public function set_overrides_factory_of_same_id(): void
    {
        $c = new Container();
        $c->factory('svc', fn() => 'from-factory');
        $c->set('svc', 'from-set');

        self::assertSame('from-set', $c->get('svc'));
    }
}
