require('dotenv').config();
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { default: makeWASocket, DisconnectReason, useMultiFileAuthState, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');
const QRCode = require('qrcode');

const app = express();
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || 'localhost';

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(express.static('public')); // Serve static files from public folder

// API Key authentication middleware
const API_KEY = process.env.API_KEY || '';
function authenticateApiKey(req, res, next) {
    // Skip auth for health check and QR endpoints
    if (req.path === '/' || req.path === '/qr') return next();
    
    if (!API_KEY) return next(); // No API key configured = open access
    
    const providedKey = req.headers['x-api-key'] || req.query.api_key;
    if (providedKey === API_KEY) return next();
    
    return res.status(401).json({ success: false, message: 'Invalid or missing API key' });
}
app.use(authenticateApiKey);

// Global variables
let sock = null;
let qrCodeData = null;
let connectionState = 'disconnected';
let reconnectAttempts = 0;
let manualLogout = false; // Flag untuk manual logout
const MAX_RECONNECT_ATTEMPTS = parseInt(process.env.MAX_RECONNECT_ATTEMPTS) || 5;
const RECONNECT_INTERVAL = parseInt(process.env.RECONNECT_INTERVAL) || 5000;

// Logger
const logger = pino({ 
    level: process.env.LOG_LEVEL || 'info',
    transport: {
        target: 'pino-pretty',
        options: {
            colorize: true,
            translateTime: 'SYS:standard',
            ignore: 'pid,hostname'
        }
    }
});

// Initialize WhatsApp Connection
async function connectToWhatsApp() {
    try {
        const { state, saveCreds } = await useMultiFileAuthState(process.env.SESSION_NAME || 'spmb-wa-session');
        const { version } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            logger: pino({ level: 'silent' }),
            printQRInTerminal: true,
            auth: state,
            browser: ['SPMB Gateway', 'Chrome', '1.0.0'],
            defaultQueryTimeoutMs: undefined,
        });

        // Connection update handler
        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                qrCodeData = qr;
                connectionState = 'qr';
                logger.info('QR Code generated, scan with WhatsApp');
                
                // Generate QR code as data URL with high error correction
                try {
                    const qrDataURL = await QRCode.toDataURL(qr, {
                        errorCorrectionLevel: 'H',
                        type: 'image/png',
                        quality: 1,
                        margin: 2,
                        width: 512,
                        color: {
                            dark: '#000000',
                            light: '#FFFFFF'
                        }
                    });
                    qrCodeData = qrDataURL;
                    logger.info('QR Code data URL generated successfully');
                } catch (err) {
                    logger.error('Failed to generate QR code:', err);
                }
            }

            if (connection === 'close') {
                const shouldReconnect = (lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut) || manualLogout;
                connectionState = 'disconnected';
                logger.warn('Connection closed. Should reconnect:', shouldReconnect, 'Manual logout:', manualLogout);

                if (shouldReconnect) {
                    // Reset reconnect attempts jika manual logout
                    if (manualLogout) {
                        reconnectAttempts = 0;
                        logger.info('Manual logout detected - resetting reconnect counter');
                    }
                    
                    if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                        reconnectAttempts++;
                        logger.info(`Reconnecting... Attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS}`);
                        setTimeout(connectToWhatsApp, RECONNECT_INTERVAL);
                    } else {
                        logger.error('Max reconnect attempts reached. Please restart the server.');
                        manualLogout = false; // Reset flag
                    }
                } else {
                    logger.info('Logged out. Please scan QR code again.');
                    manualLogout = false; // Reset flag
                }
            } else if (connection === 'open') {
                connectionState = 'connected';
                reconnectAttempts = 0;
                manualLogout = false; // Reset flag saat berhasil connect
                qrCodeData = null;
                logger.info('WhatsApp connection established successfully!');
            }
        });

        // Credentials update handler
        sock.ev.on('creds.update', saveCreds);

        // Messages handler (optional - for receiving messages)
        sock.ev.on('messages.upsert', async ({ messages }) => {
            const msg = messages[0];
            if (!msg.key.fromMe && msg.message) {
                logger.info('Received message:', msg.message);
                // You can add auto-reply logic here if needed
            }
        });

    } catch (error) {
        logger.error('Failed to connect to WhatsApp:', error);
        connectionState = 'error';
        
        if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
            reconnectAttempts++;
            logger.info(`Retrying connection... Attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS}`);
            setTimeout(connectToWhatsApp, RECONNECT_INTERVAL);
        }
    }
}

// Helper function to format phone number
function formatPhoneNumber(phone) {
    // Remove all non-numeric characters
    let cleaned = phone.replace(/\D/g, '');
    
    // If starts with 0, replace with 62
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }
    
    // If doesn't start with 62, add it
    if (!cleaned.startsWith('62')) {
        cleaned = '62' + cleaned;
    }
    
    return cleaned + '@s.whatsapp.net';
}

// API Routes

// Health check
app.get('/', (req, res) => {
    res.json({
        success: true,
        message: 'SPMB WhatsApp Gateway Server',
        version: '1.0.0',
        status: connectionState,
        timestamp: new Date().toISOString()
    });
});

// Get connection status
app.get('/status', (req, res) => {
    res.json({
        success: true,
        status: connectionState,
        qrAvailable: qrCodeData !== null,
        reconnectAttempts: reconnectAttempts,
        timestamp: new Date().toISOString()
    });
});

// Get server health metrics
app.get('/health', (req, res) => {
    const memoryUsage = process.memoryUsage();
    const cpuUsage = process.cpuUsage();
    
    res.json({
        success: true,
        uptime: process.uptime(), // in seconds
        memory: {
            rss: Math.round(memoryUsage.rss / 1024 / 1024), // MB
            heapTotal: Math.round(memoryUsage.heapTotal / 1024 / 1024), // MB
            heapUsed: Math.round(memoryUsage.heapUsed / 1024 / 1024), // MB
            external: Math.round(memoryUsage.external / 1024 / 1024), // MB
            percentage: Math.round((memoryUsage.heapUsed / memoryUsage.heapTotal) * 100) // %
        },
        cpu: {
            user: Math.round(cpuUsage.user / 1000), // microseconds to milliseconds
            system: Math.round(cpuUsage.system / 1000) // microseconds to milliseconds
        },
        connection: {
            status: connectionState,
            reconnectAttempts: reconnectAttempts
        },
        node: {
            version: process.version,
            platform: process.platform,
            arch: process.arch
        },
        timestamp: new Date().toISOString()
    });
});

// Get QR code
app.get('/qr', (req, res) => {
    if (qrCodeData) {
        res.json({
            success: true,
            qr: qrCodeData,
            message: 'Scan this QR code with WhatsApp',
            connectionState: connectionState,
            timestamp: new Date().toISOString()
        });
    } else if (connectionState === 'connected') {
        res.json({
            success: false,
            message: 'Already connected to WhatsApp',
            connectionState: connectionState,
            timestamp: new Date().toISOString()
        });
    } else {
        res.json({
            success: false,
            message: 'QR code not available yet. Please wait or try logout first...',
            connectionState: connectionState,
            reconnectAttempts: reconnectAttempts,
            timestamp: new Date().toISOString()
        });
    }
});

// Send single message
app.post('/send', async (req, res) => {
    try {
        const { phone, message } = req.body;

        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                message: 'Phone and message are required'
            });
        }

        if (connectionState !== 'connected') {
            return res.status(503).json({
                success: false,
                message: 'WhatsApp not connected',
                status: connectionState
            });
        }

        const formattedPhone = formatPhoneNumber(phone);
        
        await sock.sendMessage(formattedPhone, { text: message });
        
        logger.info(`Message sent to ${phone}`);
        
        res.json({
            success: true,
            message: 'Message sent successfully',
            to: phone,
            timestamp: new Date().toISOString()
        });

    } catch (error) {
        logger.error('Failed to send message:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to send message',
            error: error.message
        });
    }
});

// Send media (image) with caption
app.post('/send-media', async (req, res) => {
    try {
        const multer = require('multer');
        const upload = multer({ storage: multer.memoryStorage() });
        const fs = require('fs');
        const path = require('path');
        const { promisify } = require('util');
        const writeFile = promisify(fs.writeFile);
        const unlink = promisify(fs.unlink);

        // Parse multipart form data
        upload.single('media')(req, res, async (err) => {
            if (err) {
                logger.error('Multer error:', err);
                return res.status(400).json({
                    success: false,
                    message: 'Failed to upload media',
                    error: err.message
                });
            }

            try {
                const { phone, caption } = req.body;
                const mediaFile = req.file;

                if (!phone) {
                    return res.status(400).json({
                        success: false,
                        message: 'Phone is required'
                    });
                }

                if (!mediaFile) {
                    return res.status(400).json({
                        success: false,
                        message: 'Media file is required'
                    });
                }

                if (connectionState !== 'connected') {
                    return res.status(503).json({
                        success: false,
                        message: 'WhatsApp not connected',
                        status: connectionState
                    });
                }

                const formattedPhone = formatPhoneNumber(phone);

                // Save temp file
                const tempDir = path.join(__dirname, 'temp');
                if (!fs.existsSync(tempDir)) {
                    fs.mkdirSync(tempDir, { recursive: true });
                }

                const tempFilePath = path.join(tempDir, `${Date.now()}_${mediaFile.originalname}`);
                await writeFile(tempFilePath, mediaFile.buffer);

                try {
                    // Determine media type
                    const mimeType = mediaFile.mimetype;
                    let mediaType = 'image'; // default

                    if (mimeType.startsWith('video/')) {
                        mediaType = 'video';
                    } else if (mimeType.startsWith('audio/')) {
                        mediaType = 'audio';
                    } else if (mimeType === 'application/pdf') {
                        mediaType = 'document';
                    }

                    // Send media
                    const messageOptions = {
                        [mediaType]: { url: tempFilePath }
                    };

                    if (caption) {
                        messageOptions.caption = caption;
                    }

                    const result = await sock.sendMessage(formattedPhone, messageOptions);

                    logger.info(`Media sent to ${phone}, type: ${mediaType}`);

                    // Clean up temp file
                    await unlink(tempFilePath);

                    res.json({
                        success: true,
                        message: 'Media sent successfully',
                        to: phone,
                        mediaType: mediaType,
                        messageId: result.key.id,
                        timestamp: new Date().toISOString()
                    });

                } catch (sendError) {
                    // Clean up temp file on error
                    if (fs.existsSync(tempFilePath)) {
                        await unlink(tempFilePath);
                    }
                    throw sendError;
                }

            } catch (error) {
                logger.error('Failed to send media:', error);
                res.status(500).json({
                    success: false,
                    message: 'Failed to send media',
                    error: error.message
                });
            }
        });

    } catch (error) {
        logger.error('Send media endpoint error:', error);
        res.status(500).json({
            success: false,
            message: 'Internal server error',
            error: error.message
        });
    }
});

// Send bulk messages
app.post('/send-bulk', async (req, res) => {
    try {
        const { messages } = req.body;

        if (!messages || !Array.isArray(messages)) {
            return res.status(400).json({
                success: false,
                message: 'Messages array is required'
            });
        }

        if (connectionState !== 'connected') {
            return res.status(503).json({
                success: false,
                message: 'WhatsApp not connected',
                status: connectionState
            });
        }

        const results = [];

        for (const item of messages) {
            try {
                const { phone, message } = item;
                
                if (!phone || !message) {
                    results.push({
                        phone,
                        success: false,
                        error: 'Phone and message are required'
                    });
                    continue;
                }

                const formattedPhone = formatPhoneNumber(phone);
                await sock.sendMessage(formattedPhone, { text: message });
                
                results.push({
                    phone,
                    success: true,
                    timestamp: new Date().toISOString()
                });

                logger.info(`Bulk message sent to ${phone}`);

                // Delay between messages to avoid spam detection
                await new Promise(resolve => setTimeout(resolve, 1000));

            } catch (error) {
                results.push({
                    phone: item.phone,
                    success: false,
                    error: error.message
                });
                logger.error(`Failed to send bulk message to ${item.phone}:`, error);
            }
        }

        const successCount = results.filter(r => r.success).length;
        const failedCount = results.length - successCount;

        res.json({
            success: true,
            message: `Sent ${successCount} messages, ${failedCount} failed`,
            total: results.length,
            successCount,
            failedCount,
            results
        });

    } catch (error) {
        logger.error('Failed to send bulk messages:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to send bulk messages',
            error: error.message
        });
    }
});

// Restart server (process restart with PM2 auto-restart)
app.post('/restart', async (req, res) => {
    try {
        logger.info('Server restart requested via API');
        
        // Send response first before restarting
        res.json({
            success: true,
            message: 'Server is restarting... Please wait 5-10 seconds.'
        });
        
        // Delay 2 seconds to ensure response is sent
        setTimeout(() => {
            logger.info('Exiting process for restart...');
            process.exit(0); // PM2 will auto-restart
        }, 2000);
        
    } catch (error) {
        logger.error('Failed to restart server:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to restart server',
            error: error.message
        });
    }
});

// Logout/disconnect
app.post('/logout', async (req, res) => {
    try {
        logger.info('Logout requested - preparing to disconnect and generate new QR...');
        
        // Set flag sebelum logout
        manualLogout = true;
        reconnectAttempts = 0;
        
        // Logout dari WhatsApp (jika masih connected)
        if (sock) {
            try {
                await sock.logout();
                logger.info('Successfully logged out from WhatsApp');
            } catch (logoutError) {
                logger.warn('Logout error (might be already disconnected):', logoutError.message);
            }
            
            // Close socket connection
            try {
                await sock.end();
                logger.info('Socket connection closed');
            } catch (endError) {
                logger.warn('Socket end error:', endError.message);
            }
        }
        
        // Update state
        connectionState = 'disconnected';
        qrCodeData = null;
        sock = null;
        
        // Hapus session folder untuk force generate QR baru
        const fs = require('fs');
        const path = require('path');
        const sessionPath = path.join(__dirname, process.env.SESSION_NAME || 'spmb-wa-session');
        
        // Tunggu sebentar sebelum hapus session
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        try {
            if (fs.existsSync(sessionPath)) {
                fs.rmSync(sessionPath, { recursive: true, force: true });
                logger.info('✅ Session folder deleted successfully');
            } else {
                logger.info('ℹ️  Session folder does not exist, skipping deletion');
            }
        } catch (err) {
            logger.error('❌ Failed to delete session folder:', err);
        }
        
        // Tunggu sebentar lagi sebelum reconnect
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Trigger reconnect untuk generate QR baru
        logger.info('🔄 Starting reconnection to generate new QR code...');
        connectToWhatsApp();
        
        res.json({
            success: true,
            message: 'Logged out successfully. Generating new QR code... Please wait 5-10 seconds and click "Lihat QR".'
        });
        
    } catch (error) {
        logger.error('Failed to logout:', error);
        manualLogout = false; // Reset flag on error
        res.status(500).json({
            success: false,
            message: 'Failed to logout',
            error: error.message
        });
    }
});

// Get list of groups
app.get('/groups', async (req, res) => {
    try {
        if (connectionState !== 'connected') {
            return res.status(503).json({ success: false, message: 'WhatsApp not connected' });
        }
        
        const groups = await sock.groupFetchAllParticipating();
        const groupList = Object.values(groups).map(g => ({
            id: g.id,
            name: g.subject || g.id,
            participants: g.participants?.length || 0,
            creation: g.creation
        })).sort((a, b) => a.name.localeCompare(b.name));
        
        res.json({ success: true, groups: groupList });
    } catch (error) {
        logger.error('Failed to fetch groups:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// Send message to group
app.post('/send-group', async (req, res) => {
    try {
        const { groupId, message } = req.body;
        
        if (!groupId || !message) {
            return res.status(400).json({ success: false, message: 'groupId and message are required' });
        }
        
        if (connectionState !== 'connected') {
            return res.status(503).json({ success: false, message: 'WhatsApp not connected' });
        }
        
        // Ensure groupId ends with @g.us
        const gid = groupId.endsWith('@g.us') ? groupId : groupId + '@g.us';
        
        await sock.sendMessage(gid, { text: message });
        
        logger.info('Group message sent to ' + gid);
        
        res.json({
            success: true,
            message: 'Group message sent successfully',
            to: gid,
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        logger.error('Failed to send group message:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// Start server
app.listen(PORT, HOST, () => {
    logger.info(`WhatsApp Gateway Server running on http://${HOST}:${PORT}`);
    logger.info('Connecting to WhatsApp...');
    connectToWhatsApp();
});

// Graceful shutdown
process.on('SIGINT', async () => {
    logger.info('Shutting down gracefully...');
    if (sock) {
        await sock.end();
    }
    process.exit(0);
});
