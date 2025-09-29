# Agent Guidelines for dry-dbi

## Commit Policy
- **NEVER commit changes without explicit permission**
- Always ask the user before running `git add`, `git commit`, or `git push`
- Present a summary of changes and wait for confirmation
- Exception: Only commit if the user explicitly requests it with commands like "commit this" or "push the changes"

## Shortcut Integration
- When user says "Let's tackle story XXXX", use `shortcut_stories_get_by_id` to get story details
- Create comprehensive todo lists using `todowrite` for complex multi-step tasks
- Update todos as work progresses, marking tasks as in_progress/completed
- Add detailed comments to stories using `shortcut_stories_create_comment` when work is completed
- Include implementation details, features delivered, and acceptance criteria status in comments
- Use emojis and clear formatting in Shortcut comments for better readability
- **NEVER change story states** - story state changes happen automatically through git integration

### Creating Stories
- Use `shortcut_stories_create` to create new stories when requested
- **Name is required** - use clear, descriptive titles
- **Team or Workflow must be specified**:
  - If only Team is specified, the default workflow for that team will be used
  - If Workflow is specified, it will be used regardless of Team
- **Story types**: `feature` (default), `bug`, or `chore`
- Stories are automatically added to the default state for the workflow
- Include detailed descriptions with acceptance criteria when possible

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
