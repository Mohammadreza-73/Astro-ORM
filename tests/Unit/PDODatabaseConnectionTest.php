<?php

namespace Tests\Unit;

use PDO;
use App\Traits\HasConfig;
use PHPUnit\Framework\TestCase;
use App\Database\PDODatabaseConnection;
use App\Exceptions\ConfigNotValidException;
use App\Contracts\DatabaseConnectionInterface;
use App\Exceptions\PDODatabaseConnectionException;

class PDODatabaseConnectionTest extends TestCase
{
    use HasConfig;

    private $config = [];

    public function setUp(): void
    {
        $this->config = $this->getConfigs('database', 'pdo_testing');

        parent::setUp();
    }
    
    public function testPDODatabaseConnectionImplementsDatabaseConnectionInterface(): void
    {
        
        $pdoConnection = new PDODatabaseConnection($this->config);
        
        $this->assertInstanceOf(DatabaseConnectionInterface::class, $pdoConnection);
    }

    public function testConnectMethodShouldReturnsValidInstance()
    {
        $pdoConnection = new PDODatabaseConnection($this->config);
        $pdoHandler = $pdoConnection->connect();

        $this->assertInstanceOf(PDODatabaseConnection::class, $pdoHandler);
        return $pdoHandler;
    }

    /**
     * @depends testConnectMethodShouldReturnsValidInstance
     */
    public function testConnectMethodShouldBeConnectToDatabase($pdoHandler): void
    {
        $this->assertInstanceOf(PDO::class, $pdoHandler->getConnection());
    }

    public function testItThrowsExceptionIfConfigIsInvalid(): void
    {
        $this->expectException(PDODatabaseConnectionException::class);

        $this->config['db_name'] = 'dummy';
        $pdoConnection = new PDODatabaseConnection($this->config);
        $pdoConnection->connect();
    }

    public function testRecivedConfigHaveRequiredKeys(): void
    {
        $this->expectException(ConfigNotValidException::class);
        $this->expectExceptionMessage('Database Config in not valid.');

        unset($this->config['db_name']);
        $pdoConnection = new PDODatabaseConnection($this->config);
        $pdoConnection->connect();
    }
}