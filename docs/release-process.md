# Release Process Documentation

This document describes the automated release and tagging system for the dry-dbi project using GitHub Actions.

## Overview

The project uses an **intelligent automated release system** that:
- **Automatically infers version bump type** from branch names
- Supports semantic versioning (major.minor.patch)
- Creates automatic tagging and releases
- Generates changelogs from git commits
- Works seamlessly with Shortcut-generated branches
- Supports manual overrides when needed

## 🧠 Automatic Version Bump Inference

The system automatically determines the version bump type based on your branch name prefix:

| Branch Prefix | Bump Type | Example | Use Case |
|---|---|---|---|
| `feature/`, `feat/` | **MINOR** | 3.1.0 → 3.2.0 | New features |
| `enhancement/`, `improve/` | **MINOR** | 3.1.0 → 3.2.0 | Enhancements |
| `add/`, `update/` | **MINOR** | 3.1.0 → 3.2.0 | Additions/updates |
| `bug/`, `fix/` | **PATCH** | 3.1.0 → 3.1.1 | Bug fixes |
| `hotfix/`, `patch/` | **PATCH** | 3.1.0 → 3.1.1 | Critical fixes |
| `bugfix/` | **PATCH** | 3.1.0 → 3.1.1 | Bug fixes |
| `breaking/`, `major/` | **MAJOR** | 3.1.0 → 4.0.0 | Breaking changes |
| `break/`, `bc-break/` | **MAJOR** | 3.1.0 → 4.0.0 | Breaking changes |
| `breaking-change/` | **MAJOR** | 3.1.0 → 4.0.0 | Breaking changes |
| `chore/`, `docs/` | **PATCH** | 3.1.0 → 3.1.1 | Maintenance |
| `style/`, `refactor/` | **PATCH** | 3.1.0 → 3.1.1 | Code improvements |
| `test/` | **PATCH** | 3.1.0 → 3.1.1 | Test updates |
| `release/` | **MINOR** | 3.1.0 → 3.2.0 | Release branches |
| `main` | **PATCH** | 3.1.0 → 3.1.1 | Main branch |

### 🎫 Shortcut Integration

The system recognizes Shortcut's branch naming pattern `feature/sc-XXXX--description` and automatically applies **MINOR** version bumps for these branches.

## 🚀 Automatic Release Workflow

When you push to **any branch**, the system will:

1. **Analyze the branch name** to determine version bump type
2. **Calculate the new version** based on current version + bump type
3. **Update `composer.json`** with the new version
4. **Generate changelog** from git commits since last release
5. **Create a git tag** with the new version
6. **Create a GitHub release** with formatted release notes

### ✨ Feature Development (MINOR bump)

```bash
# Shortcut generates this automatically
git checkout -b feature/sc-8322--new-api-endpoints

# Make your changes
git commit -m "feat: add new API endpoints"

# Push the branch
git push origin feature/sc-8322--new-api-endpoints
# ✅ Automatically creates release: 3.1.0 → 3.2.0
```

### 🐛 Bug Fixes (PATCH bump)

```bash
# Create bug fix branch
git checkout -b bug/fix-query-builder

# Fix the issue
git commit -m "fix: resolve query builder parameter binding"

# Push the branch
git push origin bug/fix-query-builder
# ✅ Automatically creates release: 3.1.0 → 3.1.1
```

### 💥 Breaking Changes (MAJOR bump)

```bash
# Create breaking change branch
git checkout -b breaking/new-api-structure

# Implement breaking changes
git commit -m "feat!: redesign API structure"

# Push the branch
git push origin breaking/new-api-structure
# ✅ Automatically creates release: 3.1.0 → 4.0.0
```

### 🎛️ Manual Release Override

You can manually trigger a release or override the automatic detection:

1. Go to **Actions** → **Automated Release and Tagging**
2. Click **Run workflow**
3. Select any branch
4. **Optional overrides:**
   - **Version**: Specify exact version (e.g., `3.2.0-beta.1`)
   - **Bump Type**: Override auto-detection (`patch`, `minor`, `major`)
   - **Release Type**: Choose `release` or `prerelease`
5. Click **Run workflow**

**Manual override examples:**

| Scenario | Branch | Override | Result |
|---|---|---|---|
| Beta release | `feature/new-api` | Version: `3.2.0-beta.1` | Creates `v3.2.0-beta.1` |
| Force major | `feature/small-change` | Bump: `major` | 3.1.0 → 4.0.0 |
| Prerelease | `any-branch` | Type: `prerelease` | Marked as prerelease |

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

### Example 1: Shortcut Feature (Auto MINOR)

```bash
# Shortcut creates this branch automatically
git checkout -b feature/sc-8322--auto-tagging-through-github-actions

# Develop your feature
git commit -m "feat: implement automated tagging system"

# Push the branch
git push origin feature/sc-8322--auto-tagging-through-github-actions
# ✅ Result: 3.1.0 → 3.2.0 (MINOR bump)
```

### Example 2: Critical Bug Fix (Auto PATCH)

```bash
# Create hotfix branch
git checkout -b hotfix/critical-security-fix

# Fix the issue
git commit -m "fix: resolve security vulnerability"

# Push the branch
git push origin hotfix/critical-security-fix
# ✅ Result: 3.1.0 → 3.1.1 (PATCH bump)
```

### Example 3: Breaking API Change (Auto MAJOR)

```bash
# Create breaking change branch
git checkout -b breaking/api-v2

# Implement breaking changes
git commit -m "feat!: redesign API for v2"

# Push the branch
git push origin breaking/api-v2
# ✅ Result: 3.1.0 → 4.0.0 (MAJOR bump)
```

### Example 4: Manual Beta Release

```bash
# Create any branch
git checkout -b feature/experimental-feature

# Go to GitHub Actions → Run workflow
# Override: Version = "3.2.0-beta.1", Type = "prerelease"
# ✅ Result: Creates v3.2.0-beta.1 prerelease
```

### Example 5: Documentation Update (Auto PATCH)

```bash
# Create docs branch
git checkout -b docs/update-api-documentation

# Update documentation
git commit -m "docs: update API documentation"

# Push the branch
git push origin docs/update-api-documentation
# ✅ Result: 3.1.0 → 3.1.1 (PATCH bump)
```