<?php

declare(strict_types=1);

namespace FrankenForge\Shared\Infrastructure\Database;

use FrankenForge\Domains\Dashboard\Entities\User;
use FrankenForge\Domains\Dashboard\Repositories\UserRepositoryInterface;

final class SqliteUserRepository implements UserRepositoryInterface
{
    private const TABLE = 'users';

    public function __construct(
        private readonly Connection $db,
    ) {}

    public function findAll(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY name");

        return array_map(fn(array $row) => $this->toEntity($row), $rows);
    }

    public function findById(string $id): ?User
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM " . self::TABLE . " WHERE id = :id",
            ['id' => $id],
        );

        if ($row === null) {
            return null;
        }

        return $this->toEntity($row);
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM " . self::TABLE . " WHERE email = :email",
            ['email' => $email],
        );

        if ($row === null) {
            return null;
        }

        return $this->toEntity($row);
    }

    public function save(User $user): void
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

    public function delete(string $id): void
    {
        $this->db->delete(self::TABLE, 'id = :id', ['id' => $id]);
    }

    private function toEntity(array $row): User
    {
        return new User(
            id: $row['id'],
            name: $row['name'],
            email: $row['email'],
            role: $row['role'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            lastLoginAt: $row['last_login_at'] !== null ? new \DateTimeImmutable($row['last_login_at']) : null,
        );
    }

    private function toRow(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->createdAt->format('c'),
            'last_login_at' => $user->lastLoginAt?->format('c'),
        ];
    }
}