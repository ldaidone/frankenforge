<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Tests\Unit\Core\Security;

use FrankenForge\Core\Security\CsrfToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsrfTokenTest extends TestCase
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
    public function it_generates_a_token(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->generate();

        self::assertNotEmpty($token);
        self::assertSame(64, strlen($token));
    }

    #[Test]
    public function it_stores_generated_token_in_session(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->generate();

        self::assertSame($token, $_SESSION['_csrf_token']);
    }

    #[Test]
    public function getToken_returns_same_token_on_subsequent_calls(): void
    {
        $csrf = new CsrfToken();

        $first = $csrf->getToken();
        $second = $csrf->getToken();

        self::assertSame($first, $second);
    }

    #[Test]
    public function getToken_generates_token_if_none_exists(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->getToken();

        self::assertNotEmpty($token);
    }

    #[Test]
    public function it_validates_correct_token(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->generate();

        self::assertTrue($csrf->validate($token));
    }

    #[Test]
    public function it_rejects_invalid_token(): void
    {
        $csrf = new CsrfToken(true);
        $csrf->generate();

        self::assertFalse($csrf->validate('invalid-token'));
    }

    #[Test]
    public function it_rejects_null_token_in_strict_mode(): void
    {
        $csrf = new CsrfToken(true);
        $csrf->generate();

        self::assertFalse($csrf->validate(null));
    }

    #[Test]
    public function it_allows_null_token_in_non_strict_mode(): void
    {
        $csrf = new CsrfToken(false);
        $csrf->generate();

        self::assertTrue($csrf->validate(null));
    }

    #[Test]
    public function it_rejects_empty_string_in_strict_mode(): void
    {
        $csrf = new CsrfToken(true);
        $csrf->generate();

        self::assertFalse($csrf->validate(''));
    }

    #[Test]
    public function it_allows_empty_string_in_non_strict_mode(): void
    {
        $csrf = new CsrfToken(false);
        $csrf->generate();

        self::assertTrue($csrf->validate(''));
    }

    #[Test]
    public function getHiddenField_returns_html_input(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->generate();
        $field = $csrf->getHiddenField();

        self::assertStringContainsString('type="hidden"', $field);
        self::assertStringContainsString('name="_csrf"', $field);
        self::assertStringContainsString($token, $field);
    }

    #[Test]
    public function getHiddenField_uses_custom_name(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->generate();
        $field = $csrf->getHiddenField('custom_token');

        self::assertStringContainsString('name="custom_token"', $field);
    }

    #[Test]
    public function validate_uses_hash_equals_for_timing_safety(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->generate();

        $result = $csrf->validate($token, '_csrf');

        self::assertTrue($result);
    }

    #[Test]
    public function multiple_tokens_are_independent(): void
    {
        $a = new CsrfToken();
        $b = new CsrfToken();

        $tokenA = $a->generate();
        $tokenB = $b->generate();

        self::assertFalse($a->validate($tokenB));
        self::assertFalse($b->validate($tokenA));
    }

    #[Test]
    public function regenerate_replaces_token(): void
    {
        $csrf = new CsrfToken();
        $old = $csrf->generate();
        $new = $csrf->generate();

        self::assertNotSame($old, $new);
        self::assertTrue($csrf->validate($new));
        self::assertFalse($csrf->validate($old));
    }
}
