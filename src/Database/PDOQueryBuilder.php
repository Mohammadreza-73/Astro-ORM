<?php

namespace App\Database;

use PDO;
use App\Traits\HasConfig;
use App\Database\PDODatabaseConnection;

class PDOQueryBuilder
{
    private array $conditions;
    private array $values;
    private string $operator;

    
    private static $pdo;
    private static $table;

    use HasConfig;
    
    public function __construct()
    {
        $config = $this->getConfig();
        $pdoConnection = new PDODatabaseConnection($config);

        if (is_null(self::$pdo)) {
            self::$pdo = $pdoConnection->connect()->getConnection();
        }
    }

    public static function table(string $table): self
    {
        self::$table = $table;
        return new self;
    }

    public function create(array $data): int
    {
        $fields = [];
        $placeHolders = [];
        foreach ($data as $column => $value) {
            $fields[] = $column;
            $placeHolders[] = '?';
        }

        $fields = implode(', ', $fields);
        $placeHolder = implode(', ', $placeHolders);
        
        $sql = "INSERT INTO " . self::$table . " ({$fields}) VALUES({$placeHolder})";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) self::$pdo->lastInsertId();
    }

    public function where(string $column, string $value, string $operator = '='): self
    {
        $this->operator = $operator;
        $this->conditions[] = "{$column}{$operator}?";
        $this->values[] = $value;

        return $this;
    }

    public function update(array $data): int
    {
        $fields = [];
        $params = [];
        foreach ($data as $column => $value) {
            $fields[] = "{$column}=?";
            $params[] = $value;
        }
        
        $fields = implode(', ', $fields);
        $conditions = implode(' AND ', $this->conditions);

        /** 
         * Make array of params for sanitization and
         * be valued in execute method.
         * 
         * First of all use update values
         * then use condition values
         */
        $params = array_merge($params, $this->values);

        $sql = "UPDATE " . self::$table . " SET {$fields} WHERE {$conditions}";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params); // initialize parameters safety
                
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $conditions = implode(' AND ', $this->conditions);
        
        $sql = "DELETE FROM " . self::$table . " WHERE {$conditions}";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($this->values);

        return $stmt->rowCount();
    }

    public static function truncateAllTable(): void
    {
        $stmt = self::$pdo->prepare("SHOW TABLES");
        $stmt->execute();
        
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $table) {
            self::$pdo->prepare("TRUNCATE TABLE `{$table}`")->execute();
        }
    }

    public static function beginTransaction(): void
    {
        self::$pdo->beginTransaction();
    }

    public static function rollback(): void
    {
        self::$pdo->rollback();       
    }
}