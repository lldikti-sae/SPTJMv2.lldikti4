<?php

$file = 'resources/views/admin/kekurangan-bayar.blade.php';
$content = file_get_contents($file);

$tabBtn = '<li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-selesai-btn" data-bs-toggle="tab" data-bs-target="#tab-data-selesai" type="button" role="tab" aria-selected="false">
                        Data Selesai (Lunas)
                    </button>
                </li>
                ';
$content = str_replace('<li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-rekap-btn"', $tabBtn . '<li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-rekap-btn"', $content);

preg_match('/<div class="tab-pane fade" id="tab-data-lebih".*?@endif\s+<\/div>\s+<\/div>\s+<\/div>/s', $content, $matches);
if (empty($matches)) {
    die("Match not found\n");
}

$lebihHtml = $matches[0];
$selesaiHtml = str_replace(
    ['tab-data-lebih', 'Lebih Bayar', 'search_lebih', 'search_kurang', 'lebih_page', 'kurang_page', 'lebihRows', 'destroyLebih', 'formDestroyLebih'],
    ['tab-data-selesai', 'Selesai (Lunas)', 'search_selesai', 'search_lebih', 'selesai_page', 'lebih_page', 'selesaiRows', 'destroySelesai', 'formDestroySelesai'],
    $lebihHtml
);
// Remove the destroy button for Selesai
$selesaiHtml = preg_replace('/<form action="\{\{ route\(\'admin\.kekurangan-bayar\.destroySelesai\'\) \}\}".*?<\/form>/s', '', $selesaiHtml);

$content = str_replace($lebihHtml, $lebihHtml . "\n\n                {{-- TAB 3: DATA SELESAI (LUNAS) --}}\n                " . $selesaiHtml, $content);

file_put_contents($file, $content);
echo "OK\n";
