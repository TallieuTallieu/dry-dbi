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
public function timestamps(string $createdColumn = 'created', string $updatedColumn = 'updated', TimestampFormat $format = TimestampFormat::UNIX): void
```

Adds automatic timestamp management with MySQL triggers. Supports both Unix timestamp (INT) and datetime (TIMESTAMP) formats.

```php
use Tnt\Dbi\Enums\TimestampFormat;

// Use default column names and Unix format (created, updated) - RECOMMENDED
$table->timestamps();

// Use custom column names with Unix format (default)
$table->timestamps('created_at', 'updated_at');

// Use datetime/TIMESTAMP format explicitly
$table->timestamps('created', 'updated', TimestampFormat::DATETIME);

// Use custom column names with datetime format
$table->timestamps('created_on', 'modified_on', TimestampFormat::DATETIME);
```

**Unix Format (default - RECOMMENDED):** Creates:

- A `created` column with `INT UNSIGNED NOT NULL`
- An `updated` column with `INT UNSIGNED NOT NULL`
- Two MySQL triggers:
    - `{table_name}_created_trigger`: Sets both timestamps on INSERT using `UNIX_TIMESTAMP()`
    - `{table_name}_updated_trigger`: Updates the updated timestamp on UPDATE using `UNIX_TIMESTAMP()`

**Datetime Format:** Creates:

- A `created` column with `TIMESTAMP NOT NULL`
- An `updated` column with `TIMESTAMP NOT NULL`
- Two MySQL triggers:
    - `{table_name}_created_trigger`: Sets both timestamps on INSERT using `CURRENT_TIMESTAMP`
    - `{table_name}_updated_trigger`: Updates the updated timestamp on UPDATE using `CURRENT_TIMESTAMP`

#### seo()

```php
public function seo(
    ?string $titleColumn = 'seo_title',
    ?string $descriptionColumn = 'seo_description',
    ?string $changeFrequencyColumn = 'seo_change_frequency',
    ?string $photoColumn = 'seo_photo',
    ?string $priorityColumn = 'seo_priority'
): self
```

Shorthand method for adding standard SEO-related columns to a table. This is a convenient alternative to manually adding each SEO column separately. Pass `null` or an empty string to skip a column.

```php
// Add all SEO columns with default names
$table->seo();

// Add SEO columns with custom names
$table->seo(
    'meta_title',
    'meta_desc',
    'sitemap_freq',
    'image_id',
    'sitemap_priority'
);

// Skip some columns by passing null or empty string
$table->seo(null, 'meta_description', null, 'photo_id', null);
// Only creates: meta_description and photo_id

// Skip all columns (no-op)
$table->seo(null, null, null, null, null);
```

**Creates the following columns (if not skipped):**

- `seo_title` VARCHAR(255) NULL - SEO-optimized page title
- `seo_description` VARCHAR(255) NULL - Meta description for search engines
- `seo_change_frequency` VARCHAR(255) NULL - Sitemap change frequency (e.g., 'daily', 'weekly')
- `seo_photo` INT(11) NOT NULL - Foreign key to media/image table for social sharing
- `seo_priority` DECIMAL(10) NULL - Sitemap priority value (0.0 to 1.0)

**Also creates:**

- Foreign key constraint from `seo_photo` (or custom photo column) to `dry_media_file.id` (if photo column is not skipped)

#### dropSeo()

```php
public function dropSeo(
    ?string $titleColumn = 'seo_title',
    ?string $descriptionColumn = 'seo_description',
    ?string $changeFrequencyColumn = 'seo_change_frequency',
    ?string $photoColumn = 'seo_photo',
    ?string $priorityColumn = 'seo_priority'
): self
```

Shorthand method for dropping SEO-related columns and their constraints (ALTER TABLE only). Pass `null` or an empty string to skip dropping a column.

```php
// Remove all SEO columns with default names
$table->dropSeo();

// Remove SEO columns with custom names
$table->dropSeo(
    'meta_title',
    'meta_desc',
    'sitemap_freq',
    'image_id',
    'sitemap_priority'
);

// Skip some columns by passing null or empty string
$table->dropSeo(null, 'meta_description', null, 'photo_id', null);
// Only drops: meta_description and photo_id (with FK)

// Skip all columns (no-op)
$table->dropSeo(null, null, null, null, null);
```

**Drops the following (if not skipped):**

- Foreign key constraint on `seo_photo` (or custom photo column)
- Column `seo_title`
- Column `seo_description`
- Column `seo_change_frequency`
- Column `seo_photo`
- Column `seo_priority`

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
public function addUnique(string|array $columns): UniqueDefinition
```

Adds a unique constraint. Accepts either a single column name or an array of column names for composite unique constraints.

```php
// Single column unique constraint
$table->addUnique('email');

// Composite unique constraint (multiple columns)
$table->addUnique(['user_id', 'role_id']);

// Custom identifier
$table->addUnique('email')->identifier('custom_email_uq');

// Composite with custom identifier
$table->addUnique(['user_id', 'plan_id'])->identifier('uq_user_subscription');
```

#### dropUnique()

```php
public function dropUnique(string|array $columns): UniqueDefinition
```

Removes a unique constraint (ALTER TABLE only). Accepts either a single column name or an array of column names.

```php
// Drop single column unique constraint
$table->dropUnique('email');

// Drop composite unique constraint
$table->dropUnique(['user_id', 'role_id']);
```

#### dropUniqueByIdentifier()

```php
public function dropUniqueByIdentifier(string $identifier): void
```

Removes a unique constraint by its identifier name (ALTER TABLE only).

```php
$table->dropUniqueByIdentifier('uq_user_subscription');
```

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

### CHECK Constraint Operations

CHECK constraints enforce data integrity by validating column values against specified conditions.

#### addCheck()

```php
public function addCheck(string $column, string $expression): CheckDefinition
```

Adds a CHECK constraint to validate column values. The expression should be the full CHECK condition.

```php
// Simple IN constraint for status values
$table->addCheck('status', '`status` IN (0, 1, 2, 3)');

// Range constraint
$table->addCheck('age', '`age` >= 0 AND `age` <= 150');

// BETWEEN constraint
$table->addCheck('discount', '`discount` BETWEEN 0 AND 100');

// Comparison constraint
$table->addCheck('price', '`price` > 0');

// String IN constraint
$table->addCheck(
    'status',
    '`status` IN (\'pending\', \'active\', \'completed\')'
);

// REGEXP constraint for email validation
$table
    ->addCheck(
        'email',
        '`email` REGEXP \'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$\''
    )
    ->identifier('chk_email_format');

// Custom identifier
$table
    ->addCheck('status', '`status` IN (0, 1, 2, 3)')
    ->identifier('chk_subscriber_status');

// Date comparison (referencing multiple columns)
$table->addCheck('end_date', '`end_date` >= `start_date`');

// Length validation
$table->addCheck('password', 'LENGTH(`password`) >= 8');
```

#### dropCheck()

```php
public function dropCheck(string $column, string $expression = ''): CheckDefinition
```

Removes a CHECK constraint (ALTER TABLE only). Returns a CheckDefinition for custom identifier support.

```php
// Drop by default identifier (chk_{column})
$table->dropCheck('status');

// Drop with custom identifier
$table->dropCheck('email')->identifier('chk_email_format');
```

#### dropCheckByIdentifier()

```php
public function dropCheckByIdentifier(string $identifier): void
```

Removes a CHECK constraint by its identifier name (ALTER TABLE only).

```php
$table->dropCheckByIdentifier('chk_subscriber_status');
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

Sets a default value for the column. Supports literal values and SQL expressions.

**Literal Values:**

```php
$column->default('active'); // String literal
$column->default(0); // Numeric literal
$column->default(null); // NULL value
```

**Expression-Based Defaults (for JSON, TEXT, BLOB, GEOMETRY):**

MySQL requires BLOB, TEXT, GEOMETRY, and JSON data types to use expressions (wrapped in parentheses) for default values. Use the `Raw` class to specify SQL expressions:

```php
use Tnt\Dbi\Raw;

// JSON column with empty array default
$column->type('json')->notNull()->default(new Raw('JSON_ARRAY()'));
// Generates: `column` JSON NOT NULL DEFAULT (JSON_ARRAY())

// JSON column with empty object default
$column->type('json')->notNull()->default(new Raw('JSON_OBJECT()'));
// Generates: `column` JSON NOT NULL DEFAULT (JSON_OBJECT())

// JSON column with pre-populated object
$column
    ->type('json')
    ->notNull()
    ->default(new Raw("JSON_OBJECT('theme', 'light', 'notifications', true)"));
// Generates: `column` JSON NOT NULL DEFAULT (JSON_OBJECT('theme', 'light', 'notifications', true))

// TEXT column with expression default
$column->type('text')->notNull()->default(new Raw('(UUID())'));
// Generates: `column` TEXT NOT NULL DEFAULT ((UUID()))

// BLOB column with expression default
$column->type('blob')->notNull()->default(new Raw('(UNHEX(""))'));
// Generates: `column` BLOB NOT NULL DEFAULT ((UNHEX("")))
```

**Important Notes:**

- Expression-based defaults are automatically wrapped in parentheses as required by MySQL
- Regular string defaults are quoted and escaped automatically
- Expression defaults bypass quoting and escaping (use `Raw` for SQL functions only)
- This feature is essential for JSON columns which cannot have literal string defaults

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

Represents a unique constraint. Supports both single-column and composite (multi-column) unique constraints.

#### identifier()

```php
public function identifier(string $identifierName): self
```

Sets a custom identifier name for the unique constraint.

**Auto-generated identifier format:** `uq_{column}` or `uq_{column1}_{column2}_{...}` for composite constraints

#### getColumns()

```php
public function getColumns(): array
```

Returns the array of column names included in the unique constraint.

#### isComposite()

```php
public function isComposite(): bool
```

Returns `true` if the constraint spans multiple columns, `false` for single-column constraints.

```php
$unique = $table->addUnique(['user_id', 'role_id']);
$unique->isComposite(); // true

$unique = $table->addUnique('email');
$unique->isComposite(); // false
```

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

### CheckDefinition

Represents a CHECK constraint for validating column values.

#### identifier()

```php
public function identifier(string $identifierName): self
```

Sets a custom identifier name for the CHECK constraint.

**Auto-generated identifier format:** `chk_{column}`

#### getColumn()

```php
public function getColumn(): string
```

Returns the column name associated with the CHECK constraint.

#### getExpression()

```php
public function getExpression(): string
```

Returns the CHECK expression/condition.

```php
$check = $table->addCheck('status', '`status` IN (0, 1, 2, 3)');
$check->getColumn(); // 'status'
$check->getExpression(); // '`status` IN (0, 1, 2, 3)'
$check->getIdentifier(); // 'chk_status'

// With custom identifier
$check = $table
    ->addCheck('email', '`email` REGEXP \'^.+@.+$\'')
    ->identifier('chk_email_format');
$check->getIdentifier(); // 'chk_email_format'
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

Auto-generated triggers follow this pattern for both Unix and datetime formats:

- `{table_name}_created_trigger` (for INSERT operations)
- `{table_name}_updated_trigger` (for UPDATE operations)

For example:

- Table `users` → Triggers `users_created_trigger` and `users_updated_trigger`
- Table `blog_posts` → Triggers `blog_posts_created_trigger` and `blog_posts_updated_trigger`

### Best Practices

- Use `timestamps()` for tables that need audit trails
- Use custom column names when integrating with existing schemas
- Always call `dropTimestampTriggers()` before dropping timestamp columns
- When altering timestamp columns, recreate triggers to ensure consistency
- **Unix timestamps (default)** are recommended for:
    - Better performance and storage efficiency (4 bytes vs 8 bytes for TIMESTAMP)
    - Faster integer comparisons
    - Easier timezone handling in application code
- **Datetime format** is better for:
    - Human-readable timestamps in database queries
    - Built-in MySQL date functions
    - Dates beyond 2038 (Unix timestamps are limited to 1970-01-01 to 2038-01-19)

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

### Creating a Table with Timestamps (Unix - Default)

```php
$qb->table('posts')->create(function (TableBuilder $table) {
    $table->addColumn('id', 'int')->primaryKey();
    $table->addColumn('title', 'varchar')->length(255)->notNull();
    $table->addColumn('content', 'text');

    // Use default Unix timestamp format (recommended)
    $table->timestamps('created_at', 'updated_at');
});
```

This creates:

- `created_at INT UNSIGNED NOT NULL`
- `updated_at INT UNSIGNED NOT NULL`
- `posts_created_trigger` - Sets both timestamps on INSERT
- `posts_updated_trigger` - Updates `updated_at` on UPDATE

### Creating a Table with Datetime Timestamps

```php
$qb->table('legacy_posts')->create(function (TableBuilder $table) {
    $table->addColumn('id', 'int')->primaryKey();
    $table->addColumn('title', 'varchar')->length(255)->notNull();
    $table->addColumn('content', 'text');

    // Use datetime format for human-readable timestamps
    $table->timestamps('created_on', 'modified_on', 'datetime');
});
```

This creates:

- `created_on TIMESTAMP NOT NULL`
- `modified_on TIMESTAMP NOT NULL`
- `legacy_posts_created_trigger` - Sets both timestamps on INSERT
- `legacy_posts_updated_trigger` - Updates `modified_on` on UPDATE

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

// Convert from datetime to Unix timestamp format
$qb->table('users')->alter(function (TableBuilder $table) {
    // Drop old datetime columns and triggers
    $table->dropTimestampTriggers();
    $table->dropColumn('created');
    $table->dropColumn('updated');

    // Add new Unix timestamp columns (default format)
    $table->timestamps('created_at', 'updated_at');
});
```

### Working with Unique Constraints

```php
// Create table with unique constraints
$qb->table('user_roles')->create(function (TableBuilder $table) {
    $table->id();
    $table->addColumn('user_id', 'int')->notNull();
    $table->addColumn('role_id', 'int')->notNull();
    $table->addColumn('email', 'varchar')->length(255)->notNull();
    $table->timestamps();

    // Single column unique constraint
    $table->addUnique('email');

    // Composite unique constraint (prevents duplicate user-role combinations)
    $table->addUnique(['user_id', 'role_id']);

    // Composite unique with custom identifier
    $table->addUnique(['user_id', 'email'])->identifier('uq_user_email_combo');

    // Foreign keys
    $table->addForeignKey('user_id', 'users', 'id', 'CASCADE');
    $table->addForeignKey('role_id', 'roles', 'id', 'CASCADE');
});

// Alter table to add/remove unique constraints
$qb->table('user_roles')->alter(function (TableBuilder $table) {
    // Add new composite unique constraint
    $table->addUnique(['user_id', 'role_id', 'created']);

    // Drop single column unique constraint
    $table->dropUnique('email');

    // Drop composite unique constraint
    $table->dropUnique(['user_id', 'role_id']);

    // Drop unique constraint by custom identifier
    $table->dropUniqueByIdentifier('uq_user_email_combo');
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

### Creating a Table with JSON Columns

```php
use Tnt\Dbi\Raw;

$qb->table('user_settings')->create(function (TableBuilder $table) {
    $table->id();
    $table->addColumn('user_id', 'int')->notNull();

    // JSON column with empty array default
    $table
        ->addColumn('tags', 'json')
        ->notNull()
        ->default(new Raw('JSON_ARRAY()'));

    // JSON column with empty object default
    $table
        ->addColumn('metadata', 'json')
        ->notNull()
        ->default(new Raw('JSON_OBJECT()'));

    // JSON column with pre-populated default values
    $table
        ->addColumn('preferences', 'json')
        ->notNull()
        ->default(
            new Raw(
                "JSON_OBJECT('theme', 'light', 'notifications', true, 'language', 'en')"
            )
        );

    // Nullable JSON column with default
    $table
        ->addColumn('custom_data', 'json')
        ->null()
        ->default(new Raw('JSON_ARRAY()'));

    $table->addForeignKey('user_id', 'users', 'id', 'CASCADE');
    $table->timestamps();
});
```

This creates a table with properly configured JSON columns that have expression-based defaults, which is required by MySQL for BLOB, TEXT, GEOMETRY, and JSON data types.

### Working with CHECK Constraints

```php
// Create table with CHECK constraints for data validation
$qb->table('subscribers')->create(function (TableBuilder $table) {
    $table->id();
    $table->addColumn('email', 'varchar')->length(255)->notNull();
    $table->addColumn('status', 'int')->notNull();
    $table->addColumn('age', 'int')->null();
    $table->addColumn('discount', 'decimal')->length('5,2')->notNull();
    $table->timestamps();

    // Validate status is one of allowed values
    $table
        ->addCheck('status', '`status` IN (0, 1, 2, 3)')
        ->identifier('chk_subscriber_status');

    // Validate email format with REGEXP
    $table
        ->addCheck(
            'email',
            '`email` REGEXP \'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$\''
        )
        ->identifier('chk_email_format');

    // Validate age range (if provided)
    $table->addCheck('age', '`age` IS NULL OR (`age` >= 0 AND `age` <= 150)');

    // Validate discount percentage
    $table->addCheck('discount', '`discount` >= 0 AND `discount` <= 100');

    // Unique constraint
    $table->addUnique('email');
});

// Create table with date validation
$qb->table('events')->create(function (TableBuilder $table) {
    $table->id();
    $table->addColumn('name', 'varchar')->length(255)->notNull();
    $table->addColumn('start_date', 'date')->notNull();
    $table->addColumn('end_date', 'date')->notNull();
    $table->timestamps();

    // Ensure end_date is after start_date
    $table
        ->addCheck('end_date', '`end_date` >= `start_date`')
        ->identifier('chk_event_dates');
});

// Create table with string length validation
$qb->table('users')->create(function (TableBuilder $table) {
    $table->id();
    $table->addColumn('username', 'varchar')->length(50)->notNull();
    $table->addColumn('password', 'varchar')->length(255)->notNull();
    $table->timestamps();

    // Validate minimum password length
    $table
        ->addCheck('password', 'LENGTH(`password`) >= 8')
        ->identifier('chk_password_length');

    // Validate username length range
    $table->addCheck('username', 'CHAR_LENGTH(`username`) BETWEEN 3 AND 50');
});

// Alter table to add/remove CHECK constraints
$qb->table('subscribers')->alter(function (TableBuilder $table) {
    // Add new CHECK constraint
    $table
        ->addCheck('status', '`status` IN (0, 1, 2, 3, 4)')
        ->identifier('chk_subscriber_status_v2');

    // Drop CHECK constraint by column (uses default identifier chk_{column})
    $table->dropCheck('age');

    // Drop CHECK constraint by custom identifier
    $table->dropCheckByIdentifier('chk_subscriber_status');
});

// Combining CHECK constraints with other constraints
$qb->table('orders')->create(function (TableBuilder $table) {
    $table->id();
    $table->addColumn('user_id', 'int')->notNull();
    $table->addColumn('status', 'varchar')->length(20)->notNull();
    $table->addColumn('total', 'decimal')->length('10,2')->notNull();
    $table->addColumn('quantity', 'int')->notNull();
    $table->timestamps();

    // Foreign key
    $table->addForeignKey('user_id', 'users', 'id', 'CASCADE');

    // Unique constraint
    $table->addUnique('id');

    // Index for status queries
    $table->addIndex('status');

    // CHECK constraints for data validation
    $table->addCheck('total', '`total` >= 0');
    $table->addCheck('quantity', '`quantity` > 0');
    $table->addCheck(
        'status',
        '`status` IN (\'pending\', \'processing\', \'shipped\', \'delivered\', \'cancelled\')'
    );
});
```

### Complex Join

```php
$qb->leftJoin('user_profiles')
    ->as('profile')
    ->on('users.id', '=', 'profile.user_id')
    ->on('profile.is_active', '=', '1');
```
