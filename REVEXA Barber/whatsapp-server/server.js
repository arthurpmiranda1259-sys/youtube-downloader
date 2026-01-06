/**
 * Servidor WhatsApp com Baileys - Óticas Marco Polo Simonésia
 * 
 * Usa @whiskeysockets/baileys - não precisa de Puppeteer/Chrome
 * Ideal para hospedagem compartilhada como KingHost
 */

import express from 'express';
import cors from 'cors';
import { makeWASocket, DisconnectReason, useMultiFileAuthState, fetchLatestBaileysVersion } from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import QRCode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import pino from 'pino';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Estado da conexão
let sock = null;
let connectionStatus = {
    connected: false,
    qrCode: null,
    qrCodeBase64: null,
    lastQrGenerated: null,
    phoneNumber: null,
    isConnecting: false
};

// Fila de mensagens
let messageQueue = [];
let isProcessingQueue = false;

// Diretório de logs
const logDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
}

// Função de log
function log(message, type = 'info') {
    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] [${type.toUpperCase()}] ${message}`;
    console.log(logMessage);
    
    const logFile = path.join(logDir, `${new Date().toISOString().split('T')[0]}.log`);
    fs.appendFileSync(logFile, logMessage + '\n');
}

// Iniciar conexão WhatsApp com Baileys
async function startWhatsApp() {
    if (connectionStatus.isConnecting) {
        log('Já está tentando conectar...');
        return;
    }
    
    connectionStatus.isConnecting = true;
    connectionStatus.qrCode = null;
    connectionStatus.qrCodeBase64 = null;
    
    const authDir = path.join(__dirname, 'auth_info_baileys');
    
    try {
        const { state, saveCreds } = await useMultiFileAuthState(authDir);
        
        // Obter a versão mais recente do WhatsApp Web
        const { version, isLatest } = await fetchLatestBaileysVersion();
        log(`Usando WhatsApp Web versão: ${version.join('.')}, isLatest: ${isLatest}`);
        
        // Logger silencioso para não poluir o console
        const logger = pino({ level: 'silent' });
        
        log('Iniciando conexão WhatsApp com Baileys...');
        
        sock = makeWASocket({
            version,
            auth: state,
            printQRInTerminal: true,
            logger,
            // Browser ID padrão que funciona melhor
            browser: ['WhatsApp Web', 'Chrome', '120.0.0.0'],
            connectTimeoutMs: 60000,
            qrTimeout: 40000,
            keepAliveIntervalMs: 30000,
            markOnlineOnConnect: false,
            syncFullHistory: false,
            getMessage: async () => undefined
        });
        
        // Salvar credenciais quando atualizarem
        sock.ev.on('creds.update', saveCreds);
        
        // Evento de conexão
        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr, receivedPendingNotifications } = update;
            
            log(`Connection update: connection=${connection}, qr=${qr ? 'SIM' : 'não'}, receivedPendingNotifications=${receivedPendingNotifications}`);
            
            // QR Code gerado
            if (qr) {
                log('*** NOVO QR CODE GERADO ***');
                connectionStatus.qrCode = qr;
                connectionStatus.lastQrGenerated = new Date().toISOString();
                connectionStatus.connected = false;
                connectionStatus.isConnecting = true;  // Mantém como conectando enquanto aguarda scan
                
                // Gerar QR Code como base64 para exibir na web
                try {
                    connectionStatus.qrCodeBase64 = await QRCode.toDataURL(qr, {
                        width: 300,
                        margin: 2,
                        errorCorrectionLevel: 'M'
                    });
                    log('QR Code base64 gerado com sucesso - pronto para escanear!');
                } catch (err) {
                    log('Erro ao gerar QR base64: ' + err.message, 'error');
                }
            }
            
            // Conexão estabelecida
            if (connection === 'open') {
                log('WhatsApp conectado com sucesso!');
                connectionStatus.connected = true;
                connectionStatus.qrCode = null;
                connectionStatus.qrCodeBase64 = null;
                connectionStatus.isConnecting = false;
                connectionStatus.phoneNumber = sock.user?.id?.split(':')[0]?.split('@')[0] || 'N/A';
                
                console.log('\n========================================');
                console.log('✅ WHATSAPP CONECTADO!');
                console.log(`📱 Número: ${connectionStatus.phoneNumber}`);
                console.log('========================================\n');
                
                // Processar fila pendente
                processQueue();
            }
            
            // Conexão fechada
            if (connection === 'close') {
                connectionStatus.connected = false;
                connectionStatus.isConnecting = false;
                
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const errorMessage = lastDisconnect?.error?.message || 'Desconhecido';
                
                log(`Conexão fechada. Código: ${statusCode}, Erro: ${errorMessage}`, 'warn');
                
                console.log('\n========================================');
                console.log('❌ CONEXÃO FECHADA');
                console.log(`📋 Código: ${statusCode}`);
                console.log(`📋 Motivo: ${errorMessage}`);
                console.log('========================================\n');
                
                // Se foi logout ou sessão inválida, limpar e gerar novo QR
                if (statusCode === DisconnectReason.loggedOut || 
                    statusCode === DisconnectReason.badSession ||
                    statusCode === 401 ||
                    statusCode === 403 ||
                    statusCode === 405) {
                    log('Sessão encerrada. Limpando para novo QR...');
                    if (fs.existsSync(authDir)) {
                        fs.rmSync(authDir, { recursive: true, force: true });
                    }
                    connectionStatus.qrCode = null;
                    connectionStatus.qrCodeBase64 = null;
                }
                
                // Sempre tentar reconectar (exceto logout manual)
                if (statusCode !== DisconnectReason.loggedOut) {
                    log('Reconectando em 5 segundos...');
                    setTimeout(() => {
                        startWhatsApp();
                    }, 5000);
                } else {
                    log('Logout detectado. Aguardando novo scan de QR Code...');
                    setTimeout(() => {
                        startWhatsApp();
                    }, 3000);
                }
            }
        });
        
        // Mensagens recebidas (opcional - para log)
        sock.ev.on('messages.upsert', async ({ messages }) => {
            for (const msg of messages) {
                if (!msg.key.fromMe && msg.message) {
                    const from = msg.key.remoteJid;
                    const text = msg.message.conversation || msg.message.extendedTextMessage?.text || '';
                    log(`Mensagem recebida de ${from}: ${text.substring(0, 50)}...`);
                }
            }
        });
        
    } catch (error) {
        log(`Erro ao iniciar WhatsApp: ${error.message}`, 'error');
        connectionStatus.isConnecting = false;
        
        // Tentar novamente em 10 segundos
        setTimeout(() => {
            startWhatsApp();
        }, 10000);
    }
}

// Formatar número de telefone
function formatPhoneNumber(phone) {
    let cleaned = phone.replace(/\D/g, '');
    
    if (cleaned.startsWith('0')) {
        cleaned = cleaned.substring(1);
    }
    
    if (!cleaned.startsWith('55')) {
        cleaned = '55' + cleaned;
    }
    
    return cleaned + '@s.whatsapp.net';
}

// Enviar mensagem
async function sendMessage(phone, message) {
    if (!connectionStatus.connected || !sock) {
        throw new Error('WhatsApp não está conectado');
    }
    
    const formattedPhone = formatPhoneNumber(phone);
    
    try {
        const result = await sock.sendMessage(formattedPhone, { text: message });
        log(`Mensagem enviada para ${phone}`);
        
        return {
            success: true,
            messageId: result.key.id,
            timestamp: new Date().toISOString()
        };
    } catch (error) {
        log(`Erro ao enviar para ${phone}: ${error.message}`, 'error');
        throw error;
    }
}

// Processar fila de mensagens
async function processQueue() {
    if (isProcessingQueue || messageQueue.length === 0) return;
    if (!connectionStatus.connected) return;
    
    isProcessingQueue = true;
    
    while (messageQueue.length > 0 && connectionStatus.connected) {
        const item = messageQueue.shift();
        
        try {
            await sendMessage(item.phone, item.message);
        } catch (error) {
            log(`Erro na fila: ${error.message}`, 'error');
        }
        
        // Delay entre mensagens
        await new Promise(resolve => setTimeout(resolve, 2000));
    }
    
    isProcessingQueue = false;
}

// ========== ROTAS API ==========

// Status da conexão
app.get('/status', (req, res) => {
    res.json({
        success: true,
        data: {
            connected: connectionStatus.connected,
            hasQrCode: !!connectionStatus.qrCodeBase64,
            phoneNumber: connectionStatus.phoneNumber,
            queueSize: messageQueue.length,
            serverTime: new Date().toISOString()
        }
    });
});

// Obter QR Code
app.get('/qrcode', (req, res) => {
    if (connectionStatus.connected) {
        return res.json({
            success: true,
            connected: true,
            message: 'WhatsApp já está conectado'
        });
    }
    
    if (!connectionStatus.qrCodeBase64) {
        return res.json({
            success: false,
            message: 'QR Code ainda não foi gerado. Aguarde...',
            isConnecting: connectionStatus.isConnecting
        });
    }
    
    res.json({
        success: true,
        connected: false,
        qrCode: connectionStatus.qrCodeBase64,
        generatedAt: connectionStatus.lastQrGenerated
    });
});

// Enviar mensagem
app.post('/send', async (req, res) => {
    const { phone, message, priority } = req.body;
    
    if (!phone || !message) {
        return res.status(400).json({
            success: false,
            message: 'Telefone e mensagem são obrigatórios'
        });
    }
    
    if (!connectionStatus.connected) {
        return res.status(503).json({
            success: false,
            message: 'WhatsApp não está conectado'
        });
    }
    
    try {
        if (priority === 'high') {
            const result = await sendMessage(phone, message);
            res.json({ success: true, data: result });
        } else {
            messageQueue.push({ phone, message });
            processQueue();
            res.json({
                success: true,
                data: {
                    queued: true,
                    position: messageQueue.length,
                    message: 'Mensagem adicionada à fila'
                }
            });
        }
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
        
        res.json({
            success: true,
            message: 'Desconectado com sucesso. Novo QR Code será gerado automaticamente.'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

// Reconectar
app.post('/reconnect', async (req, res) => {
    try {
        // Limpar sessão anterior
        if (sock) {
            try {
                await sock.logout();
            } catch (e) {
                // Ignorar erro de logout
            }
        }
        
        connectionStatus.connected = false;
        connectionStatus.qrCode = null;
        connectionStatus.qrCodeBase64 = null;
        connectionStatus.isConnecting = false;
        
        // Limpar pasta de autenticação para forçar novo QR
        const authDir = path.join(__dirname, 'auth_info_baileys');
        if (fs.existsSync(authDir)) {
            fs.rmSync(authDir, { recursive: true, force: true });
        }
        
        // Iniciar nova conexão
        setTimeout(() => {
            startWhatsApp();
        }, 1000);
        
        res.json({
            success: true,
            message: 'Reconexão iniciada. Aguarde o novo QR Code.'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

// Health check
app.get('/health', (req, res) => {
    res.json({
        success: true,
        data: {
            status: 'running',
            uptime: process.uptime(),
            whatsappConnected: connectionStatus.connected,
            timestamp: new Date().toISOString()
        }
    });
});

// Status da fila
app.get('/queue', (req, res) => {
    res.json({
        success: true,
        data: {
            size: messageQueue.length,
            processing: isProcessingQueue
        }
    });
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log('\n========================================');
    console.log('🚀 SERVIDOR WHATSAPP BAILEYS INICIADO');
    console.log(`📡 API rodando em http://localhost:${PORT}`);
    console.log('========================================\n');
    
    log(`Servidor iniciado na porta ${PORT}`);
    
    // Iniciar conexão WhatsApp
    startWhatsApp();
});

// Tratamento de erros
process.on('uncaughtException', (error) => {
    log(`Erro não tratado: ${error.message}`, 'error');
});

process.on('unhandledRejection', (reason, promise) => {
    log(`Promise rejeitada: ${reason}`, 'error');
});

// Graceful shutdown
process.on('SIGINT', async () => {
    log('Encerrando servidor...');
    
    if (sock) {
        sock.end();
    }
    
    process.exit(0);
});
