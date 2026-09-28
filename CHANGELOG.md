# Changelog

All notable changes to this package are documented in this file. Versions
follow [Semantic Versioning](https://semver.org). New entries are generated from
commit messages by [dry-ci](https://github.com/TallieuTallieu/dry-ci); past
entries may be edited by hand.

## 3.13.3 - 2026-09-28

### Other changes

- Switch to dry-ci

## 3.13.2 - 2026-09-28

No notable changes.

## 3.13.1 - 2026-09-18

### Breaking changes

- Require PHP 8.4 or later ([sc-11481](https://app.shortcut.com/tallieu--tallieu/story/11481))

### Other changes

- **deps:** Support oak 4 (`tallieutallieu/oak` ^3.0.8 or ^4.0)

## 3.13.0 - 2026-08-26

### Features

- **column-definition:** Support per-column collation with `collate()` ([sc-11259](https://app.shortcut.com/tallieu--tallieu/story/11259))

## 3.12.3 - 2026-07-13

### Fixes

- **join-builder:** Support integer SQL literals ([sc-9979](https://app.shortcut.com/tallieu--tallieu/story/9979))

## 3.12.2 - 2026-07-13

### Fixes

- Use fluent return type in JoinBuilder ([sc-9971](https://app.shortcut.com/tallieu--tallieu/story/9971))
- Handle empty IN criteria
- **release:** Make automated releases idempotent ([sc-9972](https://app.shortcut.com/tallieu--tallieu/story/9972))

### Other changes

- Remove repository MCP configuration
- Cover raw criteria and fluent join return
- **ci:** Update GitHub Actions dependencies
- **repository:** Document BaseRepository API

## 3.12.1 - 2026-07-09

### Fixes

- Accept string|Raw column in all column-accepting query criteria

### Other changes

- The same commit is also tagged `3.12.0`

## 3.11.3 - 2026-07-09

### Fixes

- Accept string|Raw column in OrderBy and LessThan criteria

## 3.11.2 - 2026-03-19

### Fixes

- Fix SQL injection in In criteria by using parameterized bindings

## 3.11.1 - 2026-01-08

### Breaking changes

- Tables are now created with collation `utf8mb4_0900_ai_ci` instead of `utf8_unicode_ci`; this collation needs MySQL 8.0 or later ([sc-8992](https://app.shortcut.com/tallieu--tallieu/story/8992))

### Other changes

- Add repository MCP configuration (`.mcp.json`)

## 3.11.0 - 2025-12-09

### Other changes

- Add CHECK constraint documentation ([sc-8911](https://app.shortcut.com/tallieu--tallieu/story/8911))

## 3.10.0 - 2025-12-09

### Features

- Add CheckDefinition class for CHECK constraint support ([sc-8911](https://app.shortcut.com/tallieu--tallieu/story/8911))
- Add CHECK constraint methods to TableBuilder: `addCheck()`, `dropCheck()` and `dropCheckByIdentifier()`

### Other changes

- Add comprehensive unit tests for CheckDefinition
- Add CHECK constraint feature tests to TableBuilderTest

## 3.9.0 - 2025-12-08

### Features

- Add multi-column support to UniqueDefinition ([sc-8864](https://app.shortcut.com/tallieu--tallieu/story/8864))
- Integrate composite unique constraints into TableBuilder: `addUnique()` and `dropUnique()` accept an array of columns, and `dropUniqueByIdentifier()` is new

### Other changes

- Add comprehensive tests for composite unique constraints
- Add feature tests for composite unique constraints in TableBuilder
- Document composite unique constraints support
- Add sync-docs target for Obsidian documentation sync

## 3.8.0 - 2025-11-02

### Features

- Add expression-based default values for JSON/TEXT/BLOB columns ([sc-8626](https://app.shortcut.com/tallieu--tallieu/story/8626))

### Other changes

- Add comprehensive tests for expression-based column defaults
- Document expression-based default values for special column types

## 3.7.0 - 2025-11-02

### Features

- Add seo() and dropSeo() shorthand methods for SEO columns ([sc-8624](https://app.shortcut.com/tallieu--tallieu/story/8624))

### Fixes

- Resolve PHPStan type safety issue in QueryBuilder::buildRename()

### Other changes

- Add comprehensive tests for seo() and dropSeo() methods
- Add documentation for seo() and dropSeo() methods

## 3.6.0 - 2025-10-20

### Other changes

- No code changes: merges the table rename branch ([sc-8513](https://app.shortcut.com/tallieu--tallieu/story/8513)), whose `QueryBuilder::rename()` already shipped in 3.5.2

## 3.5.2 - 2025-10-20

### Features

- Add table rename functionality to QueryBuilder ([sc-8513](https://app.shortcut.com/tallieu--tallieu/story/8513))

### Other changes

- Document table rename functionality

## 3.5.1 - 2025-10-03

### Fixes

- Separate trigger statements to avoid MySQL syntax error ([sc-8398](https://app.shortcut.com/tallieu--tallieu/story/8398))

## 3.5.0 - 2025-10-02

### Breaking changes

- `timestamps()` now creates `INT UNSIGNED` Unix timestamp columns by default; pass `TimestampFormat::DATETIME` to keep `TIMESTAMP` columns ([sc-8395](https://app.shortcut.com/tallieu--tallieu/story/8395))

### Features

- Add TimestampFormat enum for type-safe timestamp configuration
- Add Unix timestamp support with trigger-based implementation

### Other changes

- Update timestamp tests for Unix format and enum usage
- Update timestamp documentation for Unix format and enum

## 3.4.1 - 2025-10-02

### Breaking changes

- Repository subclasses must declare `protected string $model` and `protected function init(): void` ([sc-8366](https://app.shortcut.com/tallieu--tallieu/story/8366))
- Custom criteria must declare `apply(QueryBuilder $queryBuilder): void`
- Criteria constructors only accept a `string` column; Raw columns are accepted again from 3.12.1
- `BaseRepository::amount()` and `orderBy()` now have typed parameters and a `self` return type; overriding methods must match

### Fixes

- Properly inject changelog content in GitHub release body
- Restore parameterized queries for LIMIT and OFFSET

### Other changes

- Add strict type hints to contracts, criteria, repositories, build handlers, QueryBuilder, builders and definitions
- Add PHPStan configuration with strict level 9 analysis
- Add PHPStan and Prettier checks to the CI workflow

## 3.4.0 - 2025-10-02

### Features

- Add IndexDefinition class for database index management ([sc-8292](https://app.shortcut.com/tallieu--tallieu/story/8292))
- Add index management methods to TableBuilder

### Other changes

- Add comprehensive unit tests for IndexDefinition
- Add integration tests for TableBuilder index operations
- Document index management API and usage examples

## 3.3.0 - 2025-10-01

### Features

- Add id() shorthand method for primary key columns ([sc-8345](https://app.shortcut.com/tallieu--tallieu/story/8345))

### Other changes

- Add comprehensive tests for id() shorthand method
- Document id() shorthand method and update examples

## 3.2.1 - 2025-09-30

### Fixes

- Replace deprecated actions/create-release with modern alternative

### Other changes

- Remove 'v' prefix from git tags for consistency
- Simplify release workflow by removing unnecessary logging
- Add comprehensive story creation guidelines to AGENTS.md
- Add comprehensive unit tests for core classes ([sc-8312](https://app.shortcut.com/tallieu--tallieu/story/8312))
- Add unit tests for Repository and CriteriaCollection classes
- Add comprehensive unit tests for QueryBuilder class

## 3.2.0 - 2025-09-30

### Breaking changes

- Require PHP 8.2 or later and `tallieutallieu/oak` ^3.0.8

### Features

- Implement automated tagging system with GitHub Actions ([sc-8322](https://app.shortcut.com/tallieu--tallieu/story/8322))
- Implement branch-based automatic versioning
- Implement intelligent automatic version bump inference

### Fixes

- Fix workflow injection vulnerabilities in GitHub Actions
- Resolve GitHub Actions output format and branch parsing issues

### Other changes

- Released under the tag `v3.2.0`; later tags have no `v` prefix
- Add agent guidelines for commit policy and Shortcut integration
- Remove the version field from composer.json; git tags are the version source
- Simplify release workflow to use git tags as version source

## 3.1.0 - 2025-09-29

### Features

- Add comprehensive type hints and input validation
- Add timestamp triggers support to TableBuilder ([sc-8294](https://app.shortcut.com/tallieu--tallieu/story/8294))
- Change default timestamp columns to 'created' and 'updated'

### Fixes

- Correct README examples and criteria documentation
- Correct typo and improve SQL escaping in schema builder

### Other changes

- Add comprehensive API documentation
- Add agent guidelines for development workflow
- Update gitignore to exclude development documentation
- Add comprehensive timestamp management documentation
- Add Pest testing framework with comprehensive test suite
- Add Docker development environment and build tools
- Update README with testing information and improve gitignore
- Update examples to showcase timestamp functionality
- Add testing section and update file structure

## 3.0.1 - 2025-09-23

### Breaking changes

- Require PHP 8.1 or later and `tallieutallieu/oak` ^3.0.6

### Other changes

- Modernize nullable type hints

## 3.0.0 - 2025-08-29

### Breaking changes

- **deps:** Require `tallieutallieu/oak` ^3.0.2; oak 1.x is no longer supported

## Earlier history

- **1.0.0** (2019-10-07): First tagged release as `dietervyncke/dry-dbi`: repositories with criteria (Equals, GreaterThan, IsNull, OrderBy, LimitOffset, …), QueryBuilder, JoinBuilder and Raw statements on top of oak.
- **1.0.1** (2019-11-06): Schema building with TableBuilder and ColumnDefinition.
- **1.0.2–1.0.6** (2019-11-07 to 2020-01-16): Foreign key and unique constraint fixes; 1.0.6 adds methods to change unique indexes on tables.
- **1.0.7** (2020-10-22): QueryBuilder prevents duplicate join tables.
- **1.0.8** (2021-04-15): Package moves to TallieuTallieu as `tallieutallieu/dry-dbi` on `tallieutallieu/oak`, and BaseRepository gets `orderBy()`.
- **1.0.9–1.0.10** (2023-08-24): ON DELETE and ON UPDATE for foreign keys, plus a hotfix.
- **1.0.11–1.0.12** (2024-11-18 to 2024-12-05): Column default value handling, including DEFAULT NULL; oak `dev-php8.2` is allowed.
- **1.0.13** (2024-12-20): In and NotNull criteria.
- **1.0.14–1.0.16** (2025-01-29 to 2025-02-04): GENERATED option on ColumnDefinition, In criteria fix for string values, and OrEquals criteria.
- **1.0.17–1.0.20** (2025-04-25): Table alias for JoinBuilder and follow-up fixes.
- **1.0.21** (2025-04-30): `Repository::create()`.
- **1.0.22–1.0.23** (2025-06-18 to 2025-09-08): Allows oak 1.1; composer.json cleanup. The 1.0.x line continued next to 3.x.
- **1.0.24** (2026-03-19): Only on the `old-dry` branch; backports the In criteria SQL injection fix from 3.11.2.

See the git tags before 3.0.0 for the full history.
