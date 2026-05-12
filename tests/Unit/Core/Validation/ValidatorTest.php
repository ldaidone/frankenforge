<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Tests\Unit\Core\Validation;

use FrankenForge\Core\Validation\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    #[Test]
    public function it_passes_when_all_rules_are_satisfied(): void
    {
        $v = Validator::make([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'age' => '25',
        ], [
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email'],
            'age' => ['required', 'integer', 'min:18'],
        ]);

        self::assertTrue($v->passes());
        self::assertFalse($v->fails());
        self::assertSame([], $v->errors());
    }

    #[Test]
    public function it_catches_required_field_missing(): void
    {
        $v = Validator::make([], [
            'email' => ['required'],
        ]);

        self::assertTrue($v->fails());
        self::assertArrayHasKey('email', $v->errors());
    }

    #[Test]
    public function it_catches_invalid_email(): void
    {
        $v = Validator::make(['email' => 'not-an-email'], [
            'email' => ['required', 'email'],
        ]);

        self::assertTrue($v->fails());
        self::assertStringContainsString('email', $v->errors()['email']);
    }

    #[Test]
    public function it_enforces_min_length(): void
    {
        $v = Validator::make(['name' => 'Ab'], [
            'name' => ['required', 'min:3'],
        ]);

        self::assertTrue($v->fails());
    }

    #[Test]
    public function it_enforces_max_length(): void
    {
        $v = Validator::make(['name' => 'This is a very long name'], [
            'name' => ['required', 'max:5'],
        ]);

        self::assertTrue($v->fails());
    }

    #[Test]
    public function it_validates_integer_type(): void
    {
        $v = Validator::make(['age' => 'abc'], [
            'age' => ['integer'],
        ]);

        self::assertTrue($v->fails());
        self::assertArrayHasKey('age', $v->errors());
    }

    #[Test]
    public function it_validates_in_values(): void
    {
        $v = Validator::make(['role' => 'hacker'], [
            'role' => ['required', 'in:admin,user,editor'],
        ]);

        self::assertTrue($v->fails());
    }

    #[Test]
    public function it_allows_optional_fields_when_not_required(): void
    {
        $v = Validator::make(['name' => 'Alice'], [
            'name' => ['required', 'min:3'],
            'bio' => ['string', 'max:500'],
        ]);

        self::assertTrue($v->passes());
    }

    #[Test]
    public function it_supports_closure_rules(): void
    {
        $v = Validator::make(['username' => 'admin'], [
            'username' => [
                'required',
                fn($v) => $v === 'admin' ? 'Username is reserved' : null,
            ],
        ]);

        self::assertTrue($v->fails());
        self::assertSame('Username is reserved', $v->errors()['username']);
    }

    #[Test]
    public function it_supports_custom_error_messages(): void
    {
        $v = Validator::make(['email' => ''], [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'We need your email to sign up',
        ]);

        self::assertTrue($v->fails());
        self::assertSame('We need your email to sign up', $v->errors()['email']);
    }

    #[Test]
    public function it_throws_when_validated_called_on_failure(): void
    {
        $v = Validator::make(['age' => 'abc'], [
            'age' => ['integer'],
        ]);

        $this->expectException(\RuntimeException::class);
        $v->validated();
    }

    #[Test]
    public function it_returns_validated_data_on_success(): void
    {
        $v = Validator::make(['name' => 'Alice', 'age' => '30'], [
            'name' => ['required'],
            'age' => ['integer'],
        ]);

        self::assertSame(['name' => 'Alice', 'age' => '30'], $v->validated());
    }

    #[Test]
    public function it_validates_matches_rule(): void
    {
        $v = Validator::make([
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ], [
            'password' => ['required'],
            'password_confirmation' => ['required', 'matches:password'],
        ]);

        self::assertTrue($v->fails());
    }

    #[Test]
    public function it_validates_regex_rule(): void
    {
        $v = Validator::make(['slug' => 'Invalid Slug!'], [
            'slug' => ['required', 'regex:/^[a-z0-9-]+$/'],
        ]);

        self::assertTrue($v->fails());
    }

    #[Test]
    public function it_validates_url_rule(): void
    {
        $v = Validator::make(['website' => 'not-a-url'], [
            'website' => ['url'],
        ]);

        self::assertTrue($v->fails());
    }

    #[Test]
    public function it_validates_uuid_rule(): void
    {
        $v = Validator::make(['id' => 'not-a-uuid'], [
            'id' => ['uuid'],
        ]);

        self::assertTrue($v->fails());
    }
}
