<?php

/**
 * One-off script: wrap bare __('site....') calls in Blade files with {{ }}.
 *
 * Rules:
 *  - Skips lines that already wrap the call in {{ }} or {{{ }}}.
 *  - Skips occurrences inside x-text="..." / x-bind / :attr="..." (Alpine).
 *  - Skips occurrences inside @click / @submit etc. (Alpine events).
 *  - Skips occurrences inside <script> blocks.
 *  - Skips PHP-only lines (starting with @php or <?php).
 *  - Wraps standalone __('site....') occurrences found in plain HTML/text
 *    with {{ __('site....') }}.
 *
 * Run from the project root: php fix_translations.php
 */

$root = __DIR__ . '/resources/views';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$files = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

$totalChanges = 0;
$changedFiles = 0;

foreach ($files as $path) {
    $original = file_get_contents($path);
    $lines = explode("\n", $original);
    $inScript = false;
    $fileChanged = false;

    foreach ($lines as $i => $line) {
        // Track <script> blocks
        if (preg_match('/<script\b/i', $line)) {
            $inScript = true;
        }
        if (preg_match('/<\/script>/i', $line)) {
            $inScript = false;
        }
        if ($inScript) {
            continue;
        }

        // Skip blade @php / <?php directives
        if (preg_match('/^\s*@php\b/', $line) || preg_match('/^\s*<\?php/', $line)) {
            continue;
        }

        // Skip lines that are already fully inside {{ }} for the first occurrence
        // We only want to transform occurrences that are NOT preceded by {{ or {{{
        // and not inside Alpine attributes (x-*, :*, @*).

        // Find all __('site....') occurrences in this line
        $pattern = '/__\([\'"]site\.[a-zA-Z0-9_]+[\'"]\)/';

        if (!preg_match($pattern, $line)) {
            continue;
        }

        // Replace only occurrences that are not already inside {{ }} or {{{ }}}
        // We'll do a negative lookbehind for {{ or {{{, and also avoid Alpine attributes.
        // Simplest robust approach: find each match and check preceding 10 chars.

        $newLine = preg_replace_callback($pattern, function ($m) use ($line) {
            $match = $m[0];
            // Position of this match
            $pos = strpos($line, $match);
            if ($pos === false) {
                return $match;
            }
            // Look at preceding 10 characters
            $before = substr($line, max(0, $pos - 10), min(10, $pos));

            // Already wrapped in blade echo?
            if (preg_match('/\{\{\{?\s*$/', $before)) {
                return $match; // already inside {{ }} — leave as is
            }

            // Inside an Alpine attribute? (x-text="..." :attr="..." @click="...")
            // Look for a quote opened before this with x-/ : / @ preceding.
            if (preg_match('/(x-[a-z]+|:[a-z\-]+|@[a-z\-]+)\s*=\s*"[^"]*$/', $before)) {
                return $match; // inside Alpine — leave as is (Alpine uses JS __)
            }
            if (preg_match('/(x-[a-z]+|:[a-z\-]+|@[a-z\-]+)\s*=\s*\'[^\']*\'$/', $before)) {
                return $match;
            }

            // Otherwise wrap it
            return '{{ ' . $match . ' }}';
        }, $line);

        if ($newLine !== $line) {
            $lines[$i] = $newLine;
            $fileChanged = true;
            $totalChanges++;
        }
    }

    if ($fileChanged) {
        file_put_contents($path, implode("\n", $lines));
        $changedFiles++;
        echo "Fixed: $path\n";
    }
}

echo "\nDone. Changed $totalChanges occurrences across $changedFiles files.\n";
