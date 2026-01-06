# Servidor WhatsApp - Óticas Marco Polo
## Guia de Instalação e Uso

### 📋 Requisitos
- Node.js 18 ou superior
- NPM ou Yarn
- Google Chrome/Chromium (será instalado automaticamente pelo puppeteer)

---

## 🚀 Instalação

### 1. Navegar para a pasta do servidor
```bash
cd whatsapp-server
```

### 2. Instalar dependências
```bash
npm install
```

### 3. Configurar variáveis de ambiente
Edite o arquivo `.env` com suas configurações:

```env
# Porta do servidor
PORT=3001

# Configurações MySQL (para funcionalidades automáticas)
DB_HOST=localhost
DB_USER=root
DB_PASS=sua_senha
DB_NAME=otica_marco_polo

# ID do cliente WhatsApp
CLIENT_ID=otica-marco-polo-whatsapp
```

### 4. Iniciar o servidor
```bash
# Modo produção
npm start

# Modo desenvolvimento (com auto-reload)
npm run dev
```

---

## 📱 Conectando o WhatsApp

1. Acesse a interface web: **http://localhost:3001**
2. Abra o WhatsApp no celular
3. Vá em **Configurações > Aparelhos Conectados > Conectar Aparelho**
4. Escaneie o QR Code exibido na tela
5. Aguarde a mensagem "Conectado"

---

## 🔌 Endpoints da API

### Status da Conexão
```
GET /api/status
```
Retorna:
```json
{
  "success": true,
  "status": "ready",
  "isReady": true,
  "phoneNumber": "5531999999999"
}
```

### Obter QR Code
```
GET /api/qrcode
```

### Enviar Mensagem Individual
```
POST /api/send-message
Content-Type: application/json

{
  "phone": "31999999999",
  "message": "Olá! Esta é uma mensagem de teste."
}
```

### Verificar se Número tem WhatsApp
```
POST /api/check-number
Content-Type: application/json

{
  "phone": "31999999999"
}
```

### Enviar Mensagens em Lote
```
POST /api/send-bulk
Content-Type: application/json

{
  "messages": [
    {"phone": "31999999999", "message": "Mensagem 1"},
    {"phone": "31888888888", "message": "Mensagem 2"}
  ],
  "delay": 3000
}
```

### Enviar Mensagens de Aniversário
```
POST /api/send-birthdays
```

### Notificar OS Pronta
```
POST /api/send-os-ready
Content-Type: application/json

{
  "phone": "31999999999",
  "clientName": "João Silva",
  "osNumber": "12345"
}
```

### Lembrete de Pagamento
```
POST /api/send-payment-reminder
Content-Type: application/json

{
  "phone": "31999999999",
  "clientName": "João Silva",
  "value": 150.00,
  "dueDate": "15/12/2025",
  "description": "Óculos de grau"
}
```

### Desconectar
```
POST /api/logout
```

### Reiniciar Conexão
```
POST /api/restart
```

---

## 🐘 Uso no PHP

```php
<?php
require_once 'app/services/WhatsAppService.php';

// Criar instância
$whatsapp = new WhatsAppService();

// Verificar status
$status = $whatsapp->getStatus();
if ($status['isReady']) {
    
    // Enviar mensagem simples
    $resultado = $whatsapp->enviarMensagem('31999999999', 'Olá! Tudo bem?');
    
    if ($resultado['success']) {
        echo "Mensagem enviada!";
    }
    
    // Enviar notificação de OS pronta
    $whatsapp->enviarOSPronta('31999999999', 'João Silva', '12345');
    
    // Enviar lembrete de pagamento
    $whatsapp->enviarLembretePagamento(
        '31999999999', 
        'João Silva', 
        150.00, 
        '15/12/2025', 
        'Óculos de grau'
    );
    
    // Enviar aniversariantes do dia
    $whatsapp->enviarAniversariantes();
}
```

---

## ⏰ Configurar Envio Automático de Aniversários

### Linux (crontab)
```bash
# Editar crontab
crontab -e

# Adicionar linha (executa às 9h todos os dias)
0 9 * * * curl -X POST http://localhost:3001/api/send-birthdays
```

### Windows (Agendador de Tarefas)
1. Abra o Agendador de Tarefas
2. Crie uma nova tarefa básica
3. Configure para executar diariamente às 9h
4. Ação: Iniciar um programa
5. Programa: `curl`
6. Argumentos: `-X POST http://localhost:3001/api/send-birthdays`

---

## 🔧 Executar como Serviço (Windows)

### Usando PM2
```bash
# Instalar PM2 globalmente
npm install -g pm2

# Iniciar servidor
pm2 start server.js --name whatsapp-otica

# Configurar para iniciar com o Windows
pm2 startup
pm2 save
```

### Usando node-windows
```bash
npm install -g node-windows

# Criar serviço
node install-service.js
```

---

## 🐧 Executar como Serviço (Linux)

### Usando PM2
```bash
# Instalar PM2
npm install -g pm2

# Iniciar
pm2 start server.js --name whatsapp-otica

# Configurar autostart
pm2 startup
pm2 save
```

### Usando Systemd
Criar arquivo `/etc/systemd/system/whatsapp-otica.service`:

```ini
[Unit]
Description=WhatsApp Server Otica Marco Polo
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/otica/whatsapp-server
ExecStart=/usr/bin/node server.js
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
# Ativar serviço
sudo systemctl enable whatsapp-otica
sudo systemctl start whatsapp-otica
```

---

## ⚠️ Solução de Problemas

### QR Code não aparece
1. Verifique se o Node.js está instalado corretamente
2. Delete a pasta `.wwebjs_auth` e reinicie o servidor
3. Verifique se há firewall bloqueando

### Mensagens não são enviadas
1. Confirme que o WhatsApp está conectado (status: ready)
2. Verifique se o número possui WhatsApp
3. Verifique os logs em `/logs/`

### Conexão cai frequentemente
1. Mantenha o celular conectado à internet
2. Não desinstale o WhatsApp do celular
3. Não escaneie o QR Code em outro computador

### Erro de Puppeteer/Chromium
```bash
# Instalar dependências do Chromium (Linux)
sudo apt-get install -y \
  gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 \
  libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 \
  libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 \
  libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 \
  libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 \
  libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation \
  libappindicator1 libnss3 lsb-release xdg-utils wget
```

---

## 📞 Suporte

Em caso de problemas, verifique:
1. Os logs do servidor em `whatsapp-server/logs/`
2. O console do Node.js
3. A conexão com o banco de dados

---

**Versão:** 2.0.0  
**Desenvolvido para:** Óticas Marco Polo - Simonésia/MG
