# Astro ORM
object-relational mapper (ORM), lets you query and manipulate data with fluent api from a database using an object-oriented paradigm.

## How its works
<img src="docs/UML Diagram.jpg" alt="UML">
### 1. Setup your database configs
Fill the ``` configs/database.php ``` file with your database configuration.

**NOTE:** You Can use any database extensions, like: PDO, Mysqli, Sqlite,etc. Just define its array key.

### 2. Implements Connection Contract
In this project i use PDO driver. so i create ```  Database/PDODatabaseConnection.php``` file and implements contracts methods.

```php
connect();        // Which implements database connection
getConnection();  // Which return database connection
```

### 3. Use Query Builder
Now get database configs, create new instanse of `PDODatabaseConnection` and connect to DB.

```php
$this->config = $this->getConfigs('database', 'astro_orm');

$pdoConnection = new PDODatabaseConnection($this->config);
$pdoHandler = $pdoConnection->connect();
```

#### Insert
Insert Data: return last insert id
```php
$data = [
    'name'  => 'John',
    'email' => 'john.doe@gmail.com',
    'link'  => 'https://example.com',
    'skill' => 'PHP'
];

$last_id = PDOQueryBuilder::table('users')->create($data);
```

#### update
Update Data: return true if successful
```php
$result = PDOQueryBuilder::table('users')
    ->where('name', 'John')
    ->where('skill', 'PHP')
    ->update([
        'skill' => 'Javascript', 
        'name' => 'Jeff', 
        'email' => 'jeff@gmail.com'
    ]);
```

#### Multiple where
```php
$result = PDOQueryBuilder::table('users')
    ->where('name', 'John')
    ->where('skill', 'JS')
    ->update(['skill' => 'Javascript']);
```

#### Multiple orWhere
```php
$result = PDOQueryBuilder::table('users')
    ->orWhere('skill', 'PHP')
    ->orWhere('skill', 'JS')
    ->get();
```

#### Delete
Delete Data: return true if successful
```php
$result = PDOQueryBuilder::table('users')
    ->where('name', 'John')
    ->delete();
```

#### Fetch
```php
$result = PDOQueryBuilder::table('users')
    ->where('name', 'John')
    ->where('skill', 'Javascript')
    ->get();
```

#### Fetch first row
```php
$result = PDOQueryBuilder::table('users')
    ->where('name', 'First Row')
    ->first();
```

#### Fetch first row or throw exception on failure
```php
$result = PDOQueryBuilder::table('users')
    ->where('name', 'Jim')
    ->firstOrFail();
```

#### Find ID
```php
$result = PDOQueryBuilder::table('users')
    ->find($id);
```

#### Find ID or throw exception on failure
```php
$result = PDOQueryBuilder::table('users')
    ->findOrFail($id);
```

#### Find with value
```php
$result = PDOQueryBuilder::table('users')
    ->findBy('name', 'Jack');
```

#### Get specific rows
```php
$result = PDOQueryBuilder::table('users')
    ->where('name', 'Jack')
    ->limit(5)
    ->get();
```

#### Sort rows
```php
$result = PDOQueryBuilder::table('users')
    ->orderBy('skill', 'DESC')
    ->get();
```