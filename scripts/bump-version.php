<?php

/**
 * Version Bump Script for dry-dbi
 * 
 * Usage:
 *   php scripts/bump-version.php patch
 *   php scripts/bump-version.php minor
 *   php scripts/bump-version.php major
 *   php scripts/bump-version.php 3.2.1
 */

if ($argc < 2) {
    echo "Usage: php scripts/bump-version.php <patch|minor|major|version>\n";
    echo "Examples:\n";
    echo "  php scripts/bump-version.php patch     # 3.1.0 -> 3.1.1\n";
    echo "  php scripts/bump-version.php minor     # 3.1.0 -> 3.2.0\n";
    echo "  php scripts/bump-version.php major     # 3.1.0 -> 4.0.0\n";
    echo "  php scripts/bump-version.php 3.2.1     # Set specific version\n";
    exit(1);
}

$composerFile = __DIR__ . '/../composer.json';
$type = $argv[1];

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
echo "Current version: $currentVersion\n";

// Parse current version
if (!preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-(.+))?$/', $currentVersion, $matches)) {
    echo "Error: Invalid current version format\n";
    exit(1);
}

$major = (int)$matches[1];
$minor = (int)$matches[2];
$patch = (int)$matches[3];
$prerelease = $matches[4] ?? null;

// Calculate new version
switch ($type) {
    case 'patch':
        $patch++;
        $prerelease = null;
        break;
    case 'minor':
        $minor++;
        $patch = 0;
        $prerelease = null;
        break;
    case 'major':
        $major++;
        $minor = 0;
        $patch = 0;
        $prerelease = null;
        break;
    default:
        // Assume it's a specific version
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-(.+))?$/', $type, $versionMatches)) {
            echo "Error: Invalid version format. Use semantic versioning (e.g., 3.2.1)\n";
            exit(1);
        }
        $major = (int)$versionMatches[1];
        $minor = (int)$versionMatches[2];
        $patch = (int)$versionMatches[3];
        $prerelease = $versionMatches[4] ?? null;
        break;
}

$newVersion = "$major.$minor.$patch";
if ($prerelease) {
    $newVersion .= "-$prerelease";
}

echo "New version: $newVersion\n";

// Confirm the change
echo "Update version from $currentVersion to $newVersion? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo "Aborted.\n";
    exit(0);
}

// Update composer.json
$composer['version'] = $newVersion;
$json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

if (file_put_contents($composerFile, $json) === false) {
    echo "Error: Failed to write composer.json\n";
    exit(1);
}

echo "✅ Version updated successfully!\n";
echo "\nNext steps:\n";
echo "1. Review the changes: git diff composer.json\n";
echo "2. Commit the version bump: git add composer.json && git commit -m \"chore: bump version to $newVersion\"\n";
echo "3. Push to main branch: git push origin main\n";
echo "4. The GitHub Actions workflow will automatically create a release\n";