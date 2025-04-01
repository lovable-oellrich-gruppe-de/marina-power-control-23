
import { useState } from "react";
import { useToast } from "@/components/ui/use-toast";
import { Mieter } from "@/types";

// This is mock data that would normally come from an API
const initialMieter: Mieter[] = [
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
];

export function useMieter() {
  const { toast } = useToast();
  const [mieter, setMieter] = useState<Mieter[]>(initialMieter);
  const [editingMieter, setEditingMieter] = useState<Mieter | null>(null);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  
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

  return {
    mieter,
    editingMieter,
    isDialogOpen,
    setEditingMieter,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  };
}
