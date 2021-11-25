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
    
    public function testPDODatabaseConnectionImplementsDatabaseConnectionInterface(): void
    {
        $config = $this->getConfig();
        $pdoConnection = new PDODatabaseConnection($config);
        
        $this->assertInstanceOf(DatabaseConnectionInterface::class, $pdoConnection,);
    }

    public function testConnectMethodShouldReturnsValidInstance()
    {
        $config = $this->getConfig();
        $pdoConnection = new PDODatabaseConnection($config);
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

        $config = $this->getConfig();
        $config['db_name'] = 'dummy';
        $pdoConnection = new PDODatabaseConnection($config);
        $pdoConnection->connect();
    }

    public function testRecivedConfigHaveRequiredKeys(): void
    {
        $this->expectException(ConfigNotValidException::class);
        $this->expectExceptionMessage('Database Config in not valid.');

        $config = $this->getConfig();
        unset($config['db_name']);
        $pdoConnection = new PDODatabaseConnection($config);
        $pdoConnection->connect();
    }
}