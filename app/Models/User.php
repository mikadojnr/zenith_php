<?php

namespace App\Models;

use Core\Database;

class User extends BaseModel
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        return $db->findBy(static::$table, ['email' => $email]);
    }

    public function getFullName(): string
    {
        return $this->name;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}