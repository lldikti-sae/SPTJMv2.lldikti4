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
            $changed = false;
            
            // Replaces for 'ganjil_tl' -> 'ganjil'
            if (strpos($content, 'ganjil_tl') !== false) {
                $content = str_replace('ganjil_tl', 'ganjil', $content);
                $changed = true;
            }
            
            // Replaces for 'Ganjil TL' -> 'Ganjil'
            if (strpos($content, 'Ganjil TL') !== false) {
                $content = str_replace('Ganjil TL', 'Ganjil', $content);
                $changed = true;
            }

            // Replaces for 'ganjil tl' -> 'ganjil'
            if (strpos($content, 'ganjil tl') !== false) {
                $content = str_replace('ganjil tl', 'ganjil', $content);
                $changed = true;
            }
            
            // Replaces for 'ganjilTL' -> 'ganjil'
            if (strpos($content, 'ganjilTL') !== false) {
                $content = str_replace('ganjilTL', 'ganjil', $content);
                $changed = true;
            }
            
            // Replaces for 'GanjilTL' -> 'Ganjil'
            if (strpos($content, 'GanjilTL') !== false) {
                $content = str_replace('GanjilTL', 'Ganjil', $content);
                $changed = true;
            }

            if ($changed) {
                // Don't modify replace.php or replace2.php
                if (strpos($path, 'replace.php') !== false || strpos($path, 'replace2.php') !== false) continue;
                file_put_contents($path, $content);
                echo "Updated: $path\n";
            }
        }
    }
}
echo "Done.\n";
