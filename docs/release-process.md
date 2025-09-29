# Release Process Documentation

This document describes the automated release and tagging system for the dry-dbi project using GitHub Actions.

## Overview

The project uses an automated release system that supports:
- Semantic versioning (major.minor.patch)
- Automatic tagging and release creation
- Changelog generation
- Manual and automatic triggers
- Integration with CI/CD pipeline

## Release Workflows

### 1. Automatic Release (composer.json changes)

When you push changes to the `main` branch that include modifications to `composer.json`, the release workflow will automatically:

1. Extract the version from `composer.json`
2. Validate semantic versioning format
3. Generate changelog from git commits
4. Create a git tag
5. Create a GitHub release with release notes

**Steps to trigger automatic release:**

```bash
# 1. Update version in composer.json
vim composer.json  # Change version field

# 2. Commit and push to main
git add composer.json
git commit -m "chore: bump version to 3.2.0"
git push origin main
```

### 2. Manual Release (workflow dispatch)

You can manually trigger a release from the GitHub Actions tab:

1. Go to **Actions** → **Automated Release and Tagging**
2. Click **Run workflow**
3. Select the `main` branch
4. Enter the version (e.g., `3.2.0`, `4.0.0-beta.1`)
5. Choose release type (release or prerelease)
6. Click **Run workflow**

The manual release will:
- Update `composer.json` with the specified version
- Commit the version change
- Create tag and release (same as automatic)

## Semantic Versioning

The system follows [Semantic Versioning 2.0.0](https://semver.org/):

- **MAJOR** version (`X.0.0`): Incompatible API changes
- **MINOR** version (`X.Y.0`): Backwards-compatible functionality additions
- **PATCH** version (`X.Y.Z`): Backwards-compatible bug fixes

### Pre-release Versions

Pre-release versions are supported with suffixes:
- `3.2.0-alpha.1`
- `3.2.0-beta.2`
- `3.2.0-rc.1`

Pre-release versions are automatically marked as "prerelease" in GitHub.

## Changelog Generation

The system automatically generates changelogs by:

1. Finding the latest existing tag
2. Collecting all commits since that tag
3. Formatting them as a bulleted list
4. Adding installation instructions

### Commit Message Best Practices

For better changelogs, use conventional commit messages:

```bash
git commit -m "feat: add new query builder method"
git commit -m "fix: resolve timestamp column issue"
git commit -m "docs: update API documentation"
git commit -m "chore: update dependencies"
```

## CI/CD Integration

### Continuous Integration

The `ci.yml` workflow runs on every push and pull request:

- **Multi-PHP Testing**: Tests against PHP 8.1, 8.2, and 8.3
- **Code Quality**: Validates composer.json, runs static analysis
- **Security**: Checks for known vulnerabilities
- **Test Coverage**: Runs Pest/PHPUnit tests with coverage

### Release Validation

The release workflow includes:

- Dependency installation and validation
- Test execution (if available)
- Composer validation
- Version format validation

## File Structure

```
.github/
├── workflows/
│   ├── ci.yml          # Continuous integration
│   └── release.yml     # Automated release and tagging
```

## Configuration

### Required Permissions

The release workflow requires:
- `contents: write` - To create tags and releases
- `pull-requests: read` - To read PR information

### Environment Variables

- `GITHUB_TOKEN` - Automatically provided by GitHub Actions

## Troubleshooting

### Common Issues

1. **"Tag already exists"**
   - The version you're trying to release already has a tag
   - Check existing tags: `git tag --list`
   - Use a different version number

2. **"Invalid semantic version format"**
   - Ensure version follows `X.Y.Z` or `X.Y.Z-suffix` format
   - Examples: `1.0.0`, `2.1.3-beta.1`

3. **"No changes to commit"**
   - When using manual dispatch, the version might already be set
   - This is not an error, the workflow will continue

### Debugging

1. Check the Actions tab for workflow run details
2. Review the workflow summary for release information
3. Verify the generated changelog in the release notes

## Best Practices

### Version Bumping Strategy

- **Patch** (`X.Y.Z+1`): Bug fixes, documentation updates
- **Minor** (`X.Y+1.0`): New features, backwards-compatible changes
- **Major** (`X+1.0.0`): Breaking changes, API modifications

### Release Timing

- Create releases from the `main` branch only
- Ensure all tests pass before releasing
- Review the generated changelog before finalizing
- Use pre-release versions for testing new features

### Maintenance

- Regularly review and update the workflows
- Monitor for security updates in GitHub Actions
- Keep documentation synchronized with workflow changes

## Examples

### Example 1: Patch Release

```bash
# Fix a bug
git checkout -b fix/query-builder-bug
# ... make changes ...
git commit -m "fix: resolve query builder parameter binding"

# Merge to main
git checkout main
git merge fix/query-builder-bug

# Update version for patch release
vim composer.json  # Change "3.1.0" to "3.1.1"
git commit -m "chore: bump version to 3.1.1"
git push origin main  # Triggers automatic release
```

### Example 2: Minor Release with New Feature

```bash
# Add new feature
git checkout -b feature/new-criteria
# ... implement feature ...
git commit -m "feat: add new criteria for complex queries"

# Merge to main
git checkout main
git merge feature/new-criteria

# Update version for minor release
vim composer.json  # Change "3.1.1" to "3.2.0"
git commit -m "chore: bump version to 3.2.0"
git push origin main  # Triggers automatic release
```

### Example 3: Manual Pre-release

1. Go to GitHub Actions → Automated Release and Tagging
2. Click "Run workflow"
3. Enter version: `4.0.0-beta.1`
4. Select "prerelease"
5. Click "Run workflow"

This creates a pre-release that users can test before the final `4.0.0` release.