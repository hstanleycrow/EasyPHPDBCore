[English](README.md) | Español

# EasyPHPDBCore
Capa de base de datos ligera basada en PDO para PHP, con un `Model` CRUD simple y sentencias preparadas en todas las operaciones.

## Requisitos
- PHP 8.2 o superior
- Composer
- PDO con el driver `pdo_mysql` (para la conexión MySQL/MariaDB)

Dependencia en tiempo de ejecución: [`psr/log`](https://packagist.org/packages/psr/log). El logger PSR-3 es opcional; sin él, los errores se lanzan como excepciones.

## Instalación
```bash
composer require hstanleycrow/easyphpdbcore
```

## Ejemplo rápido

```php
use hstanleycrow\EasyPHPDBCore\Model;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvConfig;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLPDOConnection;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvCharsetConfig;

require 'vendor/autoload.php';

// $_ENV debe tener DATABASE_HOST, DATABASE_NAME, DATABASE_USERNAME,
// DATABASE_PASSWORD, DATABASE_PORT y DATABASE_CHARSET.
$connection = new MySQLPDOConnection(
    new MySQLEnvConfig($_ENV),
    new MySQLEnvCharsetConfig($_ENV)
);

class User extends Model
{
    protected ?string $table = 'users';
}

$user = new User($connection);

$id = $user->create([
    'name' => 'Harold',
    'username' => 'hstanleycrow',
    'active' => 'S',
])->lastInsertId();

$record = $user->getById($id);

$user->update(['name' => 'Harold Crow'], ['id' => $id]);

$user->delete(['id' => $id]);
```

Cada escritura usa sentencias preparadas con valores enlazados: las claves del
array son los nombres de columna y los valores son los parámetros enlazados.
Nunca interpoles entrada del usuario en las cadenas de `query()`; pásala por los
bindings.

## Consultas de lectura personalizadas

`getRecords()` acepta bindings posicionales o con nombre:

```php
class User extends Model
{
    protected ?string $table = 'users';

    public function getActive(): array
    {
        return $this->query('SELECT id, name FROM users WHERE active = ? ORDER BY id')
            ->getRecords(['S']);
    }
}
```

## Logging opcional

Cualquier logger [PSR-3](https://www.php-fig.org/psr/psr-3/) puede inyectarse
como último argumento del constructor de la conexión y del modelo (o de las
clases de registros). Si se omite, se usa un `NullLogger` y los fallos solo se
lanzan como excepciones.

```php
$logger = new Monolog\Logger('app');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stderr'));

$connection = new MySQLPDOConnection(new MySQLEnvConfig($_ENV), new MySQLEnvCharsetConfig($_ENV), $logger);
$user = new User($connection, $logger);
```

## Manejo de errores

Todos los fallos lanzan excepciones tipadas en vez de imprimir nada:

- `hstanleycrow\EasyPHPDBCore\Exception\ConnectionException` — errores de conexión/configuración.
- `hstanleycrow\EasyPHPDBCore\Exception\QueryException` — errores al ejecutar una consulta.

Ambas extienden `hstanleycrow\EasyPHPDBCore\Exception\DatabaseException`, así que
puedes capturar una en específico o la clase base para todos los errores.

## API pública

### `Model`
| Método | Descripción |
| --- | --- |
| `__construct(IConnection $connection, ?LoggerInterface $logger = null)` | Crea un modelo. Las subclases definen `protected ?string $table`. |
| `create(array $fieldsList): self` | Inserta una fila. Las claves son columnas, los valores se enlazan. |
| `lastInsertId(): ?int` | Id generado por el último `create()`. |
| `query(string $query): self` | Define un SELECT crudo a ejecutar con `getRecords()`. |
| `getRecords(array $bindings = []): array` | Ejecuta la consulta y devuelve filas como arrays asociativos. |
| `getById(int\|string $id): ?array` | `SELECT *` por clave primaria; `null` si no existe. |
| `update(array $updateFields, array $whereConditions): self` | Actualiza las filas que cumplen todas las condiciones. |
| `delete(array $whereConditions): self` | Elimina las filas que cumplen todas las condiciones. |
| `beginTransaction() / commit() / rollback(): void` | Control de transacciones sobre el PDO subyacente. |

### Conexión
| Clase | Descripción |
| --- | --- |
| `Connection\MySQLPDOConnection` | Abre una conexión PDO real a MySQL/MariaDB. |
| `Connection\MockConnection` | Conexión vacía para pruebas. |
| `Connection\MySQLEnvConfig` / `MySQLEnvCharsetConfig` | Leen credenciales/charset desde un array de entorno. |
| `Connection\IConnection` / `IConfig` / `ICharsetConfig` | Interfaces para conectar tus propias implementaciones. |

### Clases de registros (usadas por `Model`, utilizables por separado)
`CreateRecords`, `ReadRecords`, `UpdateRecords`, `DeleteRecords` exponen cada una
un método `execute(...)` y comparten la misma forma de constructor
`(IConnection, string $table, ?LoggerInterface)` (`ReadRecords` recibe la
consulta en vez de la tabla).

## Pruebas

```bash
composer install
composer test
```

La suite corre contra una base SQLite en memoria, por lo que no requiere un
servidor MySQL.

## Licencia

MIT — ver [LICENSE](LICENSE).
