#!/bin/bash
# Fix Nginx Configuration Issue on Production Server
# Run this script on the production server: bash fix-nginx-server.sh

echo "=== Fixing Nginx Configuration Issue ==="
echo ""

# Step 1: Find the problematic file
echo "Step 1: Looking for enable-php-84.conf files..."
find /www/server -name "enable-php-84.conf" 2>/dev/null
echo ""

# Step 2: Check vhost config for includes
echo "Step 2: Checking vhost config for problematic includes..."
VHOST_FILE="/www/server/panel/vhost/nginx/absensi.smkpgriblora.sch.id.conf"
if [ -f "$VHOST_FILE" ]; then
    echo "Found vhost config: $VHOST_FILE"
    echo "Checking for enable-php-84.conf includes:"
    grep -n "enable-php-84" "$VHOST_FILE" || echo "No includes found (GOOD!)"
else
    echo "Vhost config not found at expected location"
    echo "Searching for it..."
    find /www/server -name "*absensi.smkpgriblora.sch.id*.conf" 2>/dev/null
fi
echo ""

# Step 3: Backup and fix if found
echo "Step 3: Checking if file exists in wrong location..."
WRONG_FILE="/www/server/panel/vhost/nginx/enable-php-84.conf"
if [ -f "$WRONG_FILE" ]; then
    echo "Found problematic file: $WRONG_FILE"
    echo "Backing up..."
    cp "$WRONG_FILE" "$WRONG_FILE.backup.$(date +%Y%m%d_%H%M%S)"
    echo "Removing..."
    rm "$WRONG_FILE"
    echo "Removed $WRONG_FILE"
else
    echo "File not found at $WRONG_FILE (GOOD!)"
fi
echo ""

# Step 4: Test nginx config
echo "Step 4: Testing nginx configuration..."
nginx -t
NGINX_TEST=$?
echo ""

if [ $NGINX_TEST -eq 0 ]; then
    echo "✅ Nginx config is VALID!"
    echo ""
    echo "Step 5: Restarting nginx..."
    systemctl restart nginx
    sleep 2
    systemctl status nginx --no-pager | head -10
    echo ""
    echo "Step 6: Checking nginx processes..."
    ps aux | grep nginx | grep -v grep | head -5
    echo ""
    echo "✅ Nginx fixed and restarted successfully!"
else
    echo "❌ Nginx config still has errors"
    echo "You may need to manually edit the vhost config"
    echo ""
    echo "Try this command to see the error:"
    echo "  nginx -t"
    echo ""
    echo "To edit the config:"
    echo "  nano $VHOST_FILE"
    echo "  # Look for lines with 'include' and 'enable-php-84.conf'"
    echo "  # Comment them out with # or remove them"
fi

echo ""
echo "=== Done ==="
