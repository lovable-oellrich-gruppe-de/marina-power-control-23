
import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { useNavigate } from 'react-router-dom';
import { User } from '@/types';
import { useToast } from '@/hooks/use-toast';

// Mock user data for demonstration
const mockUsers: User[] = [
  {
    id: 'admin1',
    email: 'admin@marina-power.de',
    name: 'Administrator',
    role: 'admin',
    status: 'active',
  },
  {
    id: 'user1',
    email: 'benutzer@marina-power.de',
    name: 'Normaler Benutzer',
    role: 'user',
    status: 'active',
  },
  {
    id: 'user2',
    email: 'neu@marina-power.de',
    name: 'Neuer Benutzer',
    role: 'user',
    status: 'pending',
  },
];

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  register: (email: string, password: string, name: string) => Promise<void>;
  updateUser: (userId: string, userData: Partial<User>) => Promise<User>;
  activateUser: (userId: string) => Promise<User>;
  getAllUsers: () => Promise<User[]>;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { toast } = useToast();

  // Prüfen, ob ein Benutzer bereits angemeldet ist
  useEffect(() => {
    const storedUser = localStorage.getItem('marina-user');
    if (storedUser) {
      try {
        setUser(JSON.parse(storedUser));
      } catch (error) {
        console.error('Fehler beim Parsen des gespeicherten Benutzers:', error);
        localStorage.removeItem('marina-user');
      }
    }
    setLoading(false);
  }, []);

  const login = async (email: string, password: string) => {
    setLoading(true);

    // In einer echten App würde hier eine API-Anfrage stattfinden
    await new Promise(resolve => setTimeout(resolve, 1000));

    const foundUser = mockUsers.find(u => 
      u.email.toLowerCase() === email.toLowerCase() && u.status === 'active'
    );

    if (foundUser) {
      // Simuliere erfolgreiches Login mit festem Passwort "password"
      if (password === 'password') {
        localStorage.setItem('marina-user', JSON.stringify(foundUser));
        setUser(foundUser);
        toast({
          title: "Erfolgreich angemeldet",
          description: `Willkommen zurück, ${foundUser.name}!`,
        });
        navigate('/');
      } else {
        toast({
          variant: "destructive",
          title: "Fehler bei der Anmeldung",
          description: "Ungültiges Passwort",
        });
      }
    } else {
      toast({
        variant: "destructive",
        title: "Fehler bei der Anmeldung",
        description: "Benutzer nicht gefunden oder nicht aktiviert",
      });
    }

    setLoading(false);
  };

  const logout = () => {
    localStorage.removeItem('marina-user');
    setUser(null);
    navigate('/login');
    toast({
      title: "Abgemeldet",
      description: "Ihr Konto wurde erfolgreich abgemeldet",
    });
  };

  const register = async (email: string, password: string, name: string) => {
    setLoading(true);

    // In einer echten App würde hier eine API-Anfrage stattfinden
    await new Promise(resolve => setTimeout(resolve, 1000));

    const existingUser = mockUsers.find(u => u.email.toLowerCase() === email.toLowerCase());
    if (existingUser) {
      toast({
        variant: "destructive",
        title: "Registrierung fehlgeschlagen",
        description: "Ein Benutzer mit dieser E-Mail-Adresse existiert bereits",
      });
      setLoading(false);
      throw new Error('Benutzer existiert bereits');
    }

    // Registrierung simulieren
    const newUser: User = {
      id: `user${Date.now()}`,
      email,
      name,
      role: 'user',
      status: 'pending', // Neu registrierte Benutzer müssen aktiviert werden
    };

    // In einer echten App würde der Benutzer in die Datenbank eingefügt werden
    mockUsers.push(newUser);

    setLoading(false);
    return;
  };

  const updateUser = async (userId: string, userData: Partial<User>): Promise<User> => {
    // In einer echten App würde hier eine API-Anfrage stattfinden
    await new Promise(resolve => setTimeout(resolve, 1000));

    const userIndex = mockUsers.findIndex(u => u.id === userId);
    if (userIndex === -1) {
      throw new Error('Benutzer nicht gefunden');
    }

    // Benutzer aktualisieren
    const updatedUser = {
      ...mockUsers[userIndex],
      ...userData,
    };
    mockUsers[userIndex] = updatedUser;

    return updatedUser;
  };

  const activateUser = async (userId: string): Promise<User> => {
    // In einer echten App würde hier eine API-Anfrage stattfinden
    await new Promise(resolve => setTimeout(resolve, 1000));

    const userIndex = mockUsers.findIndex(u => u.id === userId);
    if (userIndex === -1) {
      throw new Error('Benutzer nicht gefunden');
    }

    // Benutzer aktivieren
    mockUsers[userIndex].status = 'active';

    return mockUsers[userIndex];
  };

  const getAllUsers = async (): Promise<User[]> => {
    // In einer echten App würde hier eine API-Anfrage stattfinden
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    return [...mockUsers];
  };

  return (
    <AuthContext.Provider value={{ 
      user, 
      loading, 
      login, 
      logout, 
      register,
      updateUser,
      activateUser,
      getAllUsers
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
