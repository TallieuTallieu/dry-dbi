# DRY-DBI Tests

This directory contains tests for the dry-dbi library using [Pest PHP](https://pestphp.com/).

## Running Tests

### Using Make (Recommended)

```bash
# Run all tests
make test

# Run tests with verbose output
make test-verbose

# Run tests with coverage report
make test-coverage
```

This will run all tests through Docker, ensuring a consistent environment.

### Manual Execution

```bash
# Run all tests
docker compose exec -T dry-dbi-dev ./vendor/bin/pest

# Run tests with verbose output
docker compose exec -T dry-dbi-dev ./vendor/bin/pest --verbose

# Run specific test file
docker compose exec -T dry-dbi-dev ./vendor/bin/pest tests/Feature/TimestampsTest.php

# Run tests with coverage
docker compose exec -T dry-dbi-dev ./vendor/bin/pest --coverage
```

## Test Structure

### Feature Tests

- `Feature/TableBuilderTest.php` - Comprehensive TableBuilder tests (columns, constraints, foreign keys, indexes)
- `Feature/TimestampsTest.php` - Timestamp functionality tests (triggers, column creation, cleanup)

### Unit Tests

- `Unit/ColumnDefinitionTest.php` - Column definition and type tests
- `Unit/CriteriaCollectionTest.php` - Criteria collection management tests
- `Unit/CriteriaTest.php` - Individual criteria tests (Equals, GreaterThan, In, etc.)
- `Unit/ForeignKeyDefinitionTest.php` - Foreign key constraint tests
- `Unit/IndexDefinitionTest.php` - Index definition tests
- `Unit/JoinBuilderTest.php` - SQL JOIN builder tests
- `Unit/QueryBuilderTest.php` - Query builder tests (SELECT, WHERE, JOIN, etc.)
- `Unit/RawTest.php` - Raw SQL statement tests
- `Unit/RepositoryTest.php` - Repository pattern tests
- `Unit/UniqueDefinitionTest.php` - Unique constraint tests

### Adding New Tests

Pest uses a simple, expressive syntax for writing tests. Create new test files in the appropriate directory:

- `tests/Feature/` - For feature/integration tests
- `tests/Unit/` - For unit tests

### Test File Pattern

```php
<?php

use Tnt\Dbi\TableBuilder;

describe('Your Feature', function () {
  it('does something specific', function () {
    // Arrange
    $tableBuilder = new TableBuilder(false);
    $tableBuilder->table('test_table');

    // Act
    $tableBuilder->addColumn('id', 'int')->primaryKey();
    $tableBuilder->build();

    // Assert
    $query = $tableBuilder->getQuery();
    expect($query)->toContain('`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
  });

  it('handles another scenario', function () {
    // Your test logic here
    expect(true)->toBe(true);
  });
});
```

### Pest Features Used

- **Describe blocks**: Group related tests together
- **It blocks**: Individual test cases with descriptive names
- **Expect API**: Fluent assertion syntax
- **Automatic discovery**: Pest automatically finds and runs `*Test.php` files

## Test Coverage

Current test coverage includes:

### Core Components

- ✅ **TableBuilder** - CREATE/ALTER TABLE operations, columns, constraints, indexes, timestamps
- ✅ **QueryBuilder** - SELECT, WHERE, JOIN, GROUP BY, ORDER BY, LIMIT/OFFSET
- ✅ **Repository** - Repository pattern, criteria application, query execution
- ✅ **Criteria System** - All criteria types (Equals, In, GreaterThan, IsNull, etc.)
- ✅ **JoinBuilder** - INNER, LEFT, RIGHT JOIN operations
- ✅ **Column Definitions** - All column types and constraints
- ✅ **Foreign Keys** - Constraint creation with CASCADE options
- ✅ **Indexes** - Index creation and management
- ✅ **Unique Constraints** - Unique constraint definitions
- ✅ **Raw SQL** - Raw statement handling
- ✅ **Timestamps** - Automatic timestamp triggers and column management

## Pest Configuration

The test suite is configured in `tests/Pest.php`:

- Uses `Tests\TestCase` as the base test case
- Applies to both `Feature` and `Unit` test directories
- Custom expectations and helper functions can be added here

## Available Pest Commands

```bash
# Run all tests
./vendor/bin/pest

# Run with verbose output
./vendor/bin/pest --verbose

# Run specific test file
./vendor/bin/pest tests/Feature/TimestampsTest.php

# Run tests matching a pattern
./vendor/bin/pest --filter="timestamp"

# Run tests with coverage
./vendor/bin/pest --coverage

# Run tests with detailed coverage report
./vendor/bin/pest --coverage --min=80

# Run tests in parallel (if configured)
./vendor/bin/pest --parallel

# Watch for file changes and re-run tests
./vendor/bin/pest --watch
```

## Coverage Report

The test suite includes code coverage reporting via Xdebug. Run coverage with:

```bash
make test-coverage
# or
docker compose exec -T dry-dbi-dev ./vendor/bin/pest --coverage
```

### Coverage Configuration

Xdebug is configured with coverage mode in `docker/xdebug/xdebug.ini`:

```ini
xdebug.mode=develop,debug,coverage
```

## Future Improvements

- Add integration tests with actual database connections
- Add performance benchmarks
- Add HTML coverage reports with `--coverage-html`
- Add automated test execution on CI/CD
- Add dataset testing for multiple scenarios
- Add snapshot testing for complex SQL generation
