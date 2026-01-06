<?php
require_once 'config/config_minimal.php';

if (!is_logged_in()) {
    redirect('login_simple.php');
}

$db = get_db();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SYSTEM_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .logout-btn { background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .welcome-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .welcome-card h2 { color: #333; margin-bottom: 10px; }
        .welcome-card p { color: #666; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card .icon { font-size: 40px; margin-bottom: 10px; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #667eea; margin-bottom: 5px; }
        .stat-card .label { color: #666; font-size: 14px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🦷 <?= SYSTEM_NAME ?></h1>
        <div class="user-info">
            <span>Olá, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="logout_simple.php" class="logout-btn">Sair</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-card">
            <div class="success">
                ✅ <strong>Sistema configurado com sucesso!</strong> Seu RevexaDental está funcionando perfeitamente.
            </div>
            <h2>Bem-vindo ao Sistema</h2>
            <p>Esta é uma versão simplificada e funcional do RevexaDental. O sistema está pronto para uso!</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👥</div>
                <div class="value"><?= $db->query("SELECT COUNT(*) as c FROM usuarios")->fetch()['c'] ?></div>
                <div class="label">Usuários Cadastrados</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">✅</div>
                <div class="value">Sistema Ativo</div>
                <div class="label">Status</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">🔧</div>
                <div class="value">Configurado</div>
                <div class="label">Estado</div>
            </div>
        </div>
        
        <div class="welcome-card" style="margin-top: 30px;">
            <h2>Próximos Passos</h2>
            <ul style="margin-left: 20px; color: #666; line-height: 2;">
                <li>Configure os módulos de pacientes, agendamentos e prontuários</li>
                <li>Adicione mais usuários através do painel administrativo</li>
                <li>Personalize as configurações do sistema</li>
            </ul>
        </div>
    </div>
</body>
</html>
