# QueryBuilder

The QueryBuilder provides a fluent interface for constructing SQL queries. It extends BuildHandler and supports SELECT, CREATE, ALTER, and DROP operations.

## Basic Usage

```php
$qb = new QueryBuilder();
$qb->table('users')
   ->selectAll()
   ->where('active', '=', 1)
   ->orderBy('name')
   ->limit(10);
```

## SELECT Operations

### select()

```php
public function select($column): QueryBuilder
```

Adds a column to the SELECT clause.

```php
$qb->select('name')
   ->select('email')
   ->select(new Raw('COUNT(*) as total'));
```

### selectAll()

```php
public function selectAll(?string $table = null): QueryBuilder
```

Adds `table.*` to SELECT clause. Uses current table if none specified.

### selectAs()

```php
public function selectAs($statement, string $alias): QueryBuilder
```

Adds a column with an alias.

```php
$qb->selectAs('CONCAT(first_name, " ", last_name)', 'full_name');
```

## WHERE Conditions

### where()

```php
public function where($field, string $operator, $value, string $connectBefore = 'AND'): QueryBuilder
```

Adds a WHERE condition.

```php
$qb->where('age', '>', 18)
   ->where('status', '=', 'active', 'OR');
```

### whereGroup()

```php
public function whereGroup(callable $call, string $connectBefore = 'AND'): void
```

Groups WHERE conditions with parentheses.

```php
$qb->whereGroup(function($qb) {
    $qb->where('type', '=', 'admin')
       ->where('type', '=', 'moderator', 'OR');
});
```

## HAVING Conditions

### having()

```php
public function having($field, string $operator, $value, string $connectBefore = 'AND'): QueryBuilder
```

Adds HAVING condition (similar to WHERE but for grouped results).

### havingGroup()

```php
public function havingGroup(callable $call, string $connectBefore = 'AND'): void
```

Groups HAVING conditions with parentheses.

## Ordering and Grouping

### orderBy()

```php
public function orderBy($column, string $sortMethod = 'ASC'): QueryBuilder
```

Adds ORDER BY clause. Replaces existing ordering for the same column.

### groupBy()

```php
public function groupBy($column): QueryBuilder
```

Adds GROUP BY clause.

## Limiting Results

### limit()

```php
public function limit($limit): QueryBuilder
```

Sets LIMIT clause.

### offset()

```php
public function offset($offset): QueryBuilder
```

Sets OFFSET clause.

## JOIN Operations

### innerJoin()

```php
public function innerJoin(string $table): JoinBuilder
```

Creates an INNER JOIN.

### leftJoin()

```php
public function leftJoin(string $table): JoinBuilder
```

Creates a LEFT JOIN.

### rightJoin()

```php
public function rightJoin(string $table): JoinBuilder
```

Creates a RIGHT JOIN.

```php
$qb->leftJoin('posts')
   ->on('users.id', '=', 'posts.user_id')
   ->as('p');
```

## Schema Operations

### create()

```php
public function create(callable $createScheme): QueryBuilder
```

Creates a CREATE TABLE statement.

```php
$qb->table('users')
   ->create(function(TableBuilder $table) {
       $table->addColumn('id', 'int')->primaryKey();
       $table->addColumn('name', 'varchar')->length(255);
       $table->addColumn('email', 'varchar')->length(255);
   });
```

### alter()

```php
public function alter(callable $alterScheme): QueryBuilder
```

Creates an ALTER TABLE statement.

```php
$qb->table('users')
   ->alter(function(TableBuilder $table) {
       $table->addColumn('phone', 'varchar')->length(20);
       $table->dropColumn('old_field');
   });
```

### drop()

```php
public function drop(): QueryBuilder
```

Creates a DROP TABLE statement.

## Building and Execution

### build()

```php
public function build(): void
```

Builds the final SQL query. Called automatically by Repository.

The QueryBuilder determines the query type based on what methods were called:
- If `select*()` methods were used: SELECT query
- If `create()` was called: CREATE TABLE query  
- If `alter()` was called: ALTER TABLE query
- If `drop()` was called: DROP TABLE query

## Raw SQL

Use the `Raw` class for custom SQL expressions:

```php
$qb->select(new Raw('COUNT(*) as total'))
   ->where(new Raw('YEAR(created_at)'), '=', 2023);
```