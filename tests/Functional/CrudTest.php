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



    public function tearDown(): void
    {
        $this->httpClient = null;

        parent::tearDown();
    }
}