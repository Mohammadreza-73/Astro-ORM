<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;
use App\Exceptions\RecordNotFoundException;
use PDO;

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
        $this->insertIntoDb([
            'name' => 'Ali',
            'skill' => "JS"
        ]);

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'Ali')
            ->where('skill', 'JS')
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

    public function testItCanGetData(): void
    {
        $this->multipleInsertIntoDb(10);
        $this->multipleInsertIntoDb(10, [
            'name' => 'Ali',
            'skill' => 'Javascript'
        ]);

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'Ali')
            ->where('skill', 'Javascript')
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(10, $result);
    }

    public function testItCanGetSpecificColumns(): void
    {
        $this->multipleInsertIntoDb(10);

        $result = PDOQueryBuilder::table('users')
            ->where('skill', 'PHP')
            ->get(['name', 'skill']); // Get specific columns

        $this->assertIsArray($result);
        $this->assertObjectHasAttribute('name', $result[0]);
        $this->assertObjectHasAttribute('skill', $result[0]);

        /** Tips: Convert object to array */
        $result = json_decode(json_encode($result[0]), true);
        /** Expected object has to include only specific columns */
        $this->assertEquals(['name', 'skill'], array_keys($result));
    }

    public function testItCanGetFirstRow(): void
    {
        $this->multipleInsertIntoDb(2, ['name' => 'First Row']);
        $this->multipleInsertIntoDb(10, ['name' => 'Another Row']);

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'First Row')
            ->first();
            
        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('id', $result);
        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('email', $result);
        $this->assertObjectHasAttribute('skill', $result);
    }

    public function testItCanFindWithId(): void
    {
        $this->insertIntoDb();
        $id = $this->insertIntoDb(['name' => 'Row for find']);

        $result = PDOQueryBuilder::table('users')
            ->find($id);

        $this->assertIsObject($result);
        $this->assertEquals($id, $result->id);
    }

    public function testItCanFindBy(): void
    {
        $this->insertIntoDb();
        $id = $this->insertIntoDb(['name' => 'Row for find by']);

        $result = PDOQueryBuilder::table('users')
            ->findBy('name', 'Row for find by');

        $this->assertIsObject($result);
        $this->assertEquals($id, $result->id);
    }

    public function testItCanGetOrderdData(): void
    {
        $this->multipleInsertIntoDb(10, ['skill' => 'Laravel']);
        $this->insertIntoDb(['skill' => 'Zend']);
        
        $result = PDOQueryBuilder::table('users')
            ->orderBy('skill', 'DESC')
            ->get();
        
        $this->assertIsArray($result);
        $this->assertEquals('Zend', $result[0]->skill); // First row
    }

    public function testItCanLimitData(): void
    {
        $this->multipleInsertIntoDb(10);

        $result = PDOQueryBuilder::table('users')
            ->limit(5)
            ->get();
        
        $this->assertCount(5, $result);
    }

    public function testItCanReturnsEmptyArrayWhenRecordNotFound(): void
    {
        $this->multipleInsertIntoDb(5);

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'dummy')
            ->get();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testItCanReturnsNullWhenFirstRecordNotFound(): void
    {
        $this->multipleInsertIntoDb(5);

        $result = PDOQueryBuilder::table('users')
            ->where('name', 'dummy')
            ->first();

        $this->assertNull($result);
    }

    public function testItCanReturnsZeroWhenRecordNotFoundForUpdate(): void
    {
        $this->multipleInsertIntoDb(5);

        $result = PDOQueryBuilder::table('users')
            ->table('users')
            ->where('name', 'dummy')
            ->update(['name' => 'Test']);

        $this->assertEquals(0, $result);
    }

    public function testItCanThrowExceptionWhenFirstRecordNotFound(): void
    {
        $this->multipleInsertIntoDb(5);

        $this->expectException(RecordNotFoundException::class);

        PDOQueryBuilder::table('users')
            ->where('name', 'dummy')
            ->firstOrFail();
    }

    public function testItCanThrowExceptoinWhenCanNotFindRecord(): void
    {
        $id = $this->multipleInsertIntoDb(5);

        $this->expectException(RecordNotFoundException::class);

        PDOQueryBuilder::table('users')
            ->findOrFail($id + 1);
    }

    public function testItCanFetchDataWithMultipleOrWhere(): void
    {
        $this->multipleInsertIntoDb(5);
        $this->multipleInsertIntoDb(5, [
            'name' => 'Ali',
            'skill' => 'JS',
        ]);

        $result = PDOQueryBuilder::table('users')
            ->orWhere('skill', 'PHP')
            ->orWhere('skill', 'JS')
            ->get();

        $this->assertCount(10, $result);
        /** This scope should contains user with PHP skill */
        $scope1 = json_decode(json_encode($result[0]), true);
        $this->assertEquals('PHP', $scope1['skill']);
    /** This scope should contains user with JS skill */
        $scope2 = json_decode(json_encode($result[5]), true);
        $this->assertEquals('JS', $scope2['skill']);
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