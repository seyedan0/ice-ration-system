<?php

/**
 * One-off script: fix over-wrapped __() calls that are already inside {{ }}.
 *
 * The previous script wrapped standalone __('site....') occurrences, but it
 * also wrongly wrapped calls that were already inside a Blade echo expression
 * like:
 *   {{ $x ? {{ __('site.a') }} : {{ __('site.b') }} }}
 *   {{ $y ?? {{ __('site.c') }} }}
 *   <span x-text="a ? {{ __('site.d') }} : {{ __('site.e') }}"></span>
 *   :title="$z ? {{ __('site.f') }} : {{ __('site.g') }}"
 *
 * This script unwraps the inner {{ }} so the call is used directly inside the
 * outer expression / attribute.
 *
 * Run from the project root: php fix_translations2.php
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

// Pattern: {{ __('site.xxx') }}  →  __('site.xxx')
$innerPattern = '/\{\{ (__\([\'"]site\.[a-zA-Z0-9_]+[\'"]\)) \}\}/';

foreach ($files as $path) {
    $original = file_get_contents($path);
    $new = preg_replace_callback($innerPattern, function ($m) use ($path, &$totalChanges) {
        $totalChanges++;
        return $m[1]; // keep only the __() call
    }, $original, -1);

    if ($new !== $original) {
        file_put_contents($path, $new);
        $changedFiles++;
        echo "Fixed (unwrap inner): $path\n";
    }
}

echo "\nDone. Unwrapped $totalChanges inner occurrences across $changedFiles files.\n";
