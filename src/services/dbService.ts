
import mysql from 'mysql2/promise';
import { toast } from '@/hooks/use-toast';

// Datenbank-Konfiguration
interface DatabaseConfig {
  host: string;
  user: string;
  password: string;
  database: string;
  port: number;
}

// Initialer Konfigurationsversuch aus dem localStorage
const getDbConfig = (): DatabaseConfig | null => {
  const storedConfig = localStorage.getItem('marina-db-config');
  if (storedConfig) {
    try {
      return JSON.parse(storedConfig);
    } catch (error) {
      console.error('Fehler beim Parsen der Datenbankkonfiguration:', error);
      localStorage.removeItem('marina-db-config');
      return null;
    }
  }
  return null;
};

// Konfiguration speichern
export const saveDbConfig = (config: DatabaseConfig): void => {
  localStorage.setItem('marina-db-config', JSON.stringify(config));
};

// Verbindungspool
let pool: mysql.Pool | null = null;

// Verbindungspool initialisieren
export const initializeDbConnection = async (config: DatabaseConfig): Promise<boolean> => {
  try {
    // Bestehenden Pool schließen, falls vorhanden
    if (pool) {
      await pool.end();
      pool = null;
    }

    // Neuen Pool erstellen
    pool = mysql.createPool({
      host: config.host,
      user: config.user,
      password: config.password,
      database: config.database,
      port: config.port,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0
    });

    // Verbindung testen
    const connection = await pool.getConnection();
    connection.release();
    
    // Konfiguration speichern
    saveDbConfig(config);
    
    toast({
      title: "Datenbankverbindung hergestellt",
      description: "Die Verbindung zur MariaDB wurde erfolgreich hergestellt."
    });
    
    return true;
  } catch (error) {
    console.error('Datenbankverbindungsfehler:', error);
    
    toast({
      variant: "destructive",
      title: "Verbindungsfehler",
      description: error instanceof Error ? error.message : "Fehler bei der Verbindung zur Datenbank"
    });
    
    return false;
  }
};

// Datenbankanfrage ausführen
export const query = async <T>(sql: string, params?: any[]): Promise<T> => {
  if (!pool) {
    const config = getDbConfig();
    if (!config) {
      throw new Error('Keine Datenbankverbindung konfiguriert');
    }
    
    const success = await initializeDbConnection(config);
    if (!success) {
      throw new Error('Fehler beim Verbinden zur Datenbank');
    }
  }
  
  try {
    const [results] = await pool!.execute(sql, params || []);
    return results as T;
  } catch (error) {
    console.error('SQL-Ausführungsfehler:', error);
    toast({
      variant: "destructive",
      title: "Datenbankfehler",
      description: error instanceof Error ? error.message : "Fehler bei der Datenbankabfrage"
    });
    throw error;
  }
};

// Prüfen, ob die Verbindung aktiv ist
export const isConnected = (): boolean => {
  return pool !== null;
};

// Verbindung schließen
export const closeDbConnection = async (): Promise<void> => {
  if (pool) {
    await pool.end();
    pool = null;
  }
};
