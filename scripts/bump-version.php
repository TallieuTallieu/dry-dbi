<?php

/**
 * Branch Creation Helper for dry-dbi Automatic Releases
 * 
 * This script helps create appropriately named branches that trigger
 * automatic version bumps based on the branch prefix.
 * 
 * Usage:
 *   php scripts/bump-version.php <branch-type> [description]
 */

if ($argc < 2) {
    echo "🚀 Branch Creation Helper for Automatic Releases\n";
    echo "===============================================\n\n";
    echo "Usage: php scripts/bump-version.php <branch-type> [description]\n\n";
    echo "Branch Types & Auto Version Bumps:\n";
    echo "  feature     → MINOR bump (3.1.0 → 3.2.0)\n";
    echo "  bug         → PATCH bump (3.1.0 → 3.1.1)\n";
    echo "  hotfix      → PATCH bump (3.1.0 → 3.1.1)\n";
    echo "  breaking    → MAJOR bump (3.1.0 → 4.0.0)\n";
    echo "  chore       → PATCH bump (3.1.0 → 3.1.1)\n";
    echo "  docs        → PATCH bump (3.1.0 → 3.1.1)\n\n";
    echo "Examples:\n";
    echo "  php scripts/bump-version.php feature new-api\n";
    echo "  php scripts/bump-version.php bug query-builder-fix\n";
    echo "  php scripts/bump-version.php breaking api-redesign\n";
    echo "  php scripts/bump-version.php hotfix security-patch\n\n";
    echo "💡 Tip: Just push the created branch to trigger automatic release!\n";
    exit(1);
}

$composerFile = __DIR__ . '/../composer.json';
$branchType = $argv[1];
$description = isset($argv[2]) ? $argv[2] : null;

// Validate branch type
$validBranchTypes = [
    'feature' => ['bump' => 'MINOR', 'example' => '3.1.0 → 3.2.0'],
    'feat' => ['bump' => 'MINOR', 'example' => '3.1.0 → 3.2.0'],
    'bug' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'fix' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'hotfix' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'patch' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'bugfix' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'breaking' => ['bump' => 'MAJOR', 'example' => '3.1.0 → 4.0.0'],
    'major' => ['bump' => 'MAJOR', 'example' => '3.1.0 → 4.0.0'],
    'break' => ['bump' => 'MAJOR', 'example' => '3.1.0 → 4.0.0'],
    'bc-break' => ['bump' => 'MAJOR', 'example' => '3.1.0 → 4.0.0'],
    'breaking-change' => ['bump' => 'MAJOR', 'example' => '3.1.0 → 4.0.0'],
    'enhancement' => ['bump' => 'MINOR', 'example' => '3.1.0 → 3.2.0'],
    'improve' => ['bump' => 'MINOR', 'example' => '3.1.0 → 3.2.0'],
    'add' => ['bump' => 'MINOR', 'example' => '3.1.0 → 3.2.0'],
    'update' => ['bump' => 'MINOR', 'example' => '3.1.0 → 3.2.0'],
    'chore' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'docs' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'style' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'refactor' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'test' => ['bump' => 'PATCH', 'example' => '3.1.0 → 3.1.1'],
    'release' => ['bump' => 'MINOR', 'example' => '3.1.0 → 3.2.0'],
];

if (!isset($validBranchTypes[$branchType])) {
    echo "❌ Error: Invalid branch type '$branchType'\n\n";
    echo "Valid branch types:\n";
    foreach ($validBranchTypes as $type => $info) {
        echo "  $type → {$info['bump']} ({$info['example']})\n";
    }
    exit(1);
}

$bumpInfo = $validBranchTypes[$branchType];

// Get current version
if (!file_exists($composerFile)) {
    echo "Error: composer.json not found\n";
    exit(1);
}

$composer = json_decode(file_get_contents($composerFile), true);

if (!isset($composer['version'])) {
    echo "Error: No version field found in composer.json\n";
    exit(1);
}

$currentVersion = $composer['version'];

// Generate branch name
$timestamp = date('Ymd-His');
if ($description) {
    $branchName = "$branchType/$description";
} else {
    $branchName = "$branchType/auto-$timestamp";
}

// Sanitize branch name
$branchName = preg_replace('/[^a-zA-Z0-9\-_\/]/', '-', $branchName);
$branchName = preg_replace('/-+/', '-', $branchName);
$branchName = trim($branchName, '-');

echo "🚀 Automatic Release Branch Creator\n";
echo "===================================\n\n";
echo "📦 Current version: $currentVersion\n";
echo "🌿 Branch type: $branchType\n";
echo "📈 Auto bump: {$bumpInfo['bump']} ({$bumpInfo['example']})\n";
echo "🏷️  Branch name: $branchName\n\n";

echo "This will create a branch that automatically triggers a {$bumpInfo['bump']} release.\n";
echo "Continue? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo "Aborted.\n";
    exit(0);
}

echo "\n✅ Creating branch for automatic release...\n\n";
echo "Commands to run:\n";
echo "1. git checkout -b $branchName\n";
echo "2. # Make your changes\n";
echo "3. git commit -m \"your commit message\"\n";
echo "4. git push origin $branchName\n";
echo "5. 🎉 Automatic release will be created!\n\n";

echo "Run these commands now? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) === 'y') {
    echo "\n🔄 Creating branch...\n";
    system("git checkout -b $branchName");
    echo "\n✅ Branch '$branchName' created!\n";
    echo "💡 Make your changes, commit, and push to trigger automatic release.\n";
} else {
    echo "\n💡 Run this command when ready:\n";
    echo "git checkout -b $branchName\n";
}