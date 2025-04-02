import { useState, useEffect } from 'react';
import { Mieter } from '@/types';
import { useToast } from '@/hooks/use-toast';

// Mock-Daten für Mieter
const initialMieter: Mieter[] = [
  { 
    id: 1, 
    vorname: 'Max', 
    nachname: 'Mustermann', 
    email: 'max@example.com',
    telefon: '0123456789',
    address: 'Musterstraße 1, 12345 Musterstadt',
    bootName: 'Sea Spirit',
    stellplatzNr: 'A-42',
    vertragStart: '2023-01-01',
    vertragEnde: '2023-12-31',
    notes: ''
  },
  { 
    id: 2, 
    vorname: 'Anna', 
    nachname: 'Schmidt', 
    email: 'anna@example.com',
    telefon: '0987654321',
    address: 'Beispielweg 7, 54321 Beispielstadt',
    bootName: 'Water Dream',
    stellplatzNr: 'B-17',
    vertragStart: '2023-03-01',
    vertragEnde: '2024-02-29',
    notes: 'Zahlt vierteljährlich'
  },
  { 
    id: 3, 
    vorname: 'Peter', 
    nachname: 'Müller', 
    email: 'peter@example.com',
    telefon: '01765432198',
    address: 'Teststraße 42, 98765 Testhausen',
    bootName: 'Wind Chaser',
    stellplatzNr: 'C-08',
    vertragStart: '2023-05-15',
    vertragEnde: '2023-10-15',
    notes: 'Saisonmieter'
  }
];

export function useMieter() {
  const [mieter, setMieter] = useState<Mieter[]>(initialMieter);
  const [editingMieter, setEditingMieter] = useState<Partial<Mieter>>({});
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const { toast } = useToast();

  // Mieter laden
  useEffect(() => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    // Die setTimeout ist nur zur Simulation
    const timeout = setTimeout(() => {
      setMieter(initialMieter);
    }, 500);

    return () => clearTimeout(timeout);
  }, []);

  // Neuen Mieter hinzufügen
  const handleAdd = () => {
    setEditingMieter({});
    setIsDialogOpen(true);
  };

  // Mieter bearbeiten
  const handleEdit = (mieter: Mieter) => {
    setEditingMieter({ ...mieter });
    setIsDialogOpen(true);
  };

  // Mieter löschen
  const handleDelete = (mieter: Mieter) => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    setMieter(prevMieter => prevMieter.filter(m => m.id !== mieter.id));
    
    toast({
      title: "Mieter gelöscht",
      description: `Mieter "${mieter.vorname} ${mieter.nachname}" wurde erfolgreich gelöscht.`,
    });
  };

  // Mieter speichern (neu oder aktualisiert)
  const handleSave = (mieter: Mieter) => {
    if (mieter.id) {
      // Mieter aktualisieren
      setMieter(prevMieter => prevMieter.map(m => m.id === mieter.id ? mieter : m));
      
      toast({
        title: "Mieter aktualisiert",
        description: `Mieter "${mieter.vorname} ${mieter.nachname}" wurde erfolgreich aktualisiert.`,
      });
    } else {
      // Neuen Mieter hinzufügen
      const newMieter = {
        ...mieter,
        id: Math.max(0, ...mieter.map(m => m.id || 0)) + 1
      };
      
      setMieter(prevMieter => [...prevMieter, newMieter]);
      
      toast({
        title: "Mieter erstellt",
        description: `Mieter "${newMieter.vorname} ${newMieter.nachname}" wurde erfolgreich erstellt.`,
      });
    }
    
    setIsDialogOpen(false);
    setEditingMieter({});
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
