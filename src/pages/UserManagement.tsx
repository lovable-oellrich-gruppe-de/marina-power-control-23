
import { useState, useEffect } from 'react';
import NavBar from '@/components/layout/NavBar';
import { useAuth } from '@/hooks/useAuth';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { User } from '@/types';
import { useToast } from '@/hooks/use-toast';

const UserManagement = () => {
  const { getAllUsers, updateUser, activateUser, user } = useAuth();
  const { toast } = useToast();
  
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  
  useEffect(() => {
    const fetchUsers = async () => {
      try {
        const userData = await getAllUsers();
        setUsers(userData);
      } catch (err) {
        console.error('Error fetching users:', err);
        toast({
          title: 'Fehler',
          description: 'Benutzer konnten nicht geladen werden',
          variant: 'destructive',
        });
      } finally {
        setLoading(false);
      }
    };
    
    fetchUsers();
  }, [getAllUsers, toast]);
  
  const handleEditUser = (user: User) => {
    setSelectedUser(user);
    setIsDialogOpen(true);
  };
  
  const handleSaveUser = async () => {
    if (!selectedUser) return;
    
    try {
      await updateUser(selectedUser);
      
      setUsers((prevUsers) =>
        prevUsers.map((u) => (u.id === selectedUser.id ? selectedUser : u))
      );
      
      toast({
        title: 'Erfolg',
        description: 'Benutzer wurde aktualisiert',
      });
      
      setIsDialogOpen(false);
    } catch (err) {
      console.error('Error updating user:', err);
      toast({
        title: 'Fehler',
        description: 'Benutzer konnte nicht aktualisiert werden',
        variant: 'destructive',
      });
    }
  };
  
  const handleActivateUser = async (userId: number) => {
    try {
      await activateUser(userId);
      
      toast({
        title: 'Erfolg',
        description: 'Benutzer wurde aktiviert',
      });
    } catch (err) {
      console.error('Error activating user:', err);
      toast({
        title: 'Fehler',
        description: 'Benutzer konnte nicht aktiviert werden',
        variant: 'destructive',
      });
    }
  };
  
  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Benutzerverwaltung</h1>
        
        <Card>
          <CardHeader>
            <CardTitle>Benutzer</CardTitle>
            <CardDescription>
              Verwalten Sie Benutzer und deren Berechtigungen
            </CardDescription>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-center py-4">Benutzer werden geladen...</div>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>E-Mail</TableHead>
                    <TableHead>Rolle</TableHead>
                    <TableHead className="text-right">Aktionen</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {users.map((user) => (
                    <TableRow key={user.id}>
                      <TableCell>{user.name}</TableCell>
                      <TableCell>{user.email}</TableCell>
                      <TableCell>
                        <Badge variant={user.role === 'admin' ? 'default' : 'outline'}>
                          {user.role === 'admin' ? 'Administrator' : 'Benutzer'}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-2">
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleEditUser(user)}
                          >
                            Bearbeiten
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleActivateUser(user.id)}
                          >
                            Aktivieren
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>
        
        <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Benutzer bearbeiten</DialogTitle>
              <DialogDescription>
                Bearbeiten Sie die Benutzerinformationen und Berechtigungen
              </DialogDescription>
            </DialogHeader>
            
            {selectedUser && (
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Name</Label>
                  <Input
                    id="name"
                    value={selectedUser.name}
                    onChange={(e) =>
                      setSelectedUser({ ...selectedUser, name: e.target.value })
                    }
                  />
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="email">E-Mail</Label>
                  <Input
                    id="email"
                    value={selectedUser.email}
                    onChange={(e) =>
                      setSelectedUser({ ...selectedUser, email: e.target.value })
                    }
                  />
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="role">Rolle</Label>
                  <Select
                    value={selectedUser.role}
                    onValueChange={(value: 'admin' | 'user') =>
                      setSelectedUser({ ...selectedUser, role: value })
                    }
                  >
                    <SelectTrigger id="role">
                      <SelectValue placeholder="Rolle auswählen" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="admin">Administrator</SelectItem>
                      <SelectItem value="user">Benutzer</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            )}
            
            <DialogFooter>
              <Button variant="outline" onClick={() => setIsDialogOpen(false)}>
                Abbrechen
              </Button>
              <Button onClick={handleSaveUser}>Speichern</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </main>
    </div>
  );
};

export default UserManagement;
