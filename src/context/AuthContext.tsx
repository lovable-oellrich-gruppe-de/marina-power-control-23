
import React, { createContext, useContext, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { User } from '@/types';
import { useToast } from '@/hooks/use-toast';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (email: string, password: string, name: string) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

// Mock-Benutzer für die Demozwecke
const mockUsers = [
  {
    id: '1',
    email: 'admin@marina-power.de',
    name: 'Admin',
    password: 'admin123',
    role: 'admin' as const,
    avatar: ''
  },
  {
    id: '2',
    email: 'benutzer@marina-power.de',
    name: 'Benutzer',
    password: 'benutzer123',
    role: 'user' as const,
    avatar: ''
  }
];

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();
  const { toast } = useToast();
  
  // Beim Laden der Anwendung prüfen, ob ein Benutzer im localStorage gespeichert ist
  useEffect(() => {
    const storedUser = localStorage.getItem('marina-power-user');
    if (storedUser) {
      try {
        const parsedUser = JSON.parse(storedUser);
        setUser(parsedUser);
      } catch (error) {
        localStorage.removeItem('marina-power-user');
      }
    }
    setIsLoading(false);
  }, []);

  const login = async (email: string, password: string) => {
    try {
      // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
      const foundUser = mockUsers.find(
        (u) => u.email === email && u.password === password
      );
      
      if (!foundUser) {
        throw new Error('Ungültige Anmeldeinformationen');
      }
      
      const { password: _, ...userWithoutPassword } = foundUser;
      setUser(userWithoutPassword);
      localStorage.setItem('marina-power-user', JSON.stringify(userWithoutPassword));
      
      toast({
        title: 'Erfolgreich angemeldet',
        description: `Willkommen zurück, ${userWithoutPassword.name}!`,
      });
      
      navigate('/');
    } catch (error) {
      toast({
        variant: 'destructive',
        title: 'Anmeldung fehlgeschlagen',
        description: error instanceof Error ? error.message : 'Ein Fehler ist aufgetreten',
      });
      throw error;
    }
  };

  const register = async (email: string, password: string, name: string) => {
    try {
      // Prüfen, ob die E-Mail bereits existiert
      if (mockUsers.some((u) => u.email === email)) {
        throw new Error('Diese E-Mail-Adresse wird bereits verwendet');
      }
      
      // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
      const newUser = {
        id: (mockUsers.length + 1).toString(),
        email,
        name,
        role: 'user' as const,
      };
      
      setUser(newUser);
      localStorage.setItem('marina-power-user', JSON.stringify(newUser));
      
      toast({
        title: 'Erfolgreich registriert',
        description: `Willkommen, ${name}!`,
      });
      
      navigate('/');
    } catch (error) {
      toast({
        variant: 'destructive',
        title: 'Registrierung fehlgeschlagen',
        description: error instanceof Error ? error.message : 'Ein Fehler ist aufgetreten',
      });
      throw error;
    }
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem('marina-power-user');
    navigate('/login');
    toast({
      title: 'Abgemeldet',
      description: 'Sie wurden erfolgreich abgemeldet.',
    });
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        isLoading,
        login,
        register,
        logout
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth muss innerhalb eines AuthProviders verwendet werden');
  }
  return context;
};
