<?php

namespace App\Database;

use PDO;
use App\Traits\HasConfig;
use App\Database\PDODatabaseConnection;

class PDOQueryBuilder
{
    private ?string $conditions = null;
    private array $values;
    private string $operator;
    private array $order;
    private int $limit;
    private $stmt;
    
    private static $pdo;
    private static $table;

    use HasConfig;
    
    public function __construct()
    {
        $config = $this->getConfigs('database', 'pdo_testing');
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
        $this->values = array_values($data);

        $sql = "INSERT INTO " . self::$table . " ({$fields}) VALUES({$placeHolder})";
        $this->execute($sql, $this->values);

        return (int) self::$pdo->lastInsertId();
    }

    public function where(string $column, string $value, string $operator = '='): self
    {
        if (is_null($this->conditions)) {
            $this->conditions = "{$column}{$operator}?";
        } else {
            $this->conditions .= " AND {$column}{$operator}?";
        }
        
        $this->operator = $operator;
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

        /** 
         * Make array of params for sanitization and
         * be valued in execute method.
         * 
         * First of all use update values
         * then use condition values
         */
        $params = array_merge($params, $this->values);
        
        $sql = "UPDATE " . self::$table . " SET {$fields} WHERE {$this->conditions}";
        $this->execute($sql, $params);
                
        return $this->stmt->rowCount();
    }

    public function delete(): int
    {
        $sql = "DELETE FROM " . self::$table . " WHERE {$this->conditions}";
        $this->execute($sql, $this->values);

        return $this->stmt->rowCount();
    }

    public function get(array $columns = ['*']): array
    {
        $columns = implode(', ', $columns);

        $conditions = '';
        if (isset($this->conditions)) {
            $conditions = " WHERE {$this->conditions}";
        }

        $order = '';
        if (isset($this->order)) {
            $orderColumn = $this->order['column'];
            $sort = $this->order['sort'];

            $order = " ORDER BY {$orderColumn} {$sort}";
        }

        $limit = '';
        if (isset($this->limit)) {
            $limit = " LIMIT {$this->limit}";
        }

        $sql = "SELECT {$columns} FROM " . self::$table . "{$conditions}{$order}{$limit}";
        $this->execute($sql, $this->values ?? []);
        
        return $this->stmt->fetchAll();
    }

    public function first(array $columns = ['*'])
    {
        $data = $this->get($columns);

        return empty($data) ? null : $data[0];
    }

    public function find(int $id)
    {
        return $this->where('id', $id)->first();
    }

    public function findBy(string $column, mixed $value)
    {
        return $this->where($column, $value)->first();
    }

    public function orderBy(string $column, string $sort = 'ASC'): self
    {
        $this->order = [
            'column' => $this->orderColumn = $column,
            'sort' => $this->sort = $sort
        ];
        
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
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

    private function execute(string $sql, mixed $params = '')
    {
        $this->stmt = self::$pdo->prepare($sql);
        $this->stmt->execute($params);
        $this->values = [];

        return $this;
    }
}