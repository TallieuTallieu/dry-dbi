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

### Test Files

- `Feature/TimestampsTest.php` - Tests for the timestamp functionality (triggers, column creation, cleanup)
- `Feature/TableBuilderTest.php` - Tests for basic TableBuilder functionality (columns, constraints, foreign keys)

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

### Timestamps Functionality
- ✅ CREATE TABLE with default timestamps (`created_at`, `updated_at`)
- ✅ CREATE TABLE with custom timestamp columns
- ✅ ALTER TABLE adding timestamps with trigger creation
- ✅ ALTER TABLE dropping timestamp triggers and columns
- ✅ Generated trigger name validation
- ✅ Complex tables with timestamps and other constraints
- ✅ Mixed ALTER TABLE operations with timestamps
- ✅ Timestamp configuration updates

### Basic TableBuilder Functionality
- ✅ Basic CREATE TABLE operations
- ✅ Column types and constraints (varchar, decimal, text, boolean)
- ✅ Foreign key constraints with CASCADE options
- ✅ Unique constraints
- ✅ ALTER TABLE operations (add, change, drop columns)

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

The test suite includes code coverage reporting via Xdebug. Current coverage highlights:

- **TableBuilder: 90.2%** - Comprehensive timestamp and basic functionality tests
- **ColumnDefinition: 80.7%** - Well covered through TableBuilder integration
- **ForeignKeyDefinition: 100%** - Fully tested
- **UniqueDefinition: 100%** - Fully tested
- **Overall: 30.7%** - Good coverage for tested components

### Coverage Configuration

Xdebug is configured with coverage mode in `docker/xdebug/xdebug.ini`:
```ini
xdebug.mode=develop,debug,coverage
```

## Future Improvements

- Add tests for QueryBuilder and Repository classes (currently 0% coverage)
- Add tests for Criteria classes (currently 0% coverage)
- Add integration tests with actual database connections
- Add performance benchmarks
- Add HTML coverage reports with `--coverage-html`
- Add automated test execution on CI/CD
- Add dataset testing for multiple scenarios
- Add snapshot testing for complex SQL generation
- Increase overall coverage target to 80%+