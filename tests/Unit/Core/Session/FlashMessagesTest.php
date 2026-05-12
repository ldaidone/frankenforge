<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Tests\Unit\Core\Session;

use FrankenForge\Core\Session\FlashMessages;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FlashMessagesTest extends TestCase
{
    private array $storedSession;

    protected function setUp(): void
    {
        $this->storedSession = $_SESSION ?? [];
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->storedSession;
    }

    #[Test]
    public function it_stores_a_success_message(): void
    {
        $f = new FlashMessages();
        $f->success('Done!');

        $all = $f->all();
        self::assertCount(1, $all);
        self::assertSame('success', $all[0]['type']);
        self::assertSame('Done!', $all[0]['message']);
    }

    #[Test]
    public function it_stores_an_error_message(): void
    {
        $f = new FlashMessages();
        $f->error('Failed!');

        $all = $f->all();
        self::assertSame('error', $all[0]['type']);
        self::assertSame('Failed!', $all[0]['message']);
    }

    #[Test]
    public function it_stores_a_warning_message(): void
    {
        $f = new FlashMessages();
        $f->warning('Caution!');

        $all = $f->all();
        self::assertSame('warning', $all[0]['type']);
        self::assertSame('Caution!', $all[0]['message']);
    }

    #[Test]
    public function it_stores_an_info_message(): void
    {
        $f = new FlashMessages();
        $f->info('Heads up!');

        $all = $f->all();
        self::assertSame('info', $all[0]['type']);
        self::assertSame('Heads up!', $all[0]['message']);
    }

    #[Test]
    public function all_consumes_and_clears_messages(): void
    {
        $f = new FlashMessages();
        $f->success('msg');

        self::assertCount(1, $f->all());
        self::assertCount(0, $f->all());
    }

    #[Test]
    public function has_returns_true_when_messages_exist(): void
    {
        $f = new FlashMessages();
        $f->info('alert');

        self::assertTrue($f->has());
    }

    #[Test]
    public function has_returns_false_when_no_messages(): void
    {
        $f = new FlashMessages();

        self::assertFalse($f->has());
    }

    #[Test]
    public function clear_removes_all_messages(): void
    {
        $f = new FlashMessages();
        $f->success('one');
        $f->error('two');
        $f->clear();

        self::assertFalse($f->has());
        self::assertCount(0, $f->all());
    }

    #[Test]
    public function it_stores_multiple_messages(): void
    {
        $f = new FlashMessages();
        $f->success('A');
        $f->info('B');
        $f->error('C');

        $all = $f->all();
        self::assertCount(3, $all);
    }

    #[Test]
    public function set_and_pull_stores_arbitrary_values(): void
    {
        $f = new FlashMessages();
        $f->set('user_id', 42);

        self::assertSame(42, $f->pull('user_id'));
    }

    #[Test]
    public function pull_removes_value_after_reading(): void
    {
        $f = new FlashMessages();
        $f->set('key', 'value');

        $f->pull('key');
        self::assertNull($f->pull('key'));
    }

    #[Test]
    public function pull_returns_default_for_missing_key(): void
    {
        $f = new FlashMessages();

        self::assertSame('fallback', $f->pull('missing', 'fallback'));
    }

    #[Test]
    public function pull_returns_null_for_missing_key_without_default(): void
    {
        $f = new FlashMessages();

        self::assertNull($f->pull('missing'));
    }

    #[Test]
    public function all_returns_empty_array_when_no_messages(): void
    {
        $f = new FlashMessages();

        self::assertSame([], $f->all());
    }
}
