
import React, { createContext, useState, useEffect, ReactNode } from 'react';
import { User } from '@/types';

// Define the shape of our Auth context
interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string) => Promise<void>;
  logout: () => void;
  loading: boolean;
  error: string | null;
  getAllUsers: () => Promise<User[]>;
  updateUser: (user: User) => Promise<void>;
  activateUser: (userId: number) => Promise<void>;
}

// Create the context with a default value
export const AuthContext = createContext<AuthContextType>({
  user: null,
  login: async () => {},
  register: async () => {},
  logout: () => {},
  loading: false,
  error: null,
  getAllUsers: async () => [],
  updateUser: async () => {},
  activateUser: async () => {},
});

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Check if user is logged in on initial load
  useEffect(() => {
    const checkAuth = async () => {
      try {
        // Mock auth check - replace with actual API call in production
        const savedUser = localStorage.getItem('user');
        if (savedUser) {
          setUser(JSON.parse(savedUser));
        }
      } catch (err) {
        console.error("Auth check failed:", err);
        setError("Fehler bei der Authentifizierungsüberprüfung");
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, []);

  // Login function
  const login = async (email: string, password: string) => {
    setLoading(true);
    setError(null);
    
    try {
      // Mock login - replace with actual API call in production
      // Simulating API delay
      await new Promise(r => setTimeout(r, 800));
      
      if (email === "admin@marina.de" && password === "admin") {
        const mockUser: User = {
          id: 1,
          name: "Admin User",
          email: "admin@marina.de",
          role: "admin"
        };
        setUser(mockUser);
        localStorage.setItem('user', JSON.stringify(mockUser));
      } else if (email === "user@marina.de" && password === "user") {
        const mockUser: User = {
          id: 2,
          name: "Regular User",
          email: "user@marina.de",
          role: "user"
        };
        setUser(mockUser);
        localStorage.setItem('user', JSON.stringify(mockUser));
      } else {
        throw new Error("Ungültige Anmeldedaten");
      }
    } catch (err) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError("Anmeldung fehlgeschlagen");
      }
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Register function
  const register = async (name: string, email: string, password: string) => {
    setLoading(true);
    setError(null);
    
    try {
      // Mock registration - replace with actual API call in production
      // Simulating API delay
      await new Promise(r => setTimeout(r, 800));
      
      const mockUser: User = {
        id: Math.floor(Math.random() * 1000) + 3,
        name,
        email,
        role: "user"
      };
      
      setUser(mockUser);
      localStorage.setItem('user', JSON.stringify(mockUser));
    } catch (err) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError("Registrierung fehlgeschlagen");
      }
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Logout function
  const logout = () => {
    localStorage.removeItem('user');
    setUser(null);
  };

  // Admin: Get all users
  const getAllUsers = async (): Promise<User[]> => {
    // Mock user data - replace with actual API call in production
    return [
      {
        id: 1,
        name: "Admin User",
        email: "admin@marina.de",
        role: "admin"
      },
      {
        id: 2,
        name: "Regular User",
        email: "user@marina.de",
        role: "user"
      },
      {
        id: 3,
        name: "Test User",
        email: "test@marina.de",
        role: "user"
      }
    ];
  };

  // Admin: Update user
  const updateUser = async (updatedUser: User): Promise<void> => {
    // Mock API call - replace with actual API call in production
    console.log("Updated user:", updatedUser);
  };

  // Admin: Activate user
  const activateUser = async (userId: number): Promise<void> => {
    // Mock API call - replace with actual API call in production
    console.log("Activated user ID:", userId);
  };

  const value = {
    user,
    login,
    register,
    logout,
    loading,
    error,
    getAllUsers,
    updateUser,
    activateUser
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
