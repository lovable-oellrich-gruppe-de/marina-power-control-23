
import { useState, useEffect } from 'react';
import NavBar from '@/components/layout/NavBar';
import { useAuth } from '@/context/AuthContext';
import { Button } from '@/components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { User } from '@/types';

const UserManagement = () => {
  const { getAllUsers, updateUser, user: currentUser } = useAuth();
  const { toast } = useToast();
  const [users, setUsers] = useState<User[]>([]);
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [newUserData, setNewUserData] = useState({
    name: '',
    role: 'user' as 'admin' | 'user',
  });

  useEffect(() => {
    const loadUsers = async () => {
      const allUsers = await getAllUsers();
      setUsers(allUsers);
    };
    
    loadUsers();
  }, [getAllUsers]);

  const handleEditClick = (user: User) => {
    setSelectedUser(user);
    setNewUserData({
      name: user.name,
      role: user.role,
    });
    setIsEditDialogOpen(true);
  };

  const handleUpdateUser = async () => {
    if (!selectedUser) return;
    
    try {
      const updatedUser = await updateUser(selectedUser.id, newUserData);
      setUsers(users.map(u => u.id === updatedUser.id ? updatedUser : u));
      
      toast({
        title: 'Benutzer aktualisiert',
        description: `Benutzer ${updatedUser.name} wurde erfolgreich aktualisiert.`,
      });
      
      setIsEditDialogOpen(false);
    } catch (error) {
      toast({
        variant: 'destructive',
        title: 'Fehler',
        description: 'Der Benutzer konnte nicht aktualisiert werden.',
      });
    }
  };

  return (
    <div className="flex min-h-screen flex-col">
      <NavBar />
      <main className="flex-1 p-4 md:p-6">
        <div className="mx-auto max-w-6xl">
          <h1 className="text-2xl font-bold mb-6">Benutzerverwaltung</h1>
          
          <div className="bg-white rounded-lg shadow p-6">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>E-Mail</TableHead>
                  <TableHead>Rolle</TableHead>
                  <TableHead>Aktionen</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {users.map((user) => (
                  <TableRow key={user.id}>
                    <TableCell className="font-mono">{user.id}</TableCell>
                    <TableCell>{user.name}</TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell>
                      <span className={`inline-block px-2 py-1 rounded text-xs ${
                        user.role === 'admin' 
                          ? 'bg-red-100 text-red-800' 
                          : 'bg-blue-100 text-blue-800'
                      }`}>
                        {user.role === 'admin' ? 'Administrator' : 'Benutzer'}
                      </span>
                    </TableCell>
                    <TableCell>
                      <Button 
                        variant="outline" 
                        size="sm"
                        onClick={() => handleEditClick(user)}
                        disabled={user.id === currentUser?.id}
                      >
                        Bearbeiten
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </div>
      </main>

      <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Benutzer bearbeiten</DialogTitle>
            <DialogDescription>
              Bearbeiten Sie die Benutzerdetails unten.
            </DialogDescription>
          </DialogHeader>
          
          {selectedUser && (
            <div className="space-y-4 py-2">
              <div className="space-y-2">
                <Label htmlFor="email">E-Mail</Label>
                <Input 
                  id="email" 
                  value={selectedUser.email} 
                  disabled 
                />
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="name">Name</Label>
                <Input 
                  id="name" 
                  value={newUserData.name} 
                  onChange={(e) => setNewUserData({...newUserData, name: e.target.value})}
                />
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="role">Rolle</Label>
                <Select 
                  value={newUserData.role} 
                  onValueChange={(value) => 
                    setNewUserData({...newUserData, role: value as 'admin' | 'user'})
                  }
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Rolle auswählen" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="user">Benutzer</SelectItem>
                    <SelectItem value="admin">Administrator</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          )}
          
          <DialogFooter>
            <Button variant="outline" onClick={() => setIsEditDialogOpen(false)}>
              Abbrechen
            </Button>
            <Button onClick={handleUpdateUser}>
              Speichern
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default UserManagement;
