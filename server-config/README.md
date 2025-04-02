
# Webserver-Konfiguration für Marina Power Control

Dieser Ordner enthält Beispiel-Konfigurationsdateien für das Hosting der Marina Power Control Anwendung auf verschiedenen Webservern.

## Apache-Konfiguration

Die Datei `.htaccess` sollte im Root-Verzeichnis der gebauten Anwendung platziert werden:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # Falls die angeforderte Ressource nicht existiert
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  
  # Alle Anfragen an die index.html umleiten
  RewriteRule ^ index.html [QSA,L]
</IfModule>

# CORS aktivieren, falls benötigt
<IfModule mod_headers.c>
  Header set Access-Control-Allow-Origin "*"
  Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
  Header set Access-Control-Allow-Headers "X-Requested-With, Content-Type, Authorization"
</IfModule>

# Cache-Einstellungen für statische Assets
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
  ExpiresByType application/x-javascript "access plus 1 month"
</IfModule>
```

## NGINX-Konfiguration

Erstellen Sie eine Datei namens `marina-power-control.conf` im NGINX-Konfigurationsverzeichnis (üblicherweise `/etc/nginx/sites-available/`):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/build;
    index index.html;

    # Gzip-Konfiguration
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    # Frontend-Routen verarbeiten
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache für statische Assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    # CORS aktivieren
    add_header 'Access-Control-Allow-Origin' '*';
    add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS, PUT, DELETE';
    add_header 'Access-Control-Allow-Headers' 'X-Requested-With, Content-Type, Authorization';
}
```

Erstellen Sie dann einen symbolischen Link, um die Site zu aktivieren:
```bash 
sudo ln -s /etc/nginx/sites-available/marina-power-control.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Hinweise für die Bereitstellung

1. Bauen Sie die Anwendung mit `npm run build`
2. Übertragen Sie den Inhalt des `dist`-Verzeichnisses auf Ihren Webserver
3. Stellen Sie sicher, dass die oben genannten Konfigurationen korrekt eingerichtet sind
4. Bei Problemen mit der Datenbankverbindung, stellen Sie sicher, dass der MariaDB-Server von Ihrem Webserver aus erreichbar ist

## Datenbank-Konfiguration

Die Marina Power Control App speichert ihre Datenbankverbindungsdaten im Browser-LocalStorage. 
Beim ersten Start der Anwendung müssen die Benutzer die Datenbankverbindungsdaten in den Einstellungen eingeben.

Wenn Sie MariaDB auf demselben Server wie Ihren Webserver betreiben, stellen Sie sicher, dass:

1. MariaDB auf die richtigen Netzwerkschnittstellen hört (nicht nur localhost)
2. Der Datenbankbenutzer Zugriffsrechte von der IP des Clients hat
3. Die notwendigen Datenbanken und Tabellen erstellt wurden
