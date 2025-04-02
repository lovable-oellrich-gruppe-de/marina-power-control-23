
import React, { createContext, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { User } from '@/types';
import { useToast } from '@/hooks/use-toast';
import { authService } from '@/services/authService';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (email: string, password: string, name: string) => Promise<void>;
  logout: () => void;
  getAllUsers: () => Promise<User[]>;
  updateUser: (userId: string, userData: { name: string; role: 'admin' | 'user'; status?: 'active' | 'pending' }) => Promise<User>;
  activateUser: (userId: string) => Promise<User>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();
  const { toast } = useToast();
  
  // Beim Laden der Anwendung prüfen, ob ein Benutzer im localStorage gespeichert ist
  useEffect(() => {
    const storedUser = authService.getSavedUser();
    if (storedUser) {
      setUser(storedUser);
    }
    setIsLoading(false);
  }, []);

  const login = async (email: string, password: string) => {
    try {
      const loggedInUser = await authService.login({ email, password });
      setUser(loggedInUser);
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
      await authService.register({ email, password, name });
      navigate('/login');
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
    authService.logout();
    setUser(null);
    navigate('/login');
  };
  
  const getAllUsers = async (): Promise<User[]> => {
    return authService.getAllUsers();
  };
  
  const updateUser = async (
    userId: string, 
    userData: { name: string; role: 'admin' | 'user'; status?: 'active' | 'pending' }
  ): Promise<User> => {
    const updatedUser = await authService.updateUser(userId, userData);
    
    // Wenn der aktuelle Benutzer aktualisiert wurde, aktualisiere auch den User-State
    if (user && user.id === userId) {
      const currentUserUpdated = {
        ...user,
        name: userData.name,
        role: userData.role,
        ...(userData.status && { status: userData.status }),
      };
      setUser(currentUserUpdated);
      authService.saveUser(currentUserUpdated);
    }
    
    return updatedUser;
  };
  
  const activateUser = async (userId: string): Promise<User> => {
    return authService.activateUser(userId);
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        isLoading,
        login,
        register,
        logout,
        getAllUsers,
        updateUser,
        activateUser
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

// Hook wird in separate Datei verschoben
export { AuthContext };
