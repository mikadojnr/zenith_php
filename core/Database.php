<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private static ?Database $instance = null;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $dsn = sprintf(
            "%s:host=%s;port=%s;dbname=%s;charset=utf8mb4",
            $_ENV['DB_CONNECTION'],
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_DATABASE']
        );

        try {
            $this->pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function find(string $table, int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM $table WHERE id = ?", [$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBy(string $table, array $conditions): ?array
    {
        $where = implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
        $stmt = $this->query("SELECT * FROM $table WHERE $where", array_values($conditions));
        return $stmt->fetch() ?: null;
    }

    public function all(string $table): array
    {
        $stmt = $this->query("SELECT * FROM $table");
        return $stmt->fetchAll();
    }

    public function create(string $table, array $data): int
    {
        $fields = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->pdo->prepare("INSERT INTO $table ($fields) VALUES ($placeholders)");
        $stmt->execute($data);
        
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, int $id, array $data): bool
    {
        $fields = implode(' = ?, ', array_keys($data)) . ' = ?';
        $stmt = $this->query("UPDATE $table SET $fields WHERE id = ?", [...array_values($data), $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $table, int $id): bool
    {
        $stmt = $this->query("DELETE FROM $table WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}