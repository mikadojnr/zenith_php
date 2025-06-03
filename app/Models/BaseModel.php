<?php

namespace App\Models;

use Core\Database;

abstract class BaseModel
{
    protected static string $table;
    protected array $attributes = [];
    protected Database $db;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->db = Database::getInstance();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->find(static::$table, $id);
    }

    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->all(static::$table);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        return $db->create(static::$table, $data);
    }

    public function update(array $data): bool
    {
        return $this->db->update(static::$table, $this->id, $data);
    }

    public function delete(): bool
    {
        return $this->db->delete(static::$table, $this->id);
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
}