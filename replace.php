<?php
$directory = new RecursiveDirectoryIterator('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4');
$iterator = new RecursiveIteratorIterator($directory);
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $path = $file->getPathname();
        // Skip vendor, node_modules, .git, storage
        if (strpos($path, 'vendor') !== false || strpos($path, 'node_modules') !== false || strpos($path, '.git') !== false || strpos($path, 'storage') !== false) {
            continue;
        }
        if (in_array(pathinfo($path, PATHINFO_EXTENSION), ['php', 'blade.php'])) {
            $content = file_get_contents($path);
            if (strpos($content, 'p_sister_ganjil_tl') !== false) {
                // Don't modify replace.php itself
                if (strpos($path, 'replace.php') !== false) continue;
                $content = str_replace('p_sister_ganjil_tl', 'p_sister_ganjil', $content);
                file_put_contents($path, $content);
                echo "Updated: $path\n";
            }
        }
    }
}
echo "Done.\n";
