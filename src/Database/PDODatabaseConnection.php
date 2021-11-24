<?php

namespace App\Database;

use PDO;
use App\Exceptions\ConfigNotValidException;
use App\Contracts\DatabaseConnectionInterface;
use App\Exceptions\PDODatabaseConnectionException;

class PDODatabaseConnection implements DatabaseConnectionInterface
{
 
    /**
     * Pdo instance
     *
     * @var PDO
     */
    private PDO $connection;

    /**
     * Database connection configs
     *
     * @var array
     */
    private array $config;

    /**
     * Database config required keys
     * 
     * @var array
     */
    private CONST REQUIRED_CONFIG_KEY = [
        'driver',
        'host',
        'db_name',
        'db_user',
        'db_password'
    ];

    public function __construct(array $config)
    {
        if (! $this->isConfigValid($config)) {
            throw new ConfigNotValidException('Database Config in not valid.');
        }

        $this->config = $config;
    }

    /**
     * Connect to database
     *
     * @throws PDODatabaseConnectionException
     * @return self
     */
    public function connect(): self
    {
        $dsn = $this->generateDsn($this->config);
        
        try {
            $this->connection = new PDO(...$dsn);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        } catch (\PDOException $e) {
            throw new PDODatabaseConnectionException($e->getMessage());
        }

        return $this;
    }
   
    /**
     * Returns database connection
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Generate pdo dsn, username, password
     *
     * @param  array $config
     * @return array Return pdo database configs
     */
    private function generateDsn(array $config): array
    {
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";

        return [
            $dsn,
            $config['db_user'],
            $config['db_password']
        ];
    }

    /**
     * Check config file validation
     *
     * @param  array $config
     * @return boolean
     */
    public function isConfigValid(array $config): bool
    {
        $matches = array_intersect(self::REQUIRED_CONFIG_KEY, array_keys($config));

        return count($matches) === count(self::REQUIRED_CONFIG_KEY);
    }
}