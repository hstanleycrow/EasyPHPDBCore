[English](AI_USAGE.md) | Español

# AI_USAGE.es.md — EasyPHPDBCore

Especificación técnica para que una IA use esta librería correctamente al generar código PHP.

## Qué es

Una capa de base de datos delgada basada en PDO. Un `Model` base (subclaseado
por tabla) expone `create` / `getRecords` / `getById` / `update` / `delete` más
helpers de transacción. Toda escritura y `getById` usa sentencias preparadas con
valores enlazados. Las conexiones se construyen desde un array de entorno. El
logging es opcional vía PSR-3. Los errores se lanzan como excepciones tipadas,
nunca se imprimen.

## Instalación

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

## Reglas de uso

1. **La raíz del namespace es `hstanleycrow\EasyPHPDBCore\`.** `Model` y las cuatro clases de registros (`CreateRecords`, `ReadRecords`, `UpdateRecords`, `DeleteRecords`) están en la raíz. Las clases de conexión están bajo `Connection\`. Las excepciones bajo `Exception\`.
2. **Construye primero una conexión y pásala a los modelos.** `new MySQLPDOConnection(new MySQLEnvConfig($_ENV), new MySQLEnvCharsetConfig($_ENV))`. La conexión se abre de inmediato en el constructor y lanza `ConnectionException` si falla. Reutiliza una conexión entre modelos; no abras una por consulta.
3. **El array de entorno debe contener** `DATABASE_HOST`, `DATABASE_NAME`, `DATABASE_USERNAME`, `DATABASE_PASSWORD`, `DATABASE_PORT`, `DATABASE_CHARSET`. Una clave faltante lanza `ConnectionException`. `DATABASE_CHARSET` es una cadena de comando de inicio completa, ej. `"SET NAMES 'utf8mb4' COLLATE utf8mb4_unicode_ci"`.
4. **Un modelo se define solo por su tabla.** Subclasea `Model` y define `protected ?string $table = 'tu_tabla';`. No sobrescribas el constructor salvo que lo necesites; si lo haces, llama a `parent::__construct($connection, $logger)`. Llamar a un método CRUD en un `Model` sin tabla lanza `QueryException`.
5. **`create(array $fieldsList)` mapea claves a columnas y valores a placeholders `?` enlazados.** Devuelve `$this` (encadenable); lee el id generado con `->lastInsertId()`. Ejemplo: `$user->create(['name' => 'A', 'username' => 'a'])->lastInsertId()`.
6. **`update(array $updateFields, array $whereConditions)` y `delete(array $whereConditions)`** construyen fragmentos `field = ?` unidos por `AND` en el where. Todas las condiciones se combinan con AND; no hay soporte de OR/operadores — para algo más complejo usa `query()` + `getRecords()`.
7. **Las lecturas van por `query()` + `getRecords()`.** `query()` fija un SELECT crudo y devuelve `$this`; `getRecords(array $bindings = [])` lo ejecuta y devuelve `array<int, array<string,mixed>>` (array vacío si no hay coincidencias). Pon la entrada del usuario en `$bindings`, nunca en la cadena de la consulta.
8. **`getById(int|string $id)` ejecuta `SELECT * FROM {tabla} WHERE {primaryKey} = ?`** y devuelve la fila como array asociativo, o `null` si no existe. La clave primaria por defecto es `id`; sobrescríbela con `protected string $primaryKey`.
9. **Nunca interpoles valores en el SQL.** Esta es la regla dura. Usa parámetros enlazados siempre: los valores de array en `create`/`update`/`delete`, y el array `$bindings` en `getRecords`. Los nombres de columna y tabla no se pueden parametrizar en PDO — usa ahí solo identificadores confiables definidos en código.
10. **Los errores son excepciones, no códigos de retorno.** Al fallar, las clases de registros lanzan `QueryException` (consulta) o `ConnectionException` (conexión/config), ambas extienden `DatabaseException`. Nada se imprime, muere ni se vuelca. Envuelve las operaciones en `try/catch` cuando necesites manejar fallos.
11. **El logging es opcional y PSR-3.** Pasa cualquier `Psr\Log\LoggerInterface` como último argumento del constructor de la conexión, el modelo o una clase de registros. Omítelo (o pasa `null`) para desactivarlo — se usa un `NullLogger`. La librería nunca configura un logger por ti.
12. **Los valores de retorno son serializables a JSON.** Las filas son arrays asociativos simples; no hay lógica de presentación, sesión ni acoplamiento HTTP. Esto hace segura la capa para usarla directamente detrás de un endpoint REST.
13. **Transacciones**: `beginTransaction()`, `commit()`, `rollback()` en el modelo, delegando al PDO subyacente. Envuelve escrituras multi-sentencia y haz `rollback()` dentro del `catch`.

## Mínimo de punta a punta

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
    // manejar / relanzar
}
```

## Errores comunes a evitar

- Interpolar una variable en `query('... WHERE id = ' . $id)` en vez de `query('... WHERE id = ?')->getRecords([$id])`.
- Esperar un booleano o un array de errores de los métodos CRUD — `create/update/delete` devuelven `$this`, `getRecords` devuelve un array, y los fallos lanzan.
- Olvidar `protected ?string $table` en una subclase de `Model`.
- Abrir una nueva `MySQLPDOConnection` por request/consulta en vez de reutilizar una.
- Pasar `$_SERVER`/objetos de request a la librería — por diseño no tiene conciencia de HTTP.
