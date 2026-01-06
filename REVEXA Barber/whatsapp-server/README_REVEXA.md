# 📱 Servidor WhatsApp - REVEXA Barber

Servidor de integração WhatsApp para o sistema REVEXA Barber usando Baileys.

## 🚀 Como Usar

### 1. Iniciar o Servidor

```bash
cd whatsapp-server
npm start
```

O servidor iniciará na porta **3001**.

### 2. Conectar WhatsApp

1. Abra o app REVEXA Barber
2. Vá em **Configurações** → **Integração WhatsApp**
3. Clique em **Gerar QR Code**
4. Escaneie com seu WhatsApp (Aparelhos conectados → Conectar aparelho)
5. Aguarde a confirmação de conexão

### 3. Funcionalidades

**✅ O que você pode fazer:**
- Enviar lembretes automáticos de agendamento
- Notificar clientes sobre promoções
- Confirmar agendamentos por WhatsApp
- Enviar mensagens personalizadas

**📡 Endpoints da API:**

#### GET `/status`
Verifica status da conexão
```json
{
  "connected": true,
  "phoneNumber": "5532999999999",
  "qrCodeBase64": null
}
```

#### POST `/generate-qr`
Gera novo QR Code para conexão
```json
{
  "qrCodeBase64": "data:image/png;base64,..."
}
```

#### POST `/send-message`
Envia mensagem para um número
```json
{
  "phone": "5532999999999",
  "message": "Olá! Seu agendamento está confirmado."
}
```

#### POST `/disconnect`
Desconecta a sessão WhatsApp

## 🏃 Executando em Produção

### Opção 1: Node.js Direto
```bash
npm start
```

### Opção 2: PM2 (Recomendado)
```bash
npm install -g pm2
pm2 start server.js --name revexa-whatsapp
pm2 save
pm2 startup
```

### Opção 3: Docker
```bash
docker build -t revexa-whatsapp .
docker run -d -p 3001:3001 --name revexa-whatsapp revexa-whatsapp
```

## 📝 Notas Importantes

- O servidor **NÃO** precisa de navegador/Puppeteer
- A sessão é salva em `auth_info/` (não delete essa pasta!)
- Logs são salvos em `logs/`
- Requer Node.js 18+ 
- Use sempre números no formato internacional (ex: 5532999999999)

## 🔧 Configuração de Porta

Por padrão usa porta 3001. Para alterar:

```bash
PORT=8080 npm start
```

Ou crie arquivo `.env`:
```
PORT=3001
```

## 🆘 Troubleshooting

**Problema:** QR Code não aparece
- **Solução:** Reinicie o servidor e tente novamente

**Problema:** Conexão caiu
- **Solução:** Gere novo QR Code e reconecte

**Problema:** Mensagens não enviam
- **Solução:** Verifique se o número está no formato correto (55...)

## 📦 Estrutura de Pastas

```
whatsapp-server/
├── auth_info/          # Sessão WhatsApp (NÃO DELETE!)
├── logs/               # Logs do servidor
├── public/            # Interface web
│   └── index.html     # Dashboard WhatsApp
├── server.js          # Servidor principal
└── package.json       # Dependências
```

## 🔐 Segurança

⚠️ **IMPORTANTE:**
- Mantenha `auth_info/` seguro (contém sessão autenticada)
- Use HTTPS em produção
- Configure firewall para porta 3001
- Não compartilhe QR Codes

## 📧 Suporte

Problemas? Entre em contato com o suporte técnico REVEXA.

---

**Desenvolvido para REVEXA Barber** 💈✨
