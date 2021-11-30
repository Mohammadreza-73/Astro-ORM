<?php

namespace Tests\Functional;

use App\Helpers\HttpClient;
use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;

class CrudTest extends TestCase
{
    private $queryBuilder;
    private $httpClient;

    public function setUp(): void
    {
        $this->queryBuilder = new PDOQueryBuilder();
        $this->httpClient   = new HttpClient();
        
        parent::setUp();
    }

    public function testItCanCreateDataWithApi()
    {
        $data = [
            'json' => [
                'name' => 'Api',
                'email' => 'api@gmail.com',
                'skill' => 'restfull'
            ]
        ];

        $response = $this->httpClient->post('index.php', $data);
        $this->assertEquals(200, $response->getStatusCode());

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'Api')
            ->where('skill', 'restfull')
            ->first();

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * @depends testItCanCreateDataWithApi
     */
    public function testItCanUpdateDataWithApi($result): void
    {
        $data = [
            'json' => [
                'id' => $result->id,
                'name' => 'api for update'
            ]
        ];

        $response = $this->httpClient->put('index.php', $data);
        $this->assertEquals(200, $response->getStatusCode());

        $response = PDOQueryBuilder::table('users')
            ->find($result->id);

        $this->assertNotNull($response);
        $this->assertEquals('api for update', $response->name);
    }

    public function tearDown(): void
    {
        $this->httpClient = null;

        parent::tearDown();
    }
}