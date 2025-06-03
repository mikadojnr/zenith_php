<?php

namespace Core;

class Migration
{
    private Database $db;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/../database/migrations/';
        $this->createMigrationsTable();
    }

    private function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->query($sql);
    }

    public function run()
    {
        $executedMigrations = $this->getExecutedMigrations();
        $migrationFiles = $this->getMigrationFiles();

        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($migrationName, $executedMigrations)) {
                $this->executeMigration($file, $migrationName);
            }
        }
    }

    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable}");
        return array_column($stmt->fetchAll(), 'migration');
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '*.php');
        sort($files);
        return $files;
    }

    private function executeMigration(string $file, string $migrationName)
    {
        require_once $file;
        
        $className = $this->getClassNameFromFile($file);
        if (class_exists($className)) {
            $migration = new $className();
            $migration->up();
            
            // Record the migration
            $this->db->query(
                "INSERT INTO {$this->migrationsTable} (migration) VALUES (?)",
                [$migrationName]
            );
            
            echo "Executed migration: $migrationName\n";
        }
    }

    private function getClassNameFromFile(string $file): string
    {
        $content = file_get_contents($file);
        preg_match('/class\s+([^\s]+)/', $content, $matches);
        return $matches[1] ?? '';
    }
}