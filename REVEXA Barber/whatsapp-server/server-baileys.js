/**
 * Servidor WhatsApp com Baileys - Óticas Marco Polo
 * Não requer Puppeteer/Chrome - funciona em hospedagem compartilhada
 */

const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { default: makeWASocket, DisconnectReason, useMultiFileAuthState } = require('@whiskeysockets/baileys');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');
const pino = require('pino');

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Estado da conexão
let sock = null;
let connectionStatus = {
    connected: false,
    qrCode: null,
    qrCodeBase64: null,
    phoneNumber: null
};

// Fila de mensagens
let messageQueue = [];

// Diretório de autenticação
const authDir = path.join(__dirname, '.wwebjs_auth');
if (!fs.existsSync(authDir)) {
    fs.mkdirSync(authDir, { recursive: true });
}

// Log
const logDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
}

function log(message, type = 'info') {
    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] [${type.toUpperCase()}] ${message}`;
    console.log(logMessage);
    
    const logFile = path.join(logDir, `${new Date().toISOString().split('T')[0]}.log`);
    fs.appendFileSync(logFile, logMessage + '\n');
}

// Iniciar conexão WhatsApp
async function startWhatsApp() {
    try {
        const { state, saveCreds } = await useMultiFileAuthState(authDir);
        
        sock = makeWASocket({
            auth: state,
            printQRInTerminal: true,
            logger: pino({ level: 'silent' })
        });
        
        sock.ev.on('creds.update', saveCreds);
        
        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;
            
            if (qr) {
                log('QR Code gerado');
                connectionStatus.qrCode = qr;
                
                // Gerar QR Code em base64 para exibir na web
                try {
                    connectionStatus.qrCodeBase64 = await QRCode.toDataURL(qr);
                } catch (err) {
                    log('Erro ao gerar QR Code base64: ' + err.message, 'error');
                }
                
                // Mostrar QR no terminal
                console.log('\n========================================');
                console.log('ESCANEIE O QR CODE COM SEU WHATSAPP:');
                console.log('========================================\n');
            }
            
            if (connection === 'close') {
                const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
                log('Conexão fechada. Reconectar: ' + shouldReconnect);
                
                connectionStatus.connected = false;
                connectionStatus.qrCode = null;
                connectionStatus.qrCodeBase64 = null;
                
                if (shouldReconnect) {
                    setTimeout(startWhatsApp, 5000);
                }
            } else if (connection === 'open') {
                log('WhatsApp conectado!');
                connectionStatus.connected = true;
                connectionStatus.qrCode = null;
                connectionStatus.qrCodeBase64 = null;
                connectionStatus.phoneNumber = sock.user?.id?.split(':')[0] || null;
            }
        });
        
        sock.ev.on('messages.upsert', (m) => {
            // Log de mensagens recebidas (opcional)
        });
        
    } catch (error) {
        log('Erro ao iniciar WhatsApp: ' + error.message, 'error');
        setTimeout(startWhatsApp, 10000);
    }
}

// Enviar mensagem
async function sendMessage(phone, message) {
    if (!sock || !connectionStatus.connected) {
        throw new Error('WhatsApp não está conectado');
    }
    
    // Formatar número
    let formattedPhone = phone.replace(/\D/g, '');
    if (!formattedPhone.endsWith('@s.whatsapp.net')) {
        formattedPhone = formattedPhone + '@s.whatsapp.net';
    }
    
    try {
        await sock.sendMessage(formattedPhone, { text: message });
        log(`Mensagem enviada para ${phone}`);
        return { success: true };
    } catch (error) {
        log(`Erro ao enviar mensagem para ${phone}: ${error.message}`, 'error');
        throw error;
    }
}

// ============ ROTAS API ============

// Status
app.get('/status', (req, res) => {
    res.json({
        success: true,
        data: {
            connected: connectionStatus.connected,
            hasQrCode: !!connectionStatus.qrCode,
            phoneNumber: connectionStatus.phoneNumber,
            queueSize: messageQueue.length,
            serverTime: new Date().toISOString()
        }
    });
});

// QR Code
app.get('/qrcode', (req, res) => {
    if (connectionStatus.connected) {
        return res.json({
            success: true,
            connected: true,
            message: 'WhatsApp já está conectado',
            phoneNumber: connectionStatus.phoneNumber
        });
    }
    
    if (connectionStatus.qrCodeBase64) {
        return res.json({
            success: true,
            qrCode: connectionStatus.qrCodeBase64
        });
    }
    
    res.json({
        success: false,
        message: 'QR Code ainda não foi gerado. Aguarde...'
    });
});

// Enviar mensagem
app.post('/send', async (req, res) => {
    try {
        const { phone, message } = req.body;
        
        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                message: 'Telefone e mensagem são obrigatórios'
            });
        }
        
        await sendMessage(phone, message);
        
        res.json({
            success: true,
            message: 'Mensagem enviada com sucesso'
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

// Desconectar
app.post('/disconnect', async (req, res) => {
    try {
        if (sock) {
            await sock.logout();
        }
        connectionStatus.connected = false;
        connectionStatus.qrCode = null;
        connectionStatus.qrCodeBase64 = null;
        connectionStatus.phoneNumber = null;
        
        res.json({ success: true, message: 'Desconectado' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Reconectar
app.post('/reconnect', async (req, res) => {
    try {
        if (sock) {
            sock.end();
        }
        setTimeout(startWhatsApp, 1000);
        res.json({ success: true, message: 'Reconectando...' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Página inicial
app.get('/', (req, res) => {
    res.send(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>WhatsApp Server - Óticas Marco Polo</title>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
                .status { padding: 20px; border-radius: 10px; margin: 20px 0; }
                .connected { background: #d4edda; color: #155724; }
                .disconnected { background: #f8d7da; color: #721c24; }
                .qrcode { text-align: center; margin: 20px 0; }
                .qrcode img { max-width: 300px; }
                button { padding: 10px 20px; margin: 5px; cursor: pointer; }
            </style>
        </head>
        <body>
            <h1>🚀 WhatsApp Server</h1>
            <div id="status" class="status">Carregando...</div>
            <div id="qrcode" class="qrcode"></div>
            <div>
                <button onclick="checkStatus()">Atualizar Status</button>
                <button onclick="reconnect()">Reconectar</button>
            </div>
            <script>
                async function checkStatus() {
                    const res = await fetch('/status');
                    const data = await res.json();
                    const statusDiv = document.getElementById('status');
                    const qrDiv = document.getElementById('qrcode');
                    
                    if (data.data.connected) {
                        statusDiv.className = 'status connected';
                        statusDiv.innerHTML = '✅ Conectado - ' + (data.data.phoneNumber || 'WhatsApp');
                        qrDiv.innerHTML = '';
                    } else {
                        statusDiv.className = 'status disconnected';
                        statusDiv.innerHTML = '❌ Desconectado';
                        
                        const qrRes = await fetch('/qrcode');
                        const qrData = await qrRes.json();
                        if (qrData.qrCode) {
                            qrDiv.innerHTML = '<p>Escaneie o QR Code:</p><img src="' + qrData.qrCode + '">';
                        } else {
                            qrDiv.innerHTML = '<p>Aguardando QR Code...</p>';
                        }
                    }
                }
                
                async function reconnect() {
                    await fetch('/reconnect', { method: 'POST' });
                    setTimeout(checkStatus, 3000);
                }
                
                checkStatus();
                setInterval(checkStatus, 5000);
            </script>
        </body>
        </html>
    `);
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log('\n========================================');
    console.log('🚀 SERVIDOR WHATSAPP INICIADO');
    console.log(`📡 API rodando em http://localhost:${PORT}`);
    console.log('========================================\n');
    
    log(`Servidor iniciado na porta ${PORT}`);
    startWhatsApp();
});

// Graceful shutdown
process.on('SIGINT', () => {
    log('Encerrando servidor...');
    if (sock) sock.end();
    process.exit(0);
});

process.on('SIGTERM', () => {
    log('Encerrando servidor...');
    if (sock) sock.end();
    process.exit(0);
});
