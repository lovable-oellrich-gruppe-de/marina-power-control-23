
import { User } from '@/types';
import { toast } from '@/hooks/use-toast';

// Typen
type LoginCredentials = {
  email: string;
  password: string;
};

type RegisterData = {
  email: string;
  password: string;
  name: string;
};

type UpdateUserData = {
  name: string;
  role: 'admin' | 'user';
  status?: 'active' | 'pending';
};

// Mock-Benutzer für die Demozwecke
const mockUsersInitial = [
  {
    id: '1',
    email: 'admin@marina-power.de',
    name: 'Admin',
    password: 'admin123',
    role: 'admin' as const,
    status: 'active' as const,
    avatar: ''
  },
  {
    id: '2',
    email: 'benutzer@marina-power.de',
    name: 'Benutzer',
    password: 'benutzer123',
    role: 'user' as const,
    status: 'active' as const,
    avatar: ''
  }
];

// Service-Klasse
class AuthService {
  private getMockUsers(): any[] {
    const storedUsers = localStorage.getItem('marina-power-users');
    if (storedUsers) {
      try {
        return JSON.parse(storedUsers);
      } catch (error) {
        localStorage.removeItem('marina-power-users');
        return mockUsersInitial;
      }
    }
    return mockUsersInitial;
  }

  private setMockUsers(users: any[]): void {
    localStorage.setItem('marina-power-users', JSON.stringify(users));
  }

  getSavedUser(): User | null {
    const storedUser = localStorage.getItem('marina-power-user');
    if (storedUser) {
      try {
        return JSON.parse(storedUser);
      } catch (error) {
        localStorage.removeItem('marina-power-user');
        return null;
      }
    }
    return null;
  }

  saveUser(user: User): void {
    localStorage.setItem('marina-power-user', JSON.stringify(user));
  }

  removeUser(): void {
    localStorage.removeItem('marina-power-user');
  }

  async login({ email, password }: LoginCredentials): Promise<User> {
    const mockUsers = this.getMockUsers();
    
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    const foundUser = mockUsers.find(
      (u) => u.email === email && u.password === password
    );
    
    if (!foundUser) {
      throw new Error('Ungültige Anmeldeinformationen');
    }
    
    if (foundUser.status === 'pending') {
      throw new Error('Ihr Konto wurde noch nicht freigeschaltet. Bitte wenden Sie sich an einen Administrator.');
    }
    
    const { password: _, ...userWithoutPassword } = foundUser;
    
    toast({
      title: 'Erfolgreich angemeldet',
      description: `Willkommen zurück, ${userWithoutPassword.name}!`,
    });
    
    this.saveUser(userWithoutPassword);
    
    return userWithoutPassword;
  }

  async register({ email, password, name }: RegisterData): Promise<void> {
    const mockUsers = this.getMockUsers();
    
    // Prüfen, ob die E-Mail bereits existiert
    if (mockUsers.some((u) => u.email === email)) {
      throw new Error('Diese E-Mail-Adresse wird bereits verwendet');
    }
    
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    const newUser = {
      id: (mockUsers.length + 1).toString(),
      email,
      name,
      password, // In einer echten Anwendung würde das Passwort gehasht werden
      role: 'user' as const,
      status: 'pending' as const, // Neuer Benutzer ist standardmäßig "pending"
      avatar: ''
    };
    
    this.setMockUsers([...mockUsers, newUser]);
    
    toast({
      title: 'Registrierung erfolgreich',
      description: 'Ihr Konto wurde erstellt und wartet auf Freischaltung durch einen Administrator.',
    });
  }

  logout(): void {
    this.removeUser();
    
    toast({
      title: 'Abgemeldet',
      description: 'Sie wurden erfolgreich abgemeldet.',
    });
  }

  async getAllUsers(): Promise<User[]> {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    const mockUsers = this.getMockUsers();
    return mockUsers.map(({ password, ...user }) => user);
  }

  async updateUser(userId: string, userData: UpdateUserData): Promise<User> {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    const mockUsers = this.getMockUsers();
    const userIndex = mockUsers.findIndex(u => u.id === userId);
    
    if (userIndex === -1) {
      throw new Error('Benutzer nicht gefunden');
    }
    
    const updatedMockUsers = [...mockUsers];
    updatedMockUsers[userIndex] = {
      ...updatedMockUsers[userIndex],
      name: userData.name,
      role: userData.role,
      ...(userData.status && { status: userData.status }),
    };
    
    this.setMockUsers(updatedMockUsers);
    
    const { password, ...updatedUserWithoutPassword } = updatedMockUsers[userIndex];
    return updatedUserWithoutPassword;
  }

  async activateUser(userId: string): Promise<User> {
    const mockUsers = this.getMockUsers();
    const user = mockUsers.find(u => u.id === userId);
    
    if (!user) {
      throw new Error('Benutzer nicht gefunden');
    }
    
    return this.updateUser(userId, { 
      name: user.name, 
      role: user.role,
      status: 'active'
    });
  }
}

export const authService = new AuthService();
