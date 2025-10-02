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
$table->addColumn('name', 'varchar')->length(255)->notNull();

$table->addColumn('id', 'int')->primaryKey();
```

#### id()

```php
public function id(string $name = 'id', string $type = 'int', int $length = 11, bool $autoIncrement = true): self
```

Shorthand method for creating a primary key column. This is a convenient alternative to the verbose `addColumn()->length()->primaryKey()` chain.

```php
// Create a standard auto-incrementing primary key
$table->id();
// Equivalent to: $table->addColumn('id', 'int')->length(11)->primaryKey();

// Custom column name
$table->id('user_id');

// Custom type (e.g., bigint for large tables)
$table->id('order_id', 'bigint');

// Custom length
$table->id('item_id', 'int', 20);

// Without auto-increment (for composite keys or manual ID management)
$table->id('setting_id', 'int', 11, false);
```

#### changeColumn()

```php
public function changeColumn(string $name): ColumnDefinition
```

Modifies an existing column (ALTER TABLE only).

```php
$table->changeColumn('name')->rename('full_name', 'varchar', 300);
```

#### dropColumn()

```php
public function dropColumn(string $name): void
```

Removes a column from the table (ALTER TABLE only).

#### timestamps()

```php
public function timestamps(string $createdColumn = 'created', string $updatedColumn = 'updated'): void
```

Adds automatic timestamp management with MySQL triggers. Creates two TIMESTAMP columns and a trigger to automatically update the updated column on record changes.

```php
// Use default column names (created, updated)
$table->timestamps();

// Use custom column names
$table->timestamps('created_on', 'modified_on');
```

**Note:** This creates:

- A `created/created_on` column with `DEFAULT CURRENT_TIMESTAMP`
- An `updated/modified_on` column with `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
- A MySQL trigger to automatically update the updated column on UPDATE operations

#### dropTimestampTrigger()

```php
public function dropTimestampTrigger(string $triggerName): void
```

Removes a specific timestamp trigger by name (ALTER TABLE only).

#### dropTimestampTriggers()

```php
public function dropTimestampTriggers(): void
```

Removes auto-generated timestamp triggers for this table (ALTER TABLE only).

### Foreign Key Operations

#### addForeignKey()

```php
public function addForeignKey(string $column, string $foreignTable, string $foreignColumn = 'id', string $onDelete = '', string $onUpdate = ''): ForeignKeyDefinition
```

Adds a foreign key constraint.

```php
$table
  ->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE')
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

### Index Operations

#### addIndex()

```php
public function addIndex($columns): IndexDefinition
```

Adds a regular (non-unique) index to improve query performance. Accepts either a single column name or an array of column names for composite indexes.

```php
// Single column index
$table->addIndex('email');

// Composite index (multiple columns)
$table->addIndex(['user_id', 'created']);

// Custom identifier
$table->addIndex('email')->identifier('custom_email_idx');
```

#### dropIndex()

```php
public function dropIndex($columns): IndexDefinition
```

Removes an index from the table (ALTER TABLE only). Accepts either a single column name or an array of column names.

```php
// Drop single column index
$table->dropIndex('email');

// Drop composite index
$table->dropIndex(['user_id', 'created']);
```

#### dropIndexByIdentifier()

```php
public function dropIndexByIdentifier(string $identifier): void
```

Removes an index by its identifier name (ALTER TABLE only).

```php
$table->dropIndexByIdentifier('custom_email_idx');
```

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
$qb
  ->leftJoin('user_profiles')
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

### IndexDefinition

Represents a database index for improving query performance.

#### identifier()

```php
public function identifier(string $identifierName): self
```

Sets a custom identifier name for the index.

**Auto-generated identifier format:** `idx_{column}` or `idx_{column1}_{column2}_{...}` for composite indexes

#### getColumns()

```php
public function getColumns(): array
```

Returns the array of column names included in the index.

#### isComposite()

```php
public function isComposite(): bool
```

Returns `true` if the index spans multiple columns, `false` for single-column indexes.

```php
$index = $table->addIndex(['user_id', 'status']);
$index->isComposite(); // true

$index = $table->addIndex('email');
$index->isComposite(); // false
```

## Timestamp Management

The dry-dbi library provides automatic timestamp management through MySQL triggers, eliminating the need to manually update timestamp columns in your application code.

### How It Works

When you call `timestamps()` on a TableBuilder:

1. **Column Creation**: Two TIMESTAMP columns are added to your table
   - `created` (or custom name): Set to `CURRENT_TIMESTAMP` when record is created
   - `updated` (or custom name): Set to `CURRENT_TIMESTAMP` and updated automatically on changes

2. **Trigger Creation**: A MySQL trigger is created with the naming convention `{table_name}_updated_trigger`

3. **Automatic Updates**: The trigger automatically updates the `updated` column whenever any field in the record is modified

### Trigger Naming Convention

Auto-generated triggers follow this pattern: `{table_name}_updated_trigger`

For example:

- Table `users` → Trigger `users_updated_trigger`
- Table `blog_posts` → Trigger `blog_posts_updated_trigger`

### Best Practices

- Use `timestamps()` for tables that need audit trails
- Use custom column names when integrating with existing schemas
- Always call `dropTimestampTriggers()` before dropping timestamp columns
- When altering timestamp columns, recreate triggers to ensure consistency

## Usage Examples

### Creating a Table

```php
$qb->table('users')->create(function (TableBuilder $table) {
  // Use the id() shorthand for primary key
  $table->id();
  $table->addColumn('name', 'varchar')->length(255)->notNull();
  $table->addColumn('email', 'varchar')->length(255)->notNull();
  $table->addColumn('status', 'varchar')->length(20)->notNull();

  // Add automatic timestamp management
  $table->timestamps();

  // Add unique constraint
  $table->addUnique('email');

  // Add indexes for better query performance
  $table->addIndex('status');
  $table->addIndex('name');
});
```

### Creating a Table with Custom Timestamp Columns

```php
$qb->table('posts')->create(function (TableBuilder $table) {
  // Custom primary key name with bigint type
  $table->id('post_id', 'bigint');
  $table->addColumn('title', 'varchar')->length(255)->notNull();
  $table->addColumn('content', 'text');

  // Use custom timestamp column names
  $table->timestamps('created_on', 'modified_on');
});
```

### Creating a Table with Custom Timestamp Columns

```php
$qb->table('posts')->create(function (TableBuilder $table) {
  $table->addColumn('id', 'int')->primaryKey();
  $table->addColumn('title', 'varchar')->length(255)->notNull();
  $table->addColumn('content', 'text');

  // Use custom timestamp column names
  $table->timestamps('created_on', 'modified_on');
});
```

### Altering a Table

```php
$qb->table('users')->alter(function (TableBuilder $table) {
  $table->addColumn('phone', 'varchar')->length(20);
  $table->changeColumn('name')->length(300);
  $table->dropColumn('old_field');

  // Add timestamp functionality to existing table
  $table->timestamps();

  $table->addForeignKey('role_id', 'roles', 'id', 'SET NULL');
  $table->dropForeignKeyByIdentifier('old_fk_constraint');

  // Add new indexes
  $table->addIndex('phone');
  $table->dropIndex('old_index_column');
});
```

### Managing Timestamp Triggers

```php
// Remove timestamp functionality from a table
$qb->table('users')->alter(function (TableBuilder $table) {
  $table->dropColumn('created');
  $table->dropColumn('updated');
  $table->dropTimestampTriggers();
});

// Update timestamp trigger (recreate with new column names)
$qb->table('posts')->alter(function (TableBuilder $table) {
  $table->dropTimestampTriggers();
  $table->timestamps('date_created', 'date_modified');
});
```

### Working with Indexes

```php
// Create table with multiple indexes
$qb->table('posts')->create(function (TableBuilder $table) {
  $table->id();
  $table->addColumn('user_id', 'int')->notNull();
  $table->addColumn('category', 'varchar')->length(100)->notNull();
  $table->addColumn('status', 'varchar')->length(20)->notNull();
  $table->addColumn('slug', 'varchar')->length(255)->notNull();
  $table->addColumn('title', 'varchar')->length(255)->notNull();
  $table->timestamps();

  // Foreign key
  $table->addForeignKey('user_id', 'users', 'id', 'CASCADE');

  // Unique constraint for slug
  $table->addUnique('slug');

  // Single column indexes for frequently queried fields
  $table->addIndex('status');
  $table->addIndex('category');

  // Composite index for queries filtering by user and status
  $table->addIndex(['user_id', 'status']);

  // Composite index with custom identifier
  $table
    ->addIndex(['category', 'status', 'created'])
    ->identifier('idx_posts_category_filter');
});

// Alter table to add/remove indexes
$qb->table('posts')->alter(function (TableBuilder $table) {
  // Add new index
  $table->addIndex('title');

  // Drop old index
  $table->dropIndex('category');

  // Drop composite index
  $table->dropIndex(['user_id', 'status']);

  // Drop index by custom identifier
  $table->dropIndexByIdentifier('idx_posts_category_filter');

  // Add new composite index with better column order
  $table
    ->addIndex(['status', 'user_id', 'created'])
    ->identifier('idx_posts_optimized');
});
```

### Complex Join

```php
$qb
  ->leftJoin('user_profiles')
  ->as('profile')
  ->on('users.id', '=', 'profile.user_id')
  ->on('profile.is_active', '=', '1');
```
