<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Shared\Infrastructure\Database;

use FrankenForge\Core\Logging\FileLogger;
use FrankenForge\Domains\Admin\Entities\AdminUser;
use FrankenForge\Domains\Admin\Repositories\AdminUserRepositoryInterface;
use FrankenForge\Domains\Admin\Services\PasswordHasher;

final class SqliteAdminUserRepository implements AdminUserRepositoryInterface
{
    private const string TABLE = 'users';
    private $logger;

    public function __construct(
        private readonly Connection $db,
        private readonly PasswordHasher $hasher,
    ) {
        $this->logger = new FileLogger(__DIR__ . '/../../../../storage/app.log');
    }

    public function findAll(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY name");
        return array_map(fn(array $row) => $this->toEntity($row), $rows);
    }

    public function findById(string $id): ?AdminUser
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM " . self::TABLE . " WHERE id = :id",
            ['id' => $id],
        );

        return $row !== null ? $this->toEntity($row) : null;
    }

    public function findByEmail(string $email): ?AdminUser
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM " . self::TABLE . " WHERE email = :email",
            ['email' => $email],
        );

        return $row !== null ? $this->toEntity($row) : null;
    }

    public function save(AdminUser $user): void
    {
        $existing = $this->findById($user->id);

        if ($existing === null) {
            $this->db->insert(self::TABLE, $this->toRow($user));
        } else {
            $this->db->update(
                self::TABLE,
                $this->toRow($user),
                'id = :id',
                ['id' => $user->id],
            );
        }
    }

    public function updatePassword(string $id, string $hash): void
    {
        $this->db->update(
            self::TABLE,
            ['password_hash' => $hash],
            'id = :id',
            ['id' => $id],
        );
    }

    public function clearMustChangePassword(string $id): void
    {
        $this->db->update(
            self::TABLE,
            ['must_change_password' => 0],
            'id = :id',
            ['id' => $id],
        );
    }

    public function verifyPassword(string $password, AdminUser $user): bool
    {
        $hash = trim($user->passwordHash);
        if ($hash === '') {
            return false;
        }

        return $this->hasher->verify($password, $hash);
    }

    private function toEntity(array $row): AdminUser
    {
        return new AdminUser(
            id: $row['id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password_hash'] ?? '',
            mustChangePassword: (bool) ($row['must_change_password'] ?? 0),
            createdAt: new \DateTimeImmutable($row['created_at']),
        );
    }

    private function toRow(AdminUser $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password_hash' => $user->passwordHash,
            'must_change_password' => $user->mustChangePassword ? 1 : 0,
            'created_at' => $user->createdAt->format('c'),
        ];
    }
}
