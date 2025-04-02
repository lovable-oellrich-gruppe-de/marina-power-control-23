
import { useState, useEffect } from 'react';
import { Bereich } from '@/types';
import { useToast } from '@/hooks/use-toast';

// Mock-Daten für Bereiche
const initialBereiche: Bereich[] = [
  { id: 1, name: 'Hauptsteg', beschreibung: 'Zentraler Steg mit 20 Anschlüssen' },
  { id: 2, name: 'Nordsteg', beschreibung: 'Nördlicher Steg mit 15 Anschlüssen' },
  { id: 3, name: 'Südsteg', beschreibung: 'Südlicher Steg mit 18 Anschlüssen' },
];

export function useBereich() {
  const [bereiche, setBereiche] = useState<Bereich[]>(initialBereiche);
  const [editingBereich, setEditingBereich] = useState<Partial<Bereich>>({});
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const { toast } = useToast();

  // Bereiche laden
  useEffect(() => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    // Die setTimeout ist nur zur Simulation
    const timeout = setTimeout(() => {
      setBereiche(initialBereiche);
    }, 500);

    return () => clearTimeout(timeout);
  }, []);

  // Neuen Bereich hinzufügen
  const handleAdd = () => {
    setEditingBereich({});
    setIsDialogOpen(true);
  };

  // Bereich bearbeiten
  const handleEdit = (bereich: Bereich) => {
    setEditingBereich({ ...bereich });
    setIsDialogOpen(true);
  };

  // Bereich löschen
  const handleDelete = (bereich: Bereich) => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    setBereiche(bereiche.filter(b => b.id !== bereich.id));
    
    toast({
      title: "Bereich gelöscht",
      description: `Bereich "${bereich.name}" wurde erfolgreich gelöscht.`,
    });
  };

  // Bereich speichern (neu oder aktualisiert)
  const handleSave = (bereich: Bereich) => {
    if (bereich.id) {
      // Bereich aktualisieren
      setBereiche(bereiche.map(b => b.id === bereich.id ? bereich : b));
      
      toast({
        title: "Bereich aktualisiert",
        description: `Bereich "${bereich.name}" wurde erfolgreich aktualisiert.`,
      });
    } else {
      // Neuen Bereich hinzufügen
      const newBereich = {
        ...bereich,
        id: Math.max(0, ...bereiche.map(b => b.id || 0)) + 1
      };
      
      setBereiche([...bereiche, newBereich]);
      
      toast({
        title: "Bereich erstellt",
        description: `Bereich "${newBereich.name}" wurde erfolgreich erstellt.`,
      });
    }
    
    setIsDialogOpen(false);
    setEditingBereich({});
  };

  return {
    bereiche,
    editingBereich,
    isDialogOpen,
    setEditingBereich,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  };
}
