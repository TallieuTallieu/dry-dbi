# Schema Builders

Schema builders provide fluent interfaces for creating and modifying database table structures.

## TableBuilder

Handles CREATE TABLE and ALTER TABLE operations.

### Constructor

```php
public function __construct(bool $isAlter = false)
```

- `$isAlter`: Set to `true` for ALTER TABLE operations, `false` for CREATE TABLE

### Column Operations

#### addColumn()

```php
public function addColumn(string $name, string $type): ColumnDefinition
```

Adds a new column to the table. Returns a ColumnDefinition for further configuration.

```php
$table->addColumn('name', 'varchar')
      ->length(255)
      ->notNull();

$table->addColumn('id', 'int')
      ->primaryKey();
```

#### changeColumn()

```php
public function changeColumn(string $name): ColumnDefinition
```

Modifies an existing column (ALTER TABLE only).

```php
$table->changeColumn('name')
      ->rename('full_name', 'varchar', 300);
```

#### dropColumn()

```php
public function dropColumn(string $name): void
```

Removes a column from the table (ALTER TABLE only).

### Foreign Key Operations

#### addForeignKey()

```php
public function addForeignKey(string $column, string $foreignTable, string $foreignColumn = 'id', string $onDelete = '', string $onUpdate = ''): ForeignKeyDefinition
```

Adds a foreign key constraint.

```php
$table->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE')
      ->identifier('fk_posts_user');
```

#### dropForeignKey()

```php
public function dropForeignKey(string $column, string $foreignTable, string $foreignColumn = 'id', string $onDelete = '', string $onUpdate = ''): void
```

Removes a foreign key constraint (ALTER TABLE only).

#### dropForeignKeyByIdentifier()

```php
public function dropForeignKeyByIdentifier(string $identifier): void
```

Removes a foreign key by its identifier name.

### Unique Constraint Operations

#### addUnique()

```php
public function addUnique(string $column): UniqueDefinition
```

Adds a unique constraint.

```php
$table->addUnique('email');
```

#### dropUnique()

```php
public function dropUnique(string $column): UniqueDefinition
```

Removes a unique constraint (ALTER TABLE only).

## ColumnDefinition

Defines column properties and constraints.

### Type and Size

#### type()

```php
public function type(string $type): self
```

Sets the column data type.

#### length()

```php
public function length($length): self
```

Sets the column length/size.

```php
$column->type('varchar')->length(255);
$column->type('decimal')->length('10,2');
```

### Constraints

#### primaryKey()

```php
public function primaryKey(): self
```

Makes the column a primary key with auto-increment.

#### null() / notNull()

```php
public function null(): self
public function notNull(): self
```

Sets the column's NULL constraint.

#### default()

```php
public function default($defaultVal): self
```

Sets a default value for the column.

```php
$column->default('active');
$column->default(0);
$column->default(null);
```

### Column Modification

#### rename()

```php
public function rename(string $name, string $type, ?int $length = null): self
```

Renames a column and optionally changes its type and length (ALTER TABLE only).

#### generate()

```php
public function generate(string $query): self
```

Creates a generated column with the specified expression.

```php
$column->generate('CONCAT(first_name, " ", last_name)');
```

## JoinBuilder

Handles JOIN operations in queries.

### Join Types

#### setType()

```php
public function setType(string $type): void
```

Sets the join type: 'left', 'right', or 'inner'.

### Join Conditions

#### on()

```php
public function on(string $field, string $operator, string $value, bool $prefix = true): self
```

Adds an ON condition to the join.

```php
$join->on('users.id', '=', 'posts.user_id');
$join->on('posts.status', '=', 'published');
```

#### as()

```php
public function as(string $alias): self
```

Sets an alias for the joined table.

```php
$qb->leftJoin('user_profiles')
   ->as('profile')
   ->on('users.id', '=', 'profile.user_id');
```

## Definition Classes

### ForeignKeyDefinition

Represents a foreign key constraint.

#### identifier()

```php
public function identifier(string $identifierName): self
```

Sets a custom identifier name for the foreign key.

**Auto-generated identifier format:** `fk_{table}_{column}_{foreignTable}_{foreignColumn}`

### UniqueDefinition

Represents a unique constraint.

**Auto-generated identifier format:** `uq_{column}`

## Usage Examples

### Creating a Table

```php
$qb->table('users')
   ->create(function(TableBuilder $table) {
       $table->addColumn('id', 'int')->primaryKey();
       $table->addColumn('name', 'varchar')->length(255)->notNull();
       $table->addColumn('email', 'varchar')->length(255)->notNull();
       $table->addColumn('created_at', 'timestamp')->default('CURRENT_TIMESTAMP');
       
       $table->addUnique('email');
   });
```

### Altering a Table

```php
$qb->table('users')
   ->alter(function(TableBuilder $table) {
       $table->addColumn('phone', 'varchar')->length(20);
       $table->changeColumn('name')->length(300);
       $table->dropColumn('old_field');
       
       $table->addForeignKey('role_id', 'roles', 'id', 'SET NULL');
       $table->dropForeignKeyByIdentifier('old_fk_constraint');
   });
```

### Complex Join

```php
$qb->leftJoin('user_profiles')
   ->as('profile')
   ->on('users.id', '=', 'profile.user_id')
   ->on('profile.is_active', '=', '1');
```