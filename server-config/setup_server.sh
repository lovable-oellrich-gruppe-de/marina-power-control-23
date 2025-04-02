
#!/bin/bash

# Dieses Skript richtet den NGINX-Webserver und MariaDB für Marina Power Control ein
# Es wird davon ausgegangen, dass es auf einem Debian/Ubuntu-System ausgeführt wird

# Fehler bei der Ausführung abfangen
set -e

# Farben für die Ausgabe
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}Marina Power Control Server-Setup${NC}"
echo "------------------------------------------------"

# Prüfen, ob das Skript als Root ausgeführt wird
if [ "$(id -u)" -ne 0 ]; then
   echo -e "${RED}Dieses Skript muss als Root ausgeführt werden.${NC}" 
   exit 1
fi

# Aktualisieren der Paketlisten
echo -e "${GREEN}Aktualisiere Paketlisten...${NC}"
apt-get update

# Installieren der benötigten Software
echo -e "${GREEN}Installiere benötigte Software...${NC}"
apt-get install -y nginx mariadb-server nodejs npm

# MariaDB absichern
echo -e "${GREEN}Sichere MariaDB ab...${NC}"
mysql_secure_installation

# NGINX-Konfiguration einrichten
echo -e "${GREEN}Konfiguriere NGINX...${NC}"
# Backup der Standard-Konfiguration
if [ -f /etc/nginx/sites-available/default ]; then
  mv /etc/nginx/sites-available/default /etc/nginx/sites-available/default.bak
fi

# Kopiere unsere Konfiguration
cp marina-power-control.conf /etc/nginx/sites-available/

# Aktiviere die Site
if [ -f /etc/nginx/sites-enabled/default ]; then
  rm /etc/nginx/sites-enabled/default
fi

ln -sf /etc/nginx/sites-available/marina-power-control.conf /etc/nginx/sites-enabled/

# Teste die NGINX-Konfiguration
nginx -t

# Erstelle das Webverzeichnis
echo -e "${GREEN}Erstelle Webverzeichnis...${NC}"
mkdir -p /var/www/marina-power-control

# MariaDB-Datenbank einrichten
echo -e "${GREEN}Richte MariaDB-Datenbank ein...${NC}"
mysql < setup_db.sql

# Node.js API-Server installieren
echo -e "${GREEN}Richte Node.js API-Server ein...${NC}"
mkdir -p /opt/marina-power-api
cp server.js /opt/marina-power-api/
cd /opt/marina-power-api
npm init -y
npm install express cors mysql2 body-parser

# Systemd-Service für den API-Server erstellen
echo -e "${GREEN}Erstelle Systemd-Service für den API-Server...${NC}"
cat > /etc/systemd/system/marina-power-api.service << EOF
[Unit]
Description=Marina Power Control API Server
After=network.target mariadb.service

[Service]
ExecStart=/usr/bin/node /opt/marina-power-api/server.js
WorkingDirectory=/opt/marina-power-api
Restart=always
User=www-data
Group=www-data
Environment=NODE_ENV=production PORT=3001

[Install]
WantedBy=multi-user.target
EOF

# Berechtigungen setzen
chown -R www-data:www-data /var/www/marina-power-control
chown -R www-data:www-data /opt/marina-power-api

# Dienste starten
echo -e "${GREEN}Starte Dienste...${NC}"
systemctl daemon-reload
systemctl restart nginx
systemctl enable --now marina-power-api

echo -e "${GREEN}Installation abgeschlossen!${NC}"
echo "------------------------------------------------"
echo "1. Bauen Sie Ihre React-Anwendung mit 'npm run build'"
echo "2. Kopieren Sie den Inhalt des 'dist'-Verzeichnisses nach /var/www/marina-power-control"
echo "3. Besuchen Sie http://your-domain.com oder die IP-Adresse Ihres Servers"
