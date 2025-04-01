
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { useToast } from "@/components/ui/use-toast";
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { Mieter } from "@/types";

const MieterPage = () => {
  const { toast } = useToast();
  const [mieter, setMieter] = useState<Mieter[]>([
    {
      id: 1,
      vorname: "Max",
      nachname: "Mustermann",
      strasse: "Musterstraße",
      hausnummer: "123",
      email: "max@example.com",
      telefon: "01234567890",
      mobil: "0987654321",
      hinweis: "Stammkunde",
      bootsname: "Seeschwalbe"
    },
    {
      id: 2,
      vorname: "Julia",
      nachname: "Schmidt",
      strasse: "Hafenstraße",
      hausnummer: "45",
      email: "julia@example.com",
      telefon: "09876543210",
      mobil: "01234567890",
      hinweis: "",
      bootsname: "Wellentänzer"
    }
  ]);
  
  const [editingMieter, setEditingMieter] = useState<Mieter | null>(null);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  
  const columns = [
    { header: "ID", accessorKey: "id" },
    { header: "Name", accessorKey: (row: Mieter) => `${row.vorname} ${row.nachname}` },
    { header: "E-Mail", accessorKey: "email" },
    { header: "Telefon", accessorKey: "telefon" },
    { header: "Bootsname", accessorKey: "bootsname" }
  ];
  
  const handleAdd = () => {
    setEditingMieter({
      vorname: "",
      nachname: "",
      strasse: "",
      hausnummer: "",
      email: "",
      telefon: "",
      mobil: "",
      hinweis: "",
      bootsname: ""
    });
    setIsDialogOpen(true);
  };
  
  const handleEdit = (row: Mieter) => {
    setEditingMieter({...row});
    setIsDialogOpen(true);
  };
  
  const handleDelete = (row: Mieter) => {
    // Hier würde ein API-Aufruf stattfinden
    setMieter(mieter.filter(m => m.id !== row.id));
    toast({
      title: "Mieter gelöscht",
      description: `${row.vorname} ${row.nachname} wurde erfolgreich gelöscht.`
    });
  };
  
  const handleSave = () => {
    if (!editingMieter) return;
    
    // Validierung
    if (!editingMieter.vorname || !editingMieter.nachname || !editingMieter.email) {
      toast({
        variant: "destructive",
        title: "Fehler",
        description: "Bitte füllen Sie alle Pflichtfelder aus."
      });
      return;
    }
    
    // Hier würde ein API-Aufruf stattfinden
    if (editingMieter.id) {
      // Update
      setMieter(mieter.map(m => m.id === editingMieter.id ? editingMieter : m));
      toast({
        title: "Mieter aktualisiert",
        description: `${editingMieter.vorname} ${editingMieter.nachname} wurde erfolgreich aktualisiert.`
      });
    } else {
      // Create
      const newMieter = {
        ...editingMieter,
        id: Math.max(...mieter.map(m => m.id || 0)) + 1
      };
      setMieter([...mieter, newMieter]);
      toast({
        title: "Mieter erstellt",
        description: `${newMieter.vorname} ${newMieter.nachname} wurde erfolgreich erstellt.`
      });
    }
    
    setIsDialogOpen(false);
    setEditingMieter(null);
  };
  
  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Mieter verwalten</h1>
        
        <DataTable
          data={mieter}
          columns={columns}
          onAdd={handleAdd}
          onEdit={handleEdit}
          onDelete={handleDelete}
          searchable
        />
        
        <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
          <DialogContent className="sm:max-w-[550px]">
            <DialogHeader>
              <DialogTitle>
                {editingMieter?.id ? 'Mieter bearbeiten' : 'Neuen Mieter anlegen'}
              </DialogTitle>
            </DialogHeader>
            
            <div className="grid gap-4 py-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="vorname">Vorname *</Label>
                  <Input
                    id="vorname"
                    value={editingMieter?.vorname || ''}
                    onChange={(e) => setEditingMieter({...editingMieter!, vorname: e.target.value})}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="nachname">Nachname *</Label>
                  <Input
                    id="nachname"
                    value={editingMieter?.nachname || ''}
                    onChange={(e) => setEditingMieter({...editingMieter!, nachname: e.target.value})}
                  />
                </div>
              </div>
              
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="strasse">Straße</Label>
                  <Input
                    id="strasse"
                    value={editingMieter?.strasse || ''}
                    onChange={(e) => setEditingMieter({...editingMieter!, strasse: e.target.value})}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="hausnummer">Hausnummer</Label>
                  <Input
                    id="hausnummer"
                    value={editingMieter?.hausnummer || ''}
                    onChange={(e) => setEditingMieter({...editingMieter!, hausnummer: e.target.value})}
                  />
                </div>
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="email">E-Mail *</Label>
                <Input
                  id="email"
                  type="email"
                  value={editingMieter?.email || ''}
                  onChange={(e) => setEditingMieter({...editingMieter!, email: e.target.value})}
                />
              </div>
              
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="telefon">Telefon</Label>
                  <Input
                    id="telefon"
                    value={editingMieter?.telefon || ''}
                    onChange={(e) => setEditingMieter({...editingMieter!, telefon: e.target.value})}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="mobil">Mobil</Label>
                  <Input
                    id="mobil"
                    value={editingMieter?.mobil || ''}
                    onChange={(e) => setEditingMieter({...editingMieter!, mobil: e.target.value})}
                  />
                </div>
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="bootsname">Bootsname</Label>
                <Input
                  id="bootsname"
                  value={editingMieter?.bootsname || ''}
                  onChange={(e) => setEditingMieter({...editingMieter!, bootsname: e.target.value})}
                />
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="hinweis">Hinweis</Label>
                <Textarea
                  id="hinweis"
                  value={editingMieter?.hinweis || ''}
                  onChange={(e) => setEditingMieter({...editingMieter!, hinweis: e.target.value})}
                />
              </div>
            </div>
            
            <div className="flex justify-end space-x-4">
              <Button variant="outline" onClick={() => setIsDialogOpen(false)}>
                Abbrechen
              </Button>
              <Button className="bg-marina-600 hover:bg-marina-700" onClick={handleSave}>
                Speichern
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </main>
    </div>
  );
};

export default MieterPage;
