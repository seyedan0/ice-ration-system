<?php

/**
 * One-off script: properly fix translation call syntax across all Blade files.
 *
 * Handles two cases correctly:
 *  1) Bare __() calls in plain HTML/text  -> wrap with {{ }}
 *     Example: <p>__('site.x')</p>  ->  <p>{{ __('site.x') }}</p>
 *  2) __() calls already inside an expression within {{ }} or Alpine attribute
 *     -> leave as __() (no wrapping)
 *     Example: {{ $a ? __('site.x') : __('site.y') }}  (correct as-is)
 *
 * Strategy: walk through each line, find all __('site....') occurrences,
 * and decide wrap-or-not based on the surrounding context:
 *  - If preceded (within ~30 chars) by an unclosed "{{ " or "{{{ "  -> don't wrap
 *  - If inside an Alpine attribute (x-..=  / :..=  / @..= ) with an unclosed quote -> don't wrap
 *  - Otherwise -> wrap with {{ }}
 *
 * Run from project root: php fix_translations3.php
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
$pattern = '/__\([\'"]site\.[a-zA-Z0-9_]+[\'"]\)/';

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

        if (!preg_match($pattern, $line)) {
            continue;
        }

        $newLine = preg_replace_callback($pattern, function ($m) use ($line, &$totalChanges) {
            $match = $m[0];
            $pos = strpos($line, $match);
            if ($pos === false) {
                return $match;
            }
            $before = substr($line, 0, $pos);

            // Already wrapped in blade echo? Look for "{{ " or "{{{ " that hasn't been closed since
            // Check if there's an odd number of "}}" after the last "{{"
            $lastOpen = max(strrpos($before, '{{ '), strrpos($before, '{{{ '));
            $lastClose = strrpos($before, ' }}');
            if ($lastOpen !== false && ($lastClose === false || $lastOpen > $lastClose)) {
                // We are inside a {{ }} expression; do NOT wrap
                return $match;
            }

            // Inside an Alpine attribute? x-..=  / :..=  / @..=
            // Look for attribute pattern with an unclosed quote
            if (preg_match('/(x-[a-z]+|:[a-z\-]+|@[a-z\-]+)\s*=\s*("[^"]*|\'[^\']*|"[^"]*\([^"]*)$/', $before)
                || preg_match('/(x-[a-z]+|:[a-z\-]+|@[a-z\-]+)\s*=\s*([\'"])[^\'"]*\([^\'"]*\)??[^\'"]*$/', $before)
            ) {
                return $match; // inside Alpine — do NOT wrap
            }
            // Simpler alpine check: if the line has x-text= or similar with quote before
            if (preg_match('/(x-[a-z]+|:[a-z\-]+|@[a-z\-]+)\s*=\s*[\'"]/', $before)
                && !preg_match('/[\'"]\s*>/', $before)
            ) {
                return $match;
            }

            // Otherwise wrap it
            $totalChanges++;
            return '{{ ' . $match . ' }}';
        }, $line);

        if ($newLine !== $line) {
            $lines[$i] = $newLine;
            $fileChanged = true;
        }
    }

    if ($fileChanged) {
        file_put_contents($path, implode("\n", $lines));
        $changedFiles++;
        echo "Fixed: $path\n";
    }
}

echo "\nDone. Wrapped $totalChanges bare occurrences across $changedFiles files.\n";
