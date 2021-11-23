<?php

namespace Tests\Unit;

use PDO;
use App\Helpers\Config;
use PHPUnit\Framework\TestCase;
use App\Database\PDODatabaseConnection;
use App\Contracts\DatabaseConnectionInterface;
use App\Exceptions\PDODatabaseConnectionException;

class PDODatabaseConnectionTest extends TestCase
{
    public function testPDODatabaseConnectionImplementsDatabaseConnectionInterface(): void
    {
        $config = Config::get('database', 'pdo_testing');
        $pdoConnection = new PDODatabaseConnection($config);
        
        $this->assertInstanceOf(DatabaseConnectionInterface::class, $pdoConnection,);
    }

    public function testConnectMethodShouldReturnsValidInstance()
    {
        $config = Config::get('database', 'pdo_testing');
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

        $config = Config::get('database', 'pdo_testing');
        $config['db_name'] = 'dummy';
        $pdoConnection = new PDODatabaseConnection($config);
        $pdoConnection->connect();
    }
}