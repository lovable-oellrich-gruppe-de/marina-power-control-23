
import { useState, useEffect } from 'react';
import { Steckdose } from '@/types';
import { useToast } from '@/hooks/use-toast';

// Mock-Daten für Steckdosen
const initialSteckdosen: Steckdose[] = [
  { 
    id: 1, 
    nummer: 'A-01', 
    vergeben: true, 
    mieterId: 1,
    zaehlerId: 1,
    bereichId: 1,
    schluesselnummer: 'S-001',
    hinweis: 'Nahe am Hauptsteg'
  },
  { 
    id: 2, 
    nummer: 'A-02', 
    vergeben: false, 
    mieterId: null,
    zaehlerId: 2,
    bereichId: 1,
    schluesselnummer: 'S-002',
    hinweis: ''
  },
  { 
    id: 3, 
    nummer: 'B-01', 
    vergeben: true, 
    mieterId: 2,
    zaehlerId: null,
    bereichId: 2,
    schluesselnummer: 'S-003',
    hinweis: 'Wartung geplant'
  }
];

export function useSteckdose() {
  const [steckdosen, setSteckdosen] = useState<Steckdose[]>(initialSteckdosen);
  const [editingSteckdose, setEditingSteckdose] = useState<Partial<Steckdose>>({});
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const { toast } = useToast();

  // Steckdosen laden
  useEffect(() => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    // Die setTimeout ist nur zur Simulation
    const timeout = setTimeout(() => {
      setSteckdosen(initialSteckdosen);
    }, 500);

    return () => clearTimeout(timeout);
  }, []);

  // Neue Steckdose hinzufügen
  const handleAdd = () => {
    setEditingSteckdose({});
    setIsDialogOpen(true);
  };

  // Steckdose bearbeiten
  const handleEdit = (steckdose: Steckdose) => {
    setEditingSteckdose({ ...steckdose });
    setIsDialogOpen(true);
  };

  // Steckdose löschen
  const handleDelete = (steckdose: Steckdose) => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    setSteckdosen(prevSteckdosen => prevSteckdosen.filter(s => s.id !== steckdose.id));
    
    toast({
      title: "Steckdose gelöscht",
      description: `Steckdose "${steckdose.nummer}" wurde erfolgreich gelöscht.`,
    });
  };

  // Steckdose speichern (neu oder aktualisiert)
  const handleSave = (steckdose: Steckdose) => {
    if (steckdose.id) {
      // Steckdose aktualisieren
      setSteckdosen(prevSteckdosen => prevSteckdosen.map(s => s.id === steckdose.id ? steckdose : s));
      
      toast({
        title: "Steckdose aktualisiert",
        description: `Steckdose "${steckdose.nummer}" wurde erfolgreich aktualisiert.`,
      });
    } else {
      // Neue Steckdose hinzufügen
      const newSteckdose = {
        ...steckdose,
        id: Math.max(0, ...steckdosen.map(s => s.id || 0)) + 1
      };
      
      setSteckdosen(prevSteckdosen => [...prevSteckdosen, newSteckdose]);
      
      toast({
        title: "Steckdose erstellt",
        description: `Steckdose "${newSteckdose.nummer}" wurde erfolgreich erstellt.`,
      });
    }
    
    setIsDialogOpen(false);
    setEditingSteckdose({});
  };

  return {
    steckdosen,
    editingSteckdose,
    isDialogOpen,
    setEditingSteckdose,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  };
}
