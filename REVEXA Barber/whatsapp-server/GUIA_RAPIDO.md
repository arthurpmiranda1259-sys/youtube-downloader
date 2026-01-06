# 🎯 Configuração Rápida - Servidor WhatsApp

## ✅ Arquivos já enviados via FTP!

Os arquivos estão em: `https://revexa.com.br/whatsapp-server/`

## 🔧 Próximos Passos (Fazer no Servidor)

### Opção 1: Via Painel de Controle (Mais Fácil)

Se seu servidor tem **Node.js App Manager** no painel:

1. Acesse o painel de controle
2. Procure por **Node.js** ou **Applications**
3. Crie nova aplicação:
   - **Caminho**: `/www/whatsapp-server`
   - **Script de entrada**: `server.js`
   - **Porta**: `3001`
4. Clique em **Instalar dependências** (npm install)
5. Inicie a aplicação

### Opção 2: Via SSH (Recomendado)

```bash
# 1. Conectar via SSH
ssh seu_usuario@revexa.com.br

# 2. Navegar até a pasta
cd /www/whatsapp-server

# 3. Instalar dependências
npm install

# 4. Instalar PM2 (se não tiver)
npm install -g pm2

# 5. Iniciar servidor
pm2 start server.js --name whatsapp-revexa

# 6. Salvar configuração
pm2 save
pm2 startup

# 7. Ver status
pm2 status
```

### Opção 3: Configuração Manual Nginx

Adicione no arquivo de configuração do Nginx:

```nginx
location /whatsapp/ {
    proxy_pass http://localhost:3001/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

Depois:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

## 🧪 Testar

```bash
# Testar localmente no servidor
curl http://localhost:3001/status

# Testar via internet
curl https://revexa.com.br/whatsapp/status
```

Deve retornar algo como:
```json
{"connected": false, "message": "WhatsApp disconnected"}
```

## 📱 Como Usar no App

1. Abra **REVEXA Barber**
2. Vá em **Configurações** → **Integração WhatsApp**
3. Clique em **Gerar QR Code**
4. Escaneie com WhatsApp do celular
5. Pronto! ✅

## 🐛 Problemas?

### Erro: Cannot find module '@whiskeysockets/baileys'
```bash
cd /www/whatsapp-server
npm install
```

### Servidor não inicia
```bash
# Ver logs
pm2 logs whatsapp-revexa

# Reiniciar
pm2 restart whatsapp-revexa
```

### Porta 3001 já em uso
```bash
# Ver o que está usando
lsof -i :3001

# Matar processo
kill -9 PID_DO_PROCESSO
```

## 📞 Suporte

Se precisar de ajuda, envie os logs:
```bash
pm2 logs whatsapp-revexa --lines 50
```
