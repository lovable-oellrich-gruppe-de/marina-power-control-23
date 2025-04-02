
import { toast } from '@/hooks/use-toast';

// Datenbank-Konfiguration
export interface DatabaseConfig {
  host: string;
  user: string;
  password: string;
  database: string;
  port: number;
}

// API-URL für Datenbankverbindungen
const API_BASE_URL = process.env.NODE_ENV === 'production' 
  ? '/api/db' // Im Produktionsmodus: Relativer Pfad für Proxy durch NGINX
  : 'http://localhost:3001/api/db'; // Im Entwicklungsmodus: Vollständiger Pfad

// Initialer Konfigurationsversuch aus dem localStorage
export const getDbConfig = (): DatabaseConfig | null => {
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

// Status der Konfiguration
let isConnectedState = false;

// Verbindung prüfen
export const checkConnectionStatus = async (): Promise<boolean> => {
  try {
    const response = await fetch(`${API_BASE_URL}/status`);
    const data = await response.json();
    isConnectedState = data.connected;
    return isConnectedState;
  } catch (error) {
    console.error('Fehler beim Prüfen der Verbindung:', error);
    isConnectedState = false;
    return false;
  }
};

// Verbindung testen
export const initializeDbConnection = async (config: DatabaseConfig): Promise<boolean> => {
  try {
    const response = await fetch(`${API_BASE_URL}/connect`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(config),
    });
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.error || 'Fehler bei der Verbindung zur Datenbank');
    }
    
    // Konfiguration speichern
    saveDbConfig(config);
    isConnectedState = true;
    
    toast({
      title: "Datenbankverbindung hergestellt",
      description: "Die Verbindung zur MariaDB wurde erfolgreich hergestellt."
    });
    
    return true;
  } catch (error) {
    console.error('Datenbankverbindungsfehler:', error);
    isConnectedState = false;
    
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
  if (!isConnectedState) {
    const status = await checkConnectionStatus();
    
    if (!status) {
      const config = getDbConfig();
      if (!config) {
        throw new Error('Keine Datenbankverbindung konfiguriert');
      }
      
      const success = await initializeDbConnection(config);
      if (!success) {
        throw new Error('Fehler beim Verbinden zur Datenbank');
      }
    }
  }
  
  try {
    const response = await fetch(`${API_BASE_URL}/query`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ sql, params }),
    });
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.error || 'Fehler bei der Datenbankabfrage');
    }
    
    return data.data as T;
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
  return isConnectedState;
};

// Verbindung trennen
export const closeDbConnection = async (): Promise<void> => {
  try {
    await fetch(`${API_BASE_URL}/disconnect`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    
    isConnectedState = false;
    toast({
      title: "Datenbankverbindung getrennt",
      description: "Die Verbindung zur MariaDB wurde getrennt."
    });
  } catch (error) {
    console.error('Fehler beim Trennen der Verbindung:', error);
    toast({
      variant: "destructive",
      title: "Fehler",
      description: "Fehler beim Trennen der Datenbankverbindung."
    });
  }
};
