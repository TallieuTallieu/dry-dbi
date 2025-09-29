# Release Process Documentation

This document describes the automated release and tagging system for the dry-dbi project using GitHub Actions.

## Overview

The project uses an automated release system that supports:
- **Branch-based versioning** - Automatically extracts version from branch names
- Semantic versioning (major.minor.patch)
- Automatic tagging and release creation
- Changelog generation
- Manual and automatic triggers
- Integration with CI/CD pipeline

## Branch-Based Versioning

The system automatically extracts version information from branch names using these patterns:

| Branch Pattern | Example | Description |
|---|---|---|
| `release/v3.2.0` | `release/v3.2.0` | Standard release branch |
| `release/3.2.0` | `release/3.2.0` | Release branch without 'v' prefix |
| `hotfix/v3.1.1` | `hotfix/v3.1.1` | Hotfix branch for patch releases |
| `hotfix/3.1.1` | `hotfix/3.1.1` | Hotfix branch without 'v' prefix |
| `version/3.2.0` | `version/3.2.0` | Version-specific branch |
| `v3.2.0` | `v3.2.0` | Direct version branch |
| `feature/v3.2.0-*` | `feature/v3.2.0-new-api` | Feature branch with version |
| `main` | `main` | Uses version from composer.json |

## Release Workflows

### 1. Automatic Release (Branch-Based)

When you push to any supported branch pattern, the release workflow will automatically:

1. Extract the version from the branch name
2. Validate semantic versioning format
3. Update `composer.json` with the extracted version
4. Generate changelog from git commits
5. Create a git tag
6. Create a GitHub release with release notes

**Steps to create a release branch:**

```bash
# 1. Create and checkout release branch
git checkout -b release/v3.2.0

# 2. Make your changes (optional)
# ... make changes ...

# 3. Push the branch
git push origin release/v3.2.0
# This automatically triggers the release workflow
```

**Steps to create a hotfix:**

```bash
# 1. Create hotfix branch from main
git checkout main
git checkout -b hotfix/v3.1.1

# 2. Fix the issue
# ... make fixes ...
git commit -m "fix: resolve critical bug"

# 3. Push the branch
git push origin hotfix/v3.1.1
# This automatically triggers the release workflow
```

### 2. Manual Release (workflow dispatch)

You can manually trigger a release from the GitHub Actions tab:

1. Go to **Actions** → **Automated Release and Tagging**
2. Click **Run workflow**
3. Select any branch (version will be extracted from branch name)
4. Optionally enter a version to override the branch-based version
5. Choose release type (release or prerelease)
6. Click **Run workflow**

The manual release will:
- Extract version from branch name (or use provided override)
- Update `composer.json` with the version
- Commit the version change to the current branch
- Create tag and release

**Manual version override example:**
- Branch: `feature/new-api` 
- Manual version input: `3.2.0-beta.1`
- Result: Creates release `v3.2.0-beta.1`

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

### Example 1: Patch Release (Hotfix)

```bash
# Create hotfix branch
git checkout main
git checkout -b hotfix/v3.1.1

# Fix the bug
git commit -m "fix: resolve query builder parameter binding"

# Push hotfix branch - automatically triggers release
git push origin hotfix/v3.1.1
```

### Example 2: Minor Release with New Feature

```bash
# Create release branch
git checkout main
git checkout -b release/v3.2.0

# Add new feature (or merge from feature branches)
git commit -m "feat: add new criteria for complex queries"

# Push release branch - automatically triggers release
git push origin release/v3.2.0
```

### Example 3: Feature Branch with Version

```bash
# Create feature branch with version
git checkout -b feature/v3.2.0-new-api

# Implement feature
git commit -m "feat: implement new API endpoints"

# Push feature branch - automatically triggers release
git push origin feature/v3.2.0-new-api
```

### Example 4: Manual Pre-release

1. Create any branch (e.g., `feature/beta-testing`)
2. Go to GitHub Actions → Automated Release and Tagging
3. Click "Run workflow"
4. Select your branch
5. Enter version: `4.0.0-beta.1`
6. Select "prerelease"
7. Click "Run workflow"

This creates a pre-release from any branch with a custom version.