# DRY DBI Documentation

A PHP 8.1+ database abstraction library providing a clean, fluent interface for database operations using the Repository pattern with composable criteria.

## Quick Start

```php
// Create a repository
class UserRepository extends BaseRepository
{
    protected $model = User::class;
    
    public function active(): self
    {
        $this->addCriteria(new IsTrue('is_active'));
        return $this;
    }
}

// Use the repository
$users = UserRepository::create()
    ->active()
    ->orderBy('name')
    ->amount(10)
    ->get();
```

## Core Components

### [Repository Pattern](repository.md)
- **[Repository](repository.md#repository-abstract)** - Abstract base class for all repositories
- **[BaseRepository](repository.md#baserepository)** - Common repository functionality with fluent interface
- Repository lifecycle and query execution

### [Query Building](query-builder.md)
- **[QueryBuilder](query-builder.md)** - Fluent SQL query construction
- SELECT, WHERE, JOIN, ORDER BY, GROUP BY operations
- Schema operations (CREATE, ALTER, DROP)
- Parameter binding and SQL injection protection

### [Criteria System](criteria.md)
- **[Comparison Criteria](criteria.md#comparison-criteria)** - Equals, GreaterThan, LessThan, etc.
- **[Null Checks](criteria.md#null-checks)** - IsNull, NotNull
- **[Boolean Checks](criteria.md#boolean-checks)** - IsTrue, IsFalse
- **[List Operations](criteria.md#list-operations)** - In clause support
- **[Ordering & Limiting](criteria.md#ordering-and-limiting)** - OrderBy, GroupBy, LimitOffset
- Custom criteria creation

### [Schema Management](schema-builders.md)
- **[TableBuilder](schema-builders.md#tablebuilder)** - CREATE and ALTER table operations
- **[ColumnDefinition](schema-builders.md#columndefinition)** - Column types, constraints, and properties
- **[JoinBuilder](schema-builders.md#joinbuilder)** - JOIN operations with conditions
- **[Timestamp Management](schema-builders.md#timestamp-management)** - Automatic timestamp triggers and column management
- Foreign keys and unique constraints

### [Raw SQL](raw-statements.md)
- **[Raw Statements](raw-statements.md)** - Custom SQL expressions with parameter binding
- Integration with QueryBuilder and Criteria
- Safe parameter binding practices

### [Architecture](contracts.md)
- **[Contracts](contracts.md)** - Interfaces defining component behavior
- **[BuildHandler](build-handler.md)** - Base class for query builders
- Extensibility and customization points

## Advanced Topics

### Custom Criteria

```php
class BetweenDates implements CriteriaInterface
{
    public function apply(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where('created_at', '>=', $this->startDate)
                     ->where('created_at', '<=', $this->endDate);
    }
}
```

### Complex Queries

```php
$repository->useQueryBuilder(function(QueryBuilder $qb) {
    $qb->leftJoin('categories')
       ->on('products.category_id', '=', 'categories.id')
       ->whereGroup(function($qb) {
           $qb->where('status', '=', 'active')
              ->where('featured', '=', true, 'OR');
       });
});
```

### Schema Operations

```php
QueryBuilder::create()
    ->table('users')
    ->create(function(TableBuilder $table) {
        $table->addColumn('id', 'int')->primaryKey();
        $table->addColumn('email', 'varchar')->length(255);
        $table->addForeignKey('role_id', 'roles');
        $table->addUnique('email');
        
        // Add automatic timestamp management
        $table->timestamps();
    });
```

## Installation

```bash
composer require tallieutallieu/dry-dbi
```

## Requirements

- PHP 8.1+
- tallieutallieu/oak framework

## Testing

The library includes a comprehensive test suite using [Pest PHP](https://pestphp.com/):

```bash
# Run all tests
make test

# Run tests with coverage
make test-coverage
```

See [tests/README.md](../tests/README.md) for detailed testing information.

## Architecture Overview

The library follows these key patterns:

- **Repository Pattern**: Clean separation between data access and business logic
- **Criteria Pattern**: Composable, reusable query conditions
- **Builder Pattern**: Fluent interfaces for query and schema construction
- **Interface Segregation**: Small, focused interfaces for extensibility

## File Structure

```
src/
├── Contracts/           # Interfaces
├── Criteria/           # Query criteria implementations
├── BaseRepository.php  # Common repository functionality
├── Repository.php      # Abstract repository base
├── QueryBuilder.php    # SQL query construction
├── BuildHandler.php    # Base builder functionality
├── TableBuilder.php    # Schema operations
├── JoinBuilder.php     # JOIN operations
├── Raw.php            # Raw SQL statements
└── ...                # Supporting classes

tests/
├── Feature/            # Feature tests
├── Unit/              # Unit tests
├── Pest.php           # Test configuration
└── README.md          # Testing documentation
```