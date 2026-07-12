English | [Español](AI_USAGE.es.md)

# AI_USAGE.md — EasyPHPDBCore

Technical spec for an AI assistant to use this library correctly when generating PHP code.

## What it is

A thin PDO-based database layer. A base `Model` (subclassed per table) exposes
`create` / `getRecords` / `getById` / `update` / `delete` plus transaction
helpers. Every write and `getById` uses prepared statements with bound values.
Connections are built from an env array. Logging is optional via PSR-3. Errors
are thrown as typed exceptions, never printed.

## Installation

```bash
composer require hstanleycrow/easyphpdbcore
```

```php
require 'vendor/autoload.php';
use hstanleycrow\EasyPHPDBCore\Model;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvConfig;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLPDOConnection;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvCharsetConfig;
```

## Usage rules

1. **Namespace root is `hstanleycrow\EasyPHPDBCore\`.** `Model` and the four record classes (`CreateRecords`, `ReadRecords`, `UpdateRecords`, `DeleteRecords`) sit at the root. Connection classes are under `Connection\`. Exceptions are under `Exception\`.
2. **Build a connection first, then pass it to models.** `new MySQLPDOConnection(new MySQLEnvConfig($_ENV), new MySQLEnvCharsetConfig($_ENV))`. The connection opens immediately in the constructor and throws `ConnectionException` if it fails. Reuse one connection across models; do not open one per query.
3. **The env array must contain** `DATABASE_HOST`, `DATABASE_NAME`, `DATABASE_USERNAME`, `DATABASE_PASSWORD`, `DATABASE_PORT`, `DATABASE_CHARSET`. A missing key throws `ConnectionException`. `DATABASE_CHARSET` is a full init command string, e.g. `"SET NAMES 'utf8mb4' COLLATE utf8mb4_unicode_ci"`.
4. **A model is defined only by its table.** Subclass `Model` and set `protected ?string $table = 'your_table';`. Do not override the constructor unless you need to; if you do, call `parent::__construct($connection, $logger)`. Calling a CRUD method on a `Model` with no table throws `QueryException`.
5. **`create(array $fieldsList)` maps keys to columns and values to bound `?` placeholders.** It returns `$this` (chainable); read the generated id with `->lastInsertId()`. Example: `$user->create(['name' => 'A', 'username' => 'a'])->lastInsertId()`.
6. **`update(array $updateFields, array $whereConditions)` and `delete(array $whereConditions)`** build `field = ?` fragments joined by `AND` for the where clause. All conditions are ANDed; there is no OR/operator support — for anything more complex use `query()` + `getRecords()`.
7. **Reads go through `query()` + `getRecords()`.** `query()` sets a raw SELECT string and returns `$this`; `getRecords(array $bindings = [])` executes it and returns `array<int, array<string,mixed>>` (empty array when nothing matches). Put user input in `$bindings`, never in the query string.
8. **`getById(int|string $id)` runs `SELECT * FROM {table} WHERE {primaryKey} = ?`** and returns the single row as an associative array, or `null` if not found. The primary key column defaults to `id`; override with `protected string $primaryKey`.
9. **Never string-interpolate values into SQL.** This is the one hard rule. Use bound parameters everywhere: array values in `create`/`update`/`delete`, and the `$bindings` array in `getRecords`. Column and table names are not parameterizable in PDO — only use trusted, code-defined identifiers there.
10. **Errors are exceptions, not return codes.** On failure the record classes throw `QueryException` (query) or `ConnectionException` (connect/config), both extending `DatabaseException`. Nothing is echoed, died, or var_dumped. Wrap operations in `try/catch` when you need to handle failures.
11. **Logging is optional and PSR-3.** Pass any `Psr\Log\LoggerInterface` as the last constructor argument of the connection, the model, or a record class. Omit it (or pass `null`) to disable logging — a `NullLogger` is used. The library never configures a logger for you.
12. **Return values are JSON-serializable.** Rows are plain associative arrays; there is no presentation logic, session, or HTTP coupling. This makes the layer safe to use directly behind a REST endpoint.
13. **Transactions** are `beginTransaction()`, `commit()`, `rollback()` on the model, delegating to the underlying PDO. Wrap multi-statement writes and `rollback()` inside the `catch`.

## Minimal end-to-end

```php
use hstanleycrow\EasyPHPDBCore\Model;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvConfig;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLPDOConnection;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvCharsetConfig;

$connection = new MySQLPDOConnection(new MySQLEnvConfig($_ENV), new MySQLEnvCharsetConfig($_ENV));

class User extends Model
{
    protected ?string $table = 'users';
}

$user = new User($connection);

try {
    $id = $user->create(['name' => 'Harold', 'username' => 'hstanleycrow', 'active' => 'S'])->lastInsertId();
    $row = $user->getById($id);
    $user->update(['name' => 'Harold Crow'], ['id' => $id]);
    $user->delete(['id' => $id]);
} catch (QueryException $e) {
    // handle / rethrow
}
```

## Common mistakes to avoid

- Interpolating a variable into `query('... WHERE id = ' . $id)` instead of `query('... WHERE id = ?')->getRecords([$id])`.
- Expecting a boolean or an errors array from CRUD methods — `create/update/delete` return `$this`, `getRecords` returns an array, and failures throw.
- Forgetting `protected ?string $table` on a `Model` subclass.
- Opening a new `MySQLPDOConnection` per request/query instead of reusing one.
- Passing `$_SERVER`/request objects into the library — it has no HTTP awareness by design.
