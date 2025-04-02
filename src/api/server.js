
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const bodyParser = require('body-parser');

const app = express();
const port = process.env.PORT || 3001;

app.use(cors());
app.use(bodyParser.json());

// Globale Verbindungsvariable
let connection = null;

// Middleware zum Überprüfen der Datenbankverbindung
const checkConnection = async (req, res, next) => {
  if (!connection) {
    return res.status(503).json({ 
      error: 'Keine Datenbankverbindung vorhanden. Bitte stellen Sie zuerst eine Verbindung her.' 
    });
  }
  
  try {
    // Ping-Anfrage, um zu prüfen, ob die Verbindung noch aktiv ist
    await connection.query('SELECT 1');
    next();
  } catch (error) {
    connection = null;
    return res.status(503).json({ 
      error: 'Datenbankverbindung verloren. Bitte stellen Sie die Verbindung neu her.' 
    });
  }
};

// Verbindung zur Datenbank herstellen
app.post('/api/db/connect', async (req, res) => {
  const { host, port, user, password, database } = req.body;
  
  try {
    // Vorhandene Verbindung schließen, falls vorhanden
    if (connection) {
      await connection.end();
    }
    
    // Neue Verbindung erstellen
    connection = await mysql.createConnection({
      host,
      port,
      user,
      password,
      database
    });
    
    console.log('Datenbankverbindung hergestellt');
    res.json({ success: true, message: 'Datenbankverbindung hergestellt' });
  } catch (error) {
    console.error('Fehler bei der Datenbankverbindung:', error.message);
    res.status(500).json({ 
      success: false, 
      error: error.message || 'Fehler bei der Datenbankverbindung' 
    });
  }
});

// Verbindung trennen
app.post('/api/db/disconnect', async (req, res) => {
  try {
    if (connection) {
      await connection.end();
      connection = null;
      console.log('Datenbankverbindung getrennt');
      res.json({ success: true, message: 'Datenbankverbindung getrennt' });
    } else {
      res.json({ success: true, message: 'Keine aktive Verbindung vorhanden' });
    }
  } catch (error) {
    console.error('Fehler beim Trennen der Verbindung:', error.message);
    res.status(500).json({ 
      success: false, 
      error: error.message || 'Fehler beim Trennen der Verbindung' 
    });
  }
});

// Verbindungsstatus prüfen
app.get('/api/db/status', async (req, res) => {
  try {
    if (!connection) {
      return res.json({ connected: false });
    }
    
    // Versuchen, eine einfache Abfrage auszuführen
    await connection.query('SELECT 1');
    res.json({ connected: true });
  } catch (error) {
    connection = null;
    res.json({ connected: false });
  }
});

// SQL-Abfrage ausführen
app.post('/api/db/query', checkConnection, async (req, res) => {
  const { sql, params } = req.body;
  
  try {
    const [results] = await connection.query(sql, params || []);
    res.json({ success: true, data: results });
  } catch (error) {
    console.error('Fehler bei der SQL-Abfrage:', error.message);
    res.status(500).json({ 
      success: false, 
      error: error.message || 'Fehler bei der SQL-Abfrage' 
    });
  }
});

// Server starten
app.listen(port, () => {
  console.log(`Server läuft auf Port ${port}`);
});
