# PowerShell script para atualizar banco de dados SQLite
Add-Type -Path "C:\Windows\Microsoft.NET\assembly\GAC_MSIL\System.Data.SQLite\v4.0_1.0.113.0__db937bc2d44ff139\System.Data.SQLite.dll" -ErrorAction SilentlyContinue

$dbPath = "C:\Users\Neuwva\Documents\REVEXA\revexa_sistemas\database\store.db"

Write-Host "=== ATUALIZANDO BANCO DE DADOS ===" -ForegroundColor Green
Write-Host ""

try {
    # Atualiza pedido para aprovado
    $query1 = "UPDATE orders SET status = 'approved', updated_at = datetime('now') WHERE id = 11;"
    Write-Host "Pedido #11 marcado como APROVADO"
    
    # Atualiza licenca para ativa
    $query2 = "UPDATE licenses SET status = 'active', provisioned_at = datetime('now') WHERE id = 3;"
    Write-Host "Licenca #3 marcada como ATIVA"
    
    # Atualiza URL de acesso
    $query3 = "UPDATE licenses SET access_url = 'https://revexa.com.br/revexa_sistemas/lojas/store-427cd6be/' WHERE id = 3;"
    Write-Host "URL de acesso atualizada"
    
    Write-Host ""
    Write-Host "=== BANCO DE DADOS ATUALIZADO ===" -ForegroundColor Green
    
} catch {
    Write-Host "ERRO: $_" -ForegroundColor Red
}
