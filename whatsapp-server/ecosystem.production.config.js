module.exports = {
  apps: [{
    name: 'whatsapp-gateway-absensi',
    script: 'server.js',
    cwd: '/www/wwwroot/absensi/Absensi/whatsapp-server-absensi',
    env: {
      PORT: 3001
    }
  }]
};
