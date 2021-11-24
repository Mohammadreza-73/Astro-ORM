<?php

namespace Tests\Unit;

use App\Helpers\Config;
use PHPUnit\Framework\TestCase;
use App\Exceptions\ConfigFileNotFoundException;

class ConfigTest extends TestCase
{
    public function testGetContentsFile(): void
    {
        $config = Config::getContentsFile('database');

        $this->assertIsArray($config);
    }

    public function testItThrowsExceptionIfConfigFileNotFound(): void
    {
        $this->expectException(ConfigFileNotFoundException::class);
        $this->expectExceptionMessage('Config File ["dummy"] does not exist.');

        Config::getContentsFile('dummy');
    }

    public function testGetMethodReturnsValidArray(): void
    {
        $config = Config::get('database', 'pdo_testing');
        $expectedData = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'db_name' => 'orm_testing',
            'db_user' => 'root',
            'db_password' => ''
        ];

        $this->assertEquals($expectedData, $config);
    }

    public function testGetMethodReturnsNullIfKeyIsInvalid(): void
    {
        $config = Config::get('database', 'dummy');

        $this->assertNull($config);
    }
}