$cssDir = "C:\Users\raulm\Downloads\hris-frontend\src\pages"

$replacements = @{
    '(?i)#0F9F8F' = 'var(--color-primary)'
    '(?i)#08776B' = 'var(--color-primary-dark)'
    '(?i)#DDF5F1' = 'var(--color-primary-light)'
    '(?i)#B5EDE7' = 'var(--color-primary-light)'
    '(?i)#075E54' = 'var(--color-primary-dark)'
    
    '(?i)#10b981' = 'var(--color-success)'
    '(?i)#059669' = 'var(--color-success)'
    '(?i)#d1fae5' = 'var(--color-success-light)'
    '(?i)#a7f3d0' = 'var(--color-success-light)'

    '(?i)#ef4444' = 'var(--color-error)'
    '(?i)#dc2626' = 'var(--color-error)'
    '(?i)#b91c1c' = 'var(--color-error)'
    '(?i)#fee2e2' = 'var(--color-error-light)'
    '(?i)#fecaca' = 'var(--color-error-light)'

    '(?i)#f59e0b' = 'var(--color-warning)'
    '(?i)#d97706' = 'var(--color-warning)'
    '(?i)#fef3c7' = 'var(--color-warning-light)'
    '(?i)#fde68a' = 'var(--color-warning-light)'

    '(?i)#1e293b' = 'var(--color-text-primary)'
    '(?i)#0f172a' = 'var(--color-text-primary)'
    '(?i)#111827' = 'var(--color-text-primary)'
    
    '(?i)#64748b' = 'var(--color-text-secondary)'
    '(?i)#475569' = 'var(--color-text-secondary)'
    '(?i)#94a3b8' = 'var(--color-text-tertiary)'
    '(?i)#cbd5e1' = 'var(--color-text-tertiary)'

    '(?i)#ffffff' = 'var(--color-white)'
    '(?i)#fff\b' = 'var(--color-white)'
    '(?i)\bwhite\b' = 'var(--color-white)'

    '(?i)#e2e8f0' = 'var(--color-border)'
    '(?i)#f1f5f9' = 'var(--color-surface-soft)'
    '(?i)#f8fafc' = 'var(--color-surface-soft)'
    '(?i)#fcfdfe' = 'var(--color-surface-soft)'
    '(?i)#f8fbff' = 'var(--color-surface-soft)'
    '(?i)#fef2f2' = 'var(--color-error-light)'
    
    '(?i)font-family:\s*''Poppins'',\s*sans-serif;' = 'font-family: var(--font-family-heading);'
}

$files = Get-ChildItem -Path $cssDir -Recurse -Filter *.css

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false

    foreach ($key in $replacements.Keys) {
        if ($content -match $key) {
            $content = $content -replace $key, $replacements[$key]
            $modified = $true
        }
    }

    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8
        Write-Host "Updated: $($file.Name)"
    }
}
Write-Host "Bulk update complete!"
