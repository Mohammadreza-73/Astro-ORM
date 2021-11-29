<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;

class PDOQueryBuilderTest extends TestCase
{
    private $queryBuilder;

    public function setUp(): void
    {
        $this->queryBuilder = new PDOQueryBuilder();

        PDOQueryBuilder::beginTransaction();
        parent::setUp();
    }

    public function testItCanCreateData(): void
    {        
        $result = $this->insertIntoDb();
        
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testItCanUpdateData(): void
    {
        $this->insertIntoDb();

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'Mohammadreza')
            ->where('skill', 'PHP')
            ->update([
                'skill' => 'Javascript', 
                'name' => 'Ali', 
                'email' => 'ali@gmail.com'
            ]);

        $this->assertEquals(1, $result);
    }

    public function testItCanUpdateDataWithMultipleWhere(): void
    {
        $this->insertIntoDb();
        $this->insertIntoDb(['name' => 'Ali']);

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'Ali')
            ->update(['skill' => 'Javascript']);

        $this->assertEquals(1, $result);
    }

    public function testItCanDeleteRecord(): void
    {
        $this->multipleInsertIntoDb(5);

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'Mohammadreza')
            ->delete();

        $this->assertEquals(5, $result);
    }

    private function insertIntoDb(array $data = []): int
    {
        $data = array_merge([
            'name' => 'Mohammadreza',
            'email' => 'rahimi93@yahoo.com',
            'skill' => 'PHP'
        ], $data);

        return PDOQueryBuilder::table('users')->create($data);
    }

    private function multipleInsertIntoDb(int $count, array $data = []): int
    {
        for ($i = 0; $i < $count; $i++) {
            $this->insertIntoDb($data);
        }

        return $count; // Number of inserted rows in database
    }

    public function tearDown(): void
    {
        // PDOQueryBuilder::truncateAllTable();

        PDOQueryBuilder::rollback();
        parent::tearDown();
    }
}