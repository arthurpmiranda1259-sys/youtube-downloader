# 📱 Guia de Instalação - Servidor WhatsApp REVEXA Barber

## 🚀 Pré-requisitos no Servidor

1. **Node.js 16+** instalado
2. **PM2** para manter o servidor sempre rodando
3. **Nginx** configurado como proxy reverso

## 📦 Instalação no Servidor

### 1. Enviar arquivos para o servidor

```bash
# Fazer upload dos arquivos para /var/www/whatsapp-revexa/
# Estrutura:
/var/www/whatsapp-revexa/
├── server.js
├── package.json
├── README.md
└── auth_info_baileys/  (será criado automaticamente)
```

### 2. Instalar dependências

```bash
cd /var/www/whatsapp-revexa
npm install
```

### 3. Instalar PM2 (se ainda não tiver)

```bash
npm install -g pm2
```

### 4. Iniciar o servidor com PM2

```bash
pm2 start server.js --name whatsapp-revexa
pm2 save
pm2 startup
```

### 5. Configurar Nginx

Adicione esta configuração no arquivo do site (ex: `/etc/nginx/sites-available/revexa.com.br`):

```nginx
# Proxy para o servidor WhatsApp
location /whatsapp/ {
    proxy_pass http://localhost:3001/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

### 6. Recarregar Nginx

```bash
sudo nginx -t
sudo systemctl reload nginx
```

## ✅ Verificar Instalação

```bash
# Status do PM2
pm2 status

# Logs do servidor
pm2 logs whatsapp-revexa

# Testar endpoint
curl https://revexa.com.br/whatsapp/status
```

## 🔧 Comandos Úteis PM2

```bash
# Ver logs em tempo real
pm2 logs whatsapp-revexa

# Reiniciar servidor
pm2 restart whatsapp-revexa

# Parar servidor
pm2 stop whatsapp-revexa

# Remover do PM2
pm2 delete whatsapp-revexa

# Monitorar recursos
pm2 monit
```

## 📱 Como Conectar no App

1. Abra o app REVEXA Barber
2. Vá em **Configurações** → **Integração WhatsApp**
3. Clique em **Gerar QR Code**
4. Escaneie com seu WhatsApp (igual WhatsApp Web)
5. Pronto! 🎉

## 🔄 Atualizar Código do Servidor

```bash
# 1. Fazer upload do novo server.js
# 2. Reiniciar o PM2
cd /var/www/whatsapp-revexa
pm2 restart whatsapp-revexa
```

## 🐛 Troubleshooting

### Erro: ERR_CONNECTION_REFUSED

- Verificar se o servidor está rodando: `pm2 status`
- Verificar logs: `pm2 logs whatsapp-revexa`
- Verificar se a porta 3001 está livre: `lsof -i :3001`

### QR Code não aparece

- Ver logs do servidor: `pm2 logs whatsapp-revexa`
- Deletar pasta auth_info_baileys e reiniciar: `rm -rf auth_info_baileys && pm2 restart whatsapp-revexa`

### WhatsApp desconecta sozinho

- Verificar se o PM2 está salvando: `pm2 save`
- Verificar se o startup está configurado: `pm2 startup`

## 🔒 Segurança

⚠️ **IMPORTANTE**: Este servidor não tem autenticação! Considere adicionar:

1. **Token de autenticação** nos headers
2. **Rate limiting** para evitar abuso
3. **Firewall** permitindo apenas IPs do app
4. **HTTPS** obrigatório (já configurado via Nginx)

## 📞 Suporte

Para problemas, verificar:
1. Logs do PM2: `pm2 logs whatsapp-revexa`
2. Logs do Nginx: `tail -f /var/log/nginx/error.log`
3. Status do processo: `pm2 status`
