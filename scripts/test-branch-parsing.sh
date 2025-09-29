#!/bin/bash

# Test script for automatic version bump logic
# This simulates the logic from the GitHub Actions workflow

test_version_bump() {
    local BRANCH_NAME="$1"
    local EXPECTED_BUMP="$2"
    local CURRENT_VERSION="3.1.0"
    
    echo "Testing branch: $BRANCH_NAME"
    
    BUMP_TYPE=""
    
    # PATCH: Bug fixes and patches
    if [[ $BRANCH_NAME =~ ^(bug|fix|hotfix|patch|bugfix)/ ]]; then
        BUMP_TYPE="patch"
        echo "  🐛 Detected PATCH bump from branch prefix: ${BASH_REMATCH[1]}"
    
    # MINOR: Features and enhancements  
    elif [[ $BRANCH_NAME =~ ^(feature|feat|enhancement|improve|add|update)/ ]]; then
        BUMP_TYPE="minor"
        echo "  ✨ Detected MINOR bump from branch prefix: ${BASH_REMATCH[1]}"
    
    # MAJOR: Breaking changes
    elif [[ $BRANCH_NAME =~ ^(breaking|major|break|bc-break|breaking-change)/ ]]; then
        BUMP_TYPE="major"
        echo "  💥 Detected MAJOR bump from branch prefix: ${BASH_REMATCH[1]}"
    
    # PATCH: Chores and maintenance (default to patch)
    elif [[ $BRANCH_NAME =~ ^(chore|docs|style|refactor|test)/ ]]; then
        BUMP_TYPE="patch"
        echo "  🔧 Detected PATCH bump from maintenance branch: ${BASH_REMATCH[1]}"
    
    # MINOR: Release branches (default to minor)
    elif [[ $BRANCH_NAME =~ ^release/ ]]; then
        BUMP_TYPE="minor"
        echo "  🚀 Detected MINOR bump from release branch"
    
    # PATCH: Main branch (default to patch)
    elif [[ $BRANCH_NAME == "main" ]]; then
        BUMP_TYPE="patch"
        echo "  🏠 Detected PATCH bump from main branch"
    
    # Try to infer from Shortcut branch pattern: feature/sc-XXXX--description
    elif [[ $BRANCH_NAME =~ ^feature/sc-[0-9]+-- ]]; then
        BUMP_TYPE="minor"
        echo "  🎫 Detected MINOR bump from Shortcut feature branch"
    
    # Default to patch for unknown patterns
    else
        BUMP_TYPE="patch"
        echo "  ❓ Unknown branch pattern, defaulting to PATCH bump"
    fi
    
    # Calculate new version
    if [[ $CURRENT_VERSION =~ ^([0-9]+)\.([0-9]+)\.([0-9]+)$ ]]; then
        MAJOR=${BASH_REMATCH[1]}
        MINOR=${BASH_REMATCH[2]}
        PATCH=${BASH_REMATCH[3]}
        
        case $BUMP_TYPE in
            "major")
                MAJOR=$((MAJOR + 1))
                MINOR=0
                PATCH=0
                NEW_VERSION="$MAJOR.$MINOR.$PATCH"
                ;;
            "minor")
                MINOR=$((MINOR + 1))
                PATCH=0
                NEW_VERSION="$MAJOR.$MINOR.$PATCH"
                ;;
            "patch")
                PATCH=$((PATCH + 1))
                NEW_VERSION="$MAJOR.$MINOR.$PATCH"
                ;;
        esac
    fi
    
    echo "  📦 Version: $CURRENT_VERSION → $NEW_VERSION"
    
    # Check result
    if [[ "$BUMP_TYPE" == "$EXPECTED_BUMP" ]]; then
        echo "  ✅ PASS: Expected '$EXPECTED_BUMP', got '$BUMP_TYPE'"
    else
        echo "  ❌ FAIL: Expected '$EXPECTED_BUMP', got '$BUMP_TYPE'"
    fi
    echo ""
}

echo "🧪 Testing Automatic Version Bump Logic"
echo "========================================"
echo ""

# Test cases for MINOR bumps (features)
echo "🟢 MINOR Bump Tests (Features & Enhancements):"
test_version_bump "feature/new-api" "minor"
test_version_bump "feature/sc-8322--auto-tagging" "minor"
test_version_bump "feat/user-authentication" "minor"
test_version_bump "enhancement/better-performance" "minor"
test_version_bump "improve/query-builder" "minor"
test_version_bump "add/new-criteria" "minor"
test_version_bump "update/dependencies" "minor"
test_version_bump "release/v3.2.0" "minor"

echo "🟡 PATCH Bump Tests (Bug Fixes & Maintenance):"
test_version_bump "bug/query-builder-fix" "patch"
test_version_bump "fix/memory-leak" "patch"
test_version_bump "hotfix/security-patch" "patch"
test_version_bump "patch/minor-update" "patch"
test_version_bump "bugfix/validation-error" "patch"
test_version_bump "chore/update-deps" "patch"
test_version_bump "docs/api-documentation" "patch"
test_version_bump "style/code-formatting" "patch"
test_version_bump "refactor/clean-code" "patch"
test_version_bump "test/add-unit-tests" "patch"
test_version_bump "main" "patch"

echo "🔴 MAJOR Bump Tests (Breaking Changes):"
test_version_bump "breaking/api-redesign" "major"
test_version_bump "major/version-2" "major"
test_version_bump "break/remove-deprecated" "major"
test_version_bump "bc-break/new-structure" "major"
test_version_bump "breaking-change/api-v2" "major"

echo "❓ Unknown Pattern Tests (Default to PATCH):"
test_version_bump "unknown/some-branch" "patch"
test_version_bump "custom-prefix/test" "patch"
test_version_bump "develop" "patch"

echo "🏁 Test completed!"
echo ""
echo "📊 Summary:"
echo "- MINOR: Features, enhancements, additions, releases"
echo "- PATCH: Bug fixes, maintenance, documentation, unknown"
echo "- MAJOR: Breaking changes, major versions"