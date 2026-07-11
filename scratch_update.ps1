$files = Get-ChildItem -Path 'c:\laragon\www\LLDIKTI\SPTJMv2.lldikti4\resources\views\' -Filter '*.blade.php' -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Replace <h4> to <h1> inside page-titles
    $newContent = [regex]::Replace($content, '(<div class="page-titles">\s*)<h4>(.*?)</h4>', '$1<h1>$2</h1>')
    
    # Remove any local styles defining .page-titles h4
    $newContent = [regex]::Replace($newContent, '(?s)\.[a-zA-Z0-9_-]+page-header\s+\.page-titles\s+h4\s*\{.*?\}', '')
    
    if ($content -cne $newContent) {
        Set-Content -Path $file.FullName -Value $newContent -NoNewline -Encoding UTF8
        Write-Output "Updated: $($file.FullName)"
    }
}
