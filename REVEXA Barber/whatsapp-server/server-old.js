/**
 * Servidor WhatsApp - Óticas Marco Polo Simonésia
 * 
 * Este servidor gerencia a conexão com WhatsApp Web e fornece uma API REST
 * para envio de mensagens a partir do sistema PHP.
 * 
 * Recursos:
 * - Conexão via QR Code
 * - API REST para envio de mensagens
 * - Verificação de status de conexão
 * - Log de mensagens enviadas
 */

const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Estado da conexão
let connectionStatus = {
    connected: false,
    qrCode: null,
    lastQrGenerated: null,
    phoneNumber: null,
    clientInfo: null
};

// Fila de mensagens
let messageQueue = [];
let isProcessingQueue = false;

// Log de mensagens
const logDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
}

// Função para log
function log(message, type = 'info') {
    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] [${type.toUpperCase()}] ${message}`;
    console.log(logMessage);
    
    const logFile = path.join(logDir, `${new Date().toISOString().split('T')[0]}.log`);
    fs.appendFileSync(logFile, logMessage + '\n');
}

// Inicializar cliente WhatsApp
const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: path.join(__dirname, '.wwebjs_auth')
    }),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--single-process',
            '--disable-gpu'
        ]
    }
});

// Eventos do cliente WhatsApp
client.on('qr', (qr) => {
    log('QR Code gerado');
    connectionStatus.qrCode = qr;
    connectionStatus.lastQrGenerated = new Date().toISOString();
    connectionStatus.connected = false;
    
    // Mostrar QR no terminal
    console.log('\n========================================');
    console.log('ESCANEIE O QR CODE COM SEU WHATSAPP:');
    console.log('========================================\n');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    log('WhatsApp conectado com sucesso!');
    connectionStatus.connected = true;
    connectionStatus.qrCode = null;
    connectionStatus.phoneNumber = client.info?.wid?.user || 'N/A';
    connectionStatus.clientInfo = client.info;
    
    console.log('\n========================================');
    console.log('✅ WHATSAPP CONECTADO!');
    console.log(`📱 Número: ${connectionStatus.phoneNumber}`);
    console.log('========================================\n');
    
    // Processar fila pendente
    processQueue();
});

client.on('authenticated', () => {
    log('Autenticação bem-sucedida');
});

client.on('auth_failure', (msg) => {
    log(`Falha na autenticação: ${msg}`, 'error');
    connectionStatus.connected = false;
});

client.on('disconnected', (reason) => {
    log(`Desconectado: ${reason}`, 'warn');
    connectionStatus.connected = false;
    connectionStatus.qrCode = null;
    
    // Tentar reconectar
    setTimeout(() => {
        log('Tentando reconectar...');
        client.initialize();
    }, 5000);
});

// Função para formatar número de telefone
function formatPhoneNumber(phone) {
    // Remove caracteres não numéricos
    let cleaned = phone.replace(/\D/g, '');
    
    // Se começa com 0, remove
    if (cleaned.startsWith('0')) {
        cleaned = cleaned.substring(1);
    }
    
    // Se não tem código do país, adiciona 55 (Brasil)
    if (!cleaned.startsWith('55')) {
        cleaned = '55' + cleaned;
    }
    
    // Adiciona @c.us para WhatsApp
    return cleaned + '@c.us';
}

// Função para enviar mensagem
async function sendMessage(phone, message) {
    if (!connectionStatus.connected) {
        throw new Error('WhatsApp não está conectado');
    }
    
    const formattedPhone = formatPhoneNumber(phone);
    
    try {
        // Verificar se o número existe no WhatsApp
        const isRegistered = await client.isRegisteredUser(formattedPhone);
        if (!isRegistered) {
            throw new Error('Número não está registrado no WhatsApp');
        }
        
        // Enviar mensagem
        const result = await client.sendMessage(formattedPhone, message);
        log(`Mensagem enviada para ${phone}`);
        
        return {
            success: true,
            messageId: result.id._serialized,
            timestamp: result.timestamp
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
            const result = await sendMessage(item.phone, item.message);
            
            // Callback de sucesso
            if (item.callback) {
                item.callback(null, result);
            }
        } catch (error) {
            // Callback de erro
            if (item.callback) {
                item.callback(error, null);
            }
        }
        
        // Delay entre mensagens (evitar bloqueio)
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
            hasQrCode: !!connectionStatus.qrCode,
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
            data: {
                connected: true,
                message: 'WhatsApp já está conectado'
            }
        });
    }
    
    if (!connectionStatus.qrCode) {
        return res.json({
            success: false,
            message: 'QR Code ainda não foi gerado. Aguarde...'
        });
    }
    
    res.json({
        success: true,
        data: {
            connected: false,
            qrCode: connectionStatus.qrCode,
            generatedAt: connectionStatus.lastQrGenerated
        }
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
        // Envio prioritário (direto) ou via fila
        if (priority === 'high') {
            const result = await sendMessage(phone, message);
            res.json({
                success: true,
                data: result
            });
        } else {
            // Adicionar à fila
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

// Enviar múltiplas mensagens
app.post('/send-batch', async (req, res) => {
    const { messages } = req.body;
    
    if (!Array.isArray(messages) || messages.length === 0) {
        return res.status(400).json({
            success: false,
            message: 'Array de mensagens é obrigatório'
        });
    }
    
    if (!connectionStatus.connected) {
        return res.status(503).json({
            success: false,
            message: 'WhatsApp não está conectado'
        });
    }
    
    // Adicionar todas à fila
    messages.forEach(msg => {
        if (msg.phone && msg.message) {
            messageQueue.push({
                phone: msg.phone,
                message: msg.message,
                id: msg.id || null
            });
        }
    });
    
    processQueue();
    
    res.json({
        success: true,
        data: {
            queued: messages.length,
            totalInQueue: messageQueue.length,
            message: 'Mensagens adicionadas à fila'
        }
    });
});

// Verificar se número existe no WhatsApp
app.post('/check-number', async (req, res) => {
    const { phone } = req.body;
    
    if (!phone) {
        return res.status(400).json({
            success: false,
            message: 'Telefone é obrigatório'
        });
    }
    
    if (!connectionStatus.connected) {
        return res.status(503).json({
            success: false,
            message: 'WhatsApp não está conectado'
        });
    }
    
    try {
        const formattedPhone = formatPhoneNumber(phone);
        const isRegistered = await client.isRegisteredUser(formattedPhone);
        
        res.json({
            success: true,
            data: {
                phone: phone,
                registered: isRegistered
            }
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
        await client.logout();
        connectionStatus.connected = false;
        connectionStatus.qrCode = null;
        
        res.json({
            success: true,
            message: 'Desconectado com sucesso'
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
        if (connectionStatus.connected) {
            await client.logout();
        }
        
        connectionStatus.connected = false;
        connectionStatus.qrCode = null;
        
        client.initialize();
        
        res.json({
            success: true,
            message: 'Reconexão iniciada. Aguarde o QR Code.'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

// Limpar fila
app.delete('/queue', (req, res) => {
    const count = messageQueue.length;
    messageQueue = [];
    
    res.json({
        success: true,
        message: `${count} mensagens removidas da fila`
    });
});

// Status da fila
app.get('/queue', (req, res) => {
    res.json({
        success: true,
        data: {
            size: messageQueue.length,
            processing: isProcessingQueue,
            items: messageQueue.slice(0, 10) // Mostra apenas as primeiras 10
        }
    });
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

// Iniciar servidor
app.listen(PORT, () => {
    console.log('\n========================================');
    console.log('🚀 SERVIDOR WHATSAPP INICIADO');
    console.log(`📡 API rodando em http://localhost:${PORT}`);
    console.log('========================================\n');
    
    log(`Servidor iniciado na porta ${PORT}`);
    
    // Inicializar cliente WhatsApp
    console.log('Iniciando conexão com WhatsApp...\n');
    client.initialize();
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
    
    if (connectionStatus.connected) {
        await client.destroy();
    }
    
    process.exit(0);
});
