<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$iterator = new RecursiveIteratorIterator($dir);
$regexIterator = new RegexIterator($iterator, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($regexIterator as $file) {
    $filePath = $file[0];
    $content = file_get_contents($filePath);
    $original = $content;

    // Remove `lengthMenu: "string"` or `'string'` inside the language options
    $content = preg_replace('/lengthMenu:\s*[\'"](Show _MENU_ entries|Tampilkan _MENU_ entri|Tampilkan _MENU_ data)[\'"],?\s*/i', '', $content);

    // Remove custom `dom: "..." + "..."` or `dom: '...'` overrides to fall back to global
    // Pattern matches `dom: '...'` or `dom: "..." + "..."` and removes the entire line or multiline assignment
    $content = preg_replace('/dom:\s*[\'"][^\'"]*[\'"](?:\s*\+\s*[\'"][^\'"]*[\'"])*,?/i', '', $content);
    
    // Also cleanup trailing commas or double empty lines if needed
    // But DataTables objects are forgiving about trailing commas.
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        echo "Updated: $filePath\n";
    }
}

echo "Done.\n";
