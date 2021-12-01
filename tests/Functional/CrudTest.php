<?php

namespace Tests\Functional;

use App\Helpers\HttpClient;
use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;

class CrudTest extends TestCase
{
    private $httpClient;

    public function setUp(): void
    {
        $this->httpClient = new HttpClient();
        
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
        $this->assertEquals(201, $response->getStatusCode());

        $user = PDOQueryBuilder::table('users')
            ->where('name', 'Api')
            ->where('skill', 'restfull')
            ->first();

        $this->assertNotNull($user);

        return $user;
    }

    /**
     * @depends testItCanCreateDataWithApi
     */
    public function testItCanUpdateDataWithApi($user): void
    {
        $data = [
            'json' => [
                'id' => $user->id,
                'name' => 'api for update'
            ]
        ];

        $response = $this->httpClient->put('index.php', $data);
        $this->assertEquals(200, $response->getStatusCode());

        $response = PDOQueryBuilder::table('users')
            ->find($user->id);

        $this->assertNotNull($response);
        $this->assertEquals('api for update', $response->name);
    }

    /**
     * @depends testItCanCreateDataWithApi
     */
    public function testItCanFetchDataWithApi($user): void
    {
        $response = $this->httpClient->get('index.php', [
            'json' => [
                'id' => $user->id
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('id', json_decode($response->getBody(), true));
    }

    public function tearDown(): void
    {
        $this->httpClient = null;

        parent::tearDown();
    }
}