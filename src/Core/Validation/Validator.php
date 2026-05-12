<?php
/**
 * FrankenForge — FrankenForge\Core\Validation
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
declare(strict_types=1);

namespace FrankenForge\Core\Validation;

/**
 * Simple array-based request validator.
 *
 * Usage:
 *   $v = Validator::make($request->all(), [
 *       'email' => ['required', 'email'],
 *       'name'  => ['required', 'min:3', 'max:255'],
 *       'age'   => ['integer', 'min:18'],
 *       'role'  => ['in:admin,user,editor'],
 *       'slug'  => [fn($v) => preg_match('/^[a-z0-9-]+$/', $v) ? null : 'Invalid slug'],
 *   ]);
 *
 *   if ($v->fails()) { return $jsonResponder->error('Validation failed', 422, $v->errors()); }
 */
final class Validator
{
    /** @var array<string, mixed> */
    private array $data;

    /** @var array<string, array<callable|string>> */
    private array $rules;

    /** @var array<string, string> */
    private array $errors = [];

    /** @var array<string, string> Custom error messages keyed as "field.rule" */
    private array $messages;

    /**
     * @param array<string, mixed> $data
     * @param array<string, array<callable|string>> $rules
     * @param array<string, string> $messages
     */
    private function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, array<callable|string>> $rules
     * @param array<string, string> $messages  Custom messages keyed as "field.rule"
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        $v = new self($data, $rules, $messages);
        $v->validate();

        return $v;
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        if ($this->fails()) {
            throw new \RuntimeException('Cannot call validated() when validation has failed');
        }

        return $this->data;
    }

    /**
     * Validate the data against the rules and populate errors.
     */
    private function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                if (is_callable($rule)) {
                    $error = $rule($value, $this->data);
                    if ($error !== null) {
                        $this->addError($field, $error);
                    }
                    continue;
                }

                $error = $this->evaluateRule($field, $rule, $value);
                if ($error !== null) {
                    $this->addError($field, $error);
                }
            }
        }
    }

    /**
     * Evaluate a single validation rule for a field.
     *
     * @param string $field The name of the field being validated
     * @param string $rule The validation rule (e.g. "required", "min:3")
     * @param mixed $value The value of the field to validate
     * @return string|null An error message if validation fails, or null if it passes
     */
    private function evaluateRule(string $field, string $rule, mixed $value): ?string
    {
        // Parse rule:name into rule + parameter
        [$name, $param] = str_contains($rule, ':')
            ? explode(':', $rule, 2)
            : [$rule, null];

        $message = $this->messages["{$field}.{$name}"]
            ?? $this->messages[$name]
            ?? null;

        return match ($name) {
            'required' => $this->ruleRequired($field, $value, $message),
            'email' => $this->ruleEmail($field, $value, $message),
            'integer' => $this->ruleInteger($field, $value, $message),
            'numeric' => $this->ruleNumeric($field, $value, $message),
            'string' => $this->ruleString($field, $value, $message),
            'bool' => $this->ruleBool($field, $value, $message),
            'min' => $this->ruleMin($field, $value, $param, $message),
            'max' => $this->ruleMax($field, $value, $param, $message),
            'in' => $this->ruleIn($field, $value, $param, $message),
            'not_in' => $this->ruleNotIn($field, $value, $param, $message),
            'regex' => $this->ruleRegex($field, $value, $param, $message),
            'matches' => $this->ruleMatches($field, $value, $param, $message),
            'url' => $this->ruleUrl($field, $value, $message),
            'uuid' => $this->ruleUuid($field, $value, $message),
            default => null,
        };
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleRequired(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return $message ?? "{$field} is required";
        }
        if (is_array($value) && $value === []) {
            return $message ?? "{$field} is required";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleEmail(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
            return $message ?? "{$field} must be a valid email";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleInteger(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return $message ?? "{$field} must be an integer";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleNumeric(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return $message ?? "{$field} must be numeric";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleString(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value)) {
            return $message ?? "{$field} must be a string";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleBool(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
            return $message ?? "{$field} must be a boolean";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $param
     * @param string|null $message
     * @return string|null
     */
    private function ruleMin(string $field, mixed $value, ?string $param, ?string $message): ?string
    {
        if ($value === null || $value === '' || $param === null) {
            return null;
        }
        $min = (int) $param;

        $length = is_numeric($value) ? (int) $value : (is_array($value) ? count($value) : strlen((string) $value));
        if ($length < $min) {
            return $message ?? "{$field} must be at least {$min}";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $param
     * @param string|null $message
     * @return string|null
     */
    private function ruleMax(string $field, mixed $value, ?string $param, ?string $message): ?string
    {
        if ($value === null || $value === '' || $param === null) {
            return null;
        }
        $max = (int) $param;

        $length = is_numeric($value) ? (int) $value : (is_array($value) ? count($value) : strlen((string) $value));
        if ($length > $max) {
            return $message ?? "{$field} must not exceed {$max}";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $param
     * @param string|null $message
     * @return string|null
     */
    private function ruleIn(string $field, mixed $value, ?string $param, ?string $message): ?string
    {
        if ($value === null || $value === '' || $param === null) {
            return null;
        }
        $allowed = explode(',', $param);
        if (!in_array((string) $value, $allowed, true)) {
            return $message ?? "{$field} must be one of: {$param}";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $param
     * @param string|null $message
     * @return string|null
     */
    private function ruleNotIn(string $field, mixed $value, ?string $param, ?string $message): ?string
    {
        if ($value === null || $value === '' || $param === null) {
            return null;
        }
        $disallowed = explode(',', $param);
        if (in_array((string) $value, $disallowed, true)) {
            return $message ?? "{$field} is not allowed";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $param
     * @param string|null $message
     * @return string|null
     */
    private function ruleRegex(string $field, mixed $value, ?string $param, ?string $message): ?string
    {
        if ($value === null || $value === '' || $param === null) {
            return null;
        }
        if (!preg_match($param, (string) $value)) {
            return $message ?? "{$field} format is invalid";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $param
     * @param string|null $message
     * @return string|null
     */
    private function ruleMatches(string $field, mixed $value, ?string $param, ?string $message): ?string
    {
        if ($value === null || $value === '' || $param === null) {
            return null;
        }
        $other = $this->data[$param] ?? null;
        if ($value !== $other) {
            return $message ?? "{$field} must match {$param}";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleUrl(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!filter_var((string) $value, FILTER_VALIDATE_URL)) {
            return $message ?? "{$field} must be a valid URL";
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string|null $message
     * @return string|null
     */
    private function ruleUuid(string $field, mixed $value, ?string $message): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', (string) $value)) {
            return $message ?? "{$field} must be a valid UUID";
        }
        return null;
    }

    /**
     * @param string $field
     * @param string $message
     * @return void
     */
    private function addFieldError(string $field, string $message): void
    {
        // First error wins — consistent with most validation libraries
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    /**
     * @param string $field
     * @param string $message
     * @return void
     */
    private function addError(string $field, string $message): void
    {
        $this->addFieldError($field, $message);
    }
}
