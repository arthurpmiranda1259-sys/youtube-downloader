# Script de Verificacao Final
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "VERIFICACAO DA INSTANCIA REVEXA DENTAL" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$instancePath = "C:\Users\Neuwva\Documents\REVEXA\revexa_sistemas\lojas\store-427cd6be"

# Verifica se a pasta existe
if (Test-Path $instancePath) {
    Write-Host "[OK] Pasta da instancia criada" -ForegroundColor Green
    
    # Lista arquivos PHP
    $phpFiles = Get-ChildItem -Path $instancePath -Filter "*.php" | Select-Object -ExpandProperty Name
    Write-Host "[OK] Arquivos PHP encontrados:" -ForegroundColor Green
    foreach ($file in $phpFiles) {
        Write-Host "    - $file" -ForegroundColor Gray
    }
    
    # Verifica config
    $configPath = Join-Path $instancePath "config\config.php"
    if (Test-Path $configPath) {
        Write-Host "[OK] Arquivo de configuracao presente" -ForegroundColor Green
    } else {
        Write-Host "[ERRO] Arquivo de configuracao NAO encontrado" -ForegroundColor Red
    }
    
    # Verifica .htaccess
    $htaccessPath = Join-Path $instancePath ".htaccess"
    if (Test-Path $htaccessPath) {
        Write-Host "[OK] Arquivo .htaccess presente" -ForegroundColor Green
    } else {
        Write-Host "[AVISO] Arquivo .htaccess NAO encontrado" -ForegroundColor Yellow
    }
    
    # Verifica pastas
    $folders = @("assets", "includes", "config")
    foreach ($folder in $folders) {
        $folderPath = Join-Path $instancePath $folder
        if (Test-Path $folderPath) {
            Write-Host "[OK] Pasta /$folder presente" -ForegroundColor Green
        } else {
            Write-Host "[AVISO] Pasta /$folder NAO encontrada" -ForegroundColor Yellow
        }
    }
    
} else {
    Write-Host "[ERRO] Pasta da instancia NAO encontrada!" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "PROXIMOS PASSOS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Acesse a pagina de diagnostico:" -ForegroundColor White
Write-Host "   https://revexa.com.br/revexa_sistemas/lojas/store-427cd6be/diagnostico.php" -ForegroundColor Yellow
Write-Host ""
Write-Host "2. Se tudo estiver OK, faca login:" -ForegroundColor White
Write-Host "   https://revexa.com.br/revexa_sistemas/lojas/store-427cd6be/login.php" -ForegroundColor Yellow
Write-Host ""
Write-Host "3. Credenciais:" -ForegroundColor White
Write-Host "   Email: admin@admin.com" -ForegroundColor Yellow
Write-Host "   Senha: admin123" -ForegroundColor Yellow
Write-Host ""
Write-Host "4. Atualize o banco de dados executando:" -ForegroundColor White
Write-Host "   update_database.sql" -ForegroundColor Yellow
Write-Host ""
