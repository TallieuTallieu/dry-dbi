# BuildHandler

Abstract base class for all query builders. Provides common functionality for building SQL queries with parameter binding.

## Abstract Methods

### build()

```php
abstract public function build(): void
```

Must be implemented by subclasses to build the specific query type.

## Table Management

### table()

```php
final public function table(string $tableName): self
```

Sets the table name for the query.

### getTable()

```php
final public function getTable(): string
```

Returns the current table name.

## Query Building

### addToQuery()

```php
final protected function addToQuery(string $queryPart): void
```

Appends a string to the query being built.

### getQuery()

```php
final public function getQuery(): string
```

Returns the complete built query string.

## Parameter Binding

### addParameter()

```php
final protected function addParameter($value): void
```

Adds a single parameter for prepared statement binding.

### addParameters()

```php
final protected function addParameters(array $values): void
```

Adds multiple parameters for prepared statement binding.

### getParameters()

```php
final public function getParameters(): array
```

Returns all parameters for the prepared statement.

## Statement Creation

### createStatement()

```php
protected function createStatement($statement, bool $useTablePrefix = false): StatementInterface
```

Creates a StatementInterface from various input types:

- If already a StatementInterface: returns as-is
- If `$useTablePrefix` is true: creates Raw statement with table prefix
- Otherwise: creates Raw statement with parameter binding

### quote()

```php
final protected function quote(string $statement): string
```

Wraps a string in backticks for SQL identifiers.

```php
$this->quote('table_name'); // Returns: `table_name`
```

### withTablePrefix()

```php
protected function withTablePrefix(string $columnName): string
```

Adds table prefix to column names. Handles both simple columns and table.column format.

```php
// With table set to 'users'
$this->withTablePrefix('name');        // Returns: `users`.`name`
$this->withTablePrefix('posts.title'); // Returns: `posts`.`title`
```

## Usage in Subclasses

```php
class CustomBuilder extends BuildHandler
{
    private $conditions = [];
    
    public function where($column, $value)
    {
        $this->conditions[] = [
            'column' => $this->createStatement($column, true),
            'value' => $this->createStatement($value)
        ];
        return $this;
    }
    
    public function build()
    {
        $this->addToQuery('SELECT * FROM ' . $this->quote($this->getTable()));
        
        if (!empty($this->conditions)) {
            $this->addToQuery(' WHERE ');
            
            foreach ($this->conditions as $condition) {
                $this->addToQuery($condition['column']->getValue() . ' = ' . $condition['value']->getValue());
                $this->addParameters($condition['column']->getBindings());
                $this->addParameters($condition['value']->getBindings());
            }
        }
    }
}
```