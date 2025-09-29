#!/bin/bash

# Test script for branch name parsing logic
# This simulates the logic from the GitHub Actions workflow

test_branch_parsing() {
    local BRANCH_NAME="$1"
    local EXPECTED="$2"
    
    echo "Testing branch: $BRANCH_NAME"
    
    VERSION=""
    
    # Pattern 1: release/v3.2.0 or release/3.2.0
    if [[ $BRANCH_NAME =~ ^release/v?([0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9\.-]+)?(\+[a-zA-Z0-9\.-]+)?)$ ]]; then
        VERSION="${BASH_REMATCH[1]}"
        echo "  ✓ Matched release pattern: $VERSION"
    
    # Pattern 2: hotfix/v3.1.1 or hotfix/3.1.1  
    elif [[ $BRANCH_NAME =~ ^hotfix/v?([0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9\.-]+)?(\+[a-zA-Z0-9\.-]+)?)$ ]]; then
        VERSION="${BASH_REMATCH[1]}"
        echo "  ✓ Matched hotfix pattern: $VERSION"
    
    # Pattern 3: version/3.2.0 or v3.2.0
    elif [[ $BRANCH_NAME =~ ^(version/|v)([0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9\.-]+)?(\+[a-zA-Z0-9\.-]+)?)$ ]]; then
        VERSION="${BASH_REMATCH[2]}"
        echo "  ✓ Matched version pattern: $VERSION"
    
    # Pattern 4: feature/v3.2.0-feature-name
    elif [[ $BRANCH_NAME =~ ^feature/v?([0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9\.-]+)?(\+[a-zA-Z0-9\.-]+)?) ]]; then
        VERSION="${BASH_REMATCH[1]}"
        echo "  ✓ Matched feature pattern: $VERSION"
    
    # Pattern 5: main branch
    elif [[ $BRANCH_NAME == "main" ]]; then
        VERSION="from-composer.json"
        echo "  ✓ Matched main branch: will use composer.json"
    
    else
        echo "  ✗ No pattern matched"
        VERSION="NO_MATCH"
    fi
    
    # Check result
    if [[ "$VERSION" == "$EXPECTED" ]]; then
        echo "  ✅ PASS: Expected '$EXPECTED', got '$VERSION'"
    else
        echo "  ❌ FAIL: Expected '$EXPECTED', got '$VERSION'"
    fi
    echo ""
}

echo "🧪 Testing Branch Name Parsing Logic"
echo "===================================="
echo ""

# Test cases
test_branch_parsing "release/v3.2.0" "3.2.0"
test_branch_parsing "release/3.2.0" "3.2.0"
test_branch_parsing "release/v4.0.0-beta.1" "4.0.0-beta.1"
test_branch_parsing "hotfix/v3.1.1" "3.1.1"
test_branch_parsing "hotfix/3.1.1" "3.1.1"
test_branch_parsing "hotfix/v3.1.2-urgent" "3.1.2-urgent"
test_branch_parsing "version/3.2.0" "3.2.0"
test_branch_parsing "v3.2.0" "3.2.0"
test_branch_parsing "v4.0.0-alpha.1" "4.0.0-alpha.1"
test_branch_parsing "feature/v3.2.0-new-api" "3.2.0-new-api"
test_branch_parsing "feature/v3.2.0-fix-bug" "3.2.0-fix-bug"
test_branch_parsing "feature/3.2.0-no-v-prefix" "3.2.0-no-v-prefix"
test_branch_parsing "main" "from-composer.json"
test_branch_parsing "feature/sc-8322--dry-dbi-auto-tagging-through-github-actions" "NO_MATCH"
test_branch_parsing "develop" "NO_MATCH"
test_branch_parsing "bugfix/some-fix" "NO_MATCH"

echo "🏁 Test completed!"