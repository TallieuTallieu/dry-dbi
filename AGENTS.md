# Agent Guidelines for dry-dbi

## Commit Policy

- **NEVER commit changes without explicit permission**
- Always ask the user before running `git add`, `git commit`, or `git push`
- Present a summary of changes and wait for confirmation
- Exception: Only commit if the user explicitly requests it with commands like "commit this" or "push the changes"

## Shortcut Integration

- **Epic ID**: 8287 (DRY-dbi) - [View Epic](https://app.shortcut.com/tallieu--tallieu/epic/8287)
- **Workflow**: DRY (ID: 500000052)
- **Team**: webdev (ID: 5f05c15d-7e01-4911-80dc-2f6094ee7f1f)
- When user says "Let's tackle story XXXX", use `shortcut_stories_get_by_id` to get story details
- Create comprehensive todo lists using `todowrite` for complex multi-step tasks
- Update todos as work progresses, marking tasks as in_progress/completed
- Add detailed comments to stories using `shortcut_stories_create_comment` when work is completed
- Include implementation details, features delivered, and acceptance criteria status in comments
- Use emojis and clear formatting in Shortcut comments for better readability
- **NEVER change story states** - story state changes happen automatically through git integration

### Creating Stories

#### Basic Requirements

- Use `shortcut_stories_create` to create new stories when requested
- **Name is required** - use clear, descriptive titles
- **Team or Workflow must be specified**:
  - If only Team is specified, the default workflow for that team will be used
  - If Workflow is specified, it will be used regardless of Team
- **Story types**: `feature` (default), `bug`, or `chore`
- Stories are automatically added to the default state for the workflow
- Always link stories to the epic: `epic: 8287`

#### Naming Convention

All story names MUST follow this pattern: `dry-dbi: [Feature/Component Name]`

**Examples from existing stories:**

- ✅ `dry-dbi: Auto-tagging through GitHub Actions`
- ✅ `dry-dbi: Timestamp Triggers Support`
- ✅ `dry-dbi: JSON Column Support`
- ✅ `dry-dbi: Migration Patterns Documentation`
- ✅ `dry-dbi: Comprehensive Unit Tests`
- ❌ `Add JSON support` (missing prefix)
- ❌ `DRY-dbi JSON support` (wrong format)

#### Story Type Guidelines

- **`feature`**: New functionality, enhancements, or additions
  - Examples: "JSON Column Support", "Timestamp Triggers Support", "Batch Operations Support"
- **`chore`**: Documentation, tests, tooling, maintenance
  - Examples: "Migration Patterns Documentation", "Comprehensive Unit Tests", "Performance Benchmarks"
- **`bug`**: Bug fixes, corrections, hotfixes
  - Use for fixing existing functionality

#### Description Structure

Every story description MUST include:

1. **One-line summary** (what the story is about)
2. **Priority label** with category
3. **Details section** (expanded explanation)
4. **Acceptance Criteria** (bulleted list)

**Template:**

```
[One-line summary of the feature/task]

**Priority**: [Priority Level] - [Category]

**Details**:
[Detailed explanation of what needs to be implemented]

**Acceptance Criteria**:
- [Specific, testable criterion 1]
- [Specific, testable criterion 2]
- [Specific, testable criterion 3]
- Add documentation and examples
```

#### Priority Categories

Use these priority patterns observed in existing stories:

- **High Priority - Missing Core Functionality**: Critical features needed for basic operation
- **Medium Priority - DevOps & Automation**: CI/CD, tooling, automation
- **Medium Priority - Data Types**: Database type support, schema features
- **Low Priority - Testing & Quality Assurance**: Tests, validation, quality improvements
- **Documentation Improvements - API Documentation**: Documentation tasks

#### Acceptance Criteria Best Practices

- Always include 4-7 specific, testable criteria
- End with "Add documentation and examples" or "Add comprehensive documentation"
- Be specific about what needs to be implemented
- Include edge cases and validation requirements
- Mention integration points if applicable

**Example from existing story:**

```
**Acceptance Criteria**:
- Add `timestamps()` method to `TableBuilder` for created/updated columns
- Generate MySQL triggers to automatically update timestamps on INSERT/UPDATE
- Support customizable column names (e.g., `timestamps('created_on', 'modified_on')`)
- Handle trigger creation, modification, and cleanup during table alterations
- Add comprehensive documentation and examples
```

#### Story Creation Checklist

Before creating a story, ensure:

- [ ] Name starts with `dry-dbi:` prefix
- [ ] Story type is appropriate (`feature`, `chore`, or `bug`)
- [ ] Description includes priority label
- [ ] Description has "Details" section
- [ ] Acceptance criteria are specific and testable
- [ ] Documentation is mentioned in acceptance criteria
- [ ] Epic ID 8287 is set
- [ ] Team (webdev) or Workflow (500000052) is specified

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
