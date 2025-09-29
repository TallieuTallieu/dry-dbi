# Agent Guidelines for dry-dbi

## Build/Test Commands
- No test scripts defined in composer.json
- Use `composer install` to install dependencies
- Use `composer dump-autoload` to regenerate autoloader
- No lint/test commands available - verify manually

## Project Structure
- PHP 8.1+ library for database abstraction
- PSR-4 autoloading: `Tnt\Dbi\` → `src/`
- Depends on `tallieutallieu/oak` framework

## Code Style
- **Namespace**: All classes use `Tnt\Dbi` namespace
- **Imports**: Group use statements, no blank lines between imports
- **Properties**: Use private visibility with docblock types (`@var string`)
- **Methods**: Public fluent interface returning `$this` or specific types
- **Spacing**: Single blank line after opening PHP tag and before namespace
- **Braces**: Opening brace on same line for methods, new line for classes
- **Variables**: Use descriptive names like `$queryBuilder`, `$createScheme`

## Architecture Patterns
- Repository pattern with `BaseRepository` and criteria system
- Builder pattern for `QueryBuilder` and `TableBuilder`
- Interface segregation with contracts in `Contracts/` directory
- Criteria pattern for composable query conditions in `Criteria/` directory

## Documentation
- **Location**: [docs/](docs/) directory with comprehensive API documentation
- **Entry Point**: [docs/index.md](docs/index.md) - main documentation hub
- **Keep Updated**: When adding/modifying classes, update corresponding docs
- **Coverage**: Document all public methods, classes, and usage patterns