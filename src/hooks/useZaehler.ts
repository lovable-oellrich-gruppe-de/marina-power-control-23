import { useState, useEffect } from 'react';
import { Zaehler } from '@/types';
import { useToast } from '@/hooks/use-toast';

// Mock-Daten für Zähler
const initialZaehler: Zaehler[] = [
  { 
    id: 1, 
    zaehlernummer: 'Z-001', 
    installiertAm: '2023-01-15', 
    letzteWartung: '2023-12-01',
    notes: 'Neu installiert',
    istAusgebaut: false,
    hinweis: ''
  },
  { 
    id: 2, 
    zaehlernummer: 'Z-002', 
    installiertAm: '2023-02-20', 
    letzteWartung: '2023-11-15',
    notes: '',
    istAusgebaut: false,
    hinweis: ''
  },
  { 
    id: 3, 
    zaehlernummer: 'Z-003', 
    installiertAm: '2023-03-10', 
    letzteWartung: '2023-10-30',
    notes: 'Bald zur Wartung',
    istAusgebaut: false,
    hinweis: ''
  }
];

export function useZaehler() {
  const [zaehler, setZaehler] = useState<Zaehler[]>(initialZaehler);
  const [editingZaehler, setEditingZaehler] = useState<Partial<Zaehler>>({});
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const { toast } = useToast();

  // Zähler laden
  useEffect(() => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    // Die setTimeout ist nur zur Simulation
    const timeout = setTimeout(() => {
      setZaehler(initialZaehler);
    }, 500);

    return () => clearTimeout(timeout);
  }, []);

  // Neuen Zähler hinzufügen
  const handleAdd = () => {
    setEditingZaehler({});
    setIsDialogOpen(true);
  };

  // Zähler bearbeiten
  const handleEdit = (zaehler: Zaehler) => {
    setEditingZaehler({ ...zaehler });
    setIsDialogOpen(true);
  };

  // Zähler löschen
  const handleDelete = (zaehler: Zaehler) => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    setZaehler(prevZaehler => prevZaehler.filter(z => z.id !== zaehler.id));
    
    toast({
      title: "Zähler gelöscht",
      description: `Zähler "${zaehler.zaehlernummer}" wurde erfolgreich gelöscht.`,
    });
  };

  // Zähler speichern (neu oder aktualisiert)
  const handleSave = (zaehler: Zaehler) => {
    if (zaehler.id) {
      // Zähler aktualisieren
      setZaehler(prevZaehler => prevZaehler.map(z => z.id === zaehler.id ? zaehler : z));
      
      toast({
        title: "Zähler aktualisiert",
        description: `Zähler "${zaehler.zaehlernummer}" wurde erfolgreich aktualisiert.`,
      });
    } else {
      // Neuen Zähler hinzufügen
      const newZaehler = {
        ...zaehler,
        id: Math.max(0, ...zaehler.map(z => z.id)) + 1
      };
      
      setZaehler(prevZaehler => [...prevZaehler, newZaehler]);
      
      toast({
        title: "Zähler erstellt",
        description: `Zähler "${newZaehler.zaehlernummer}" wurde erfolgreich erstellt.`,
      });
    }
    
    setIsDialogOpen(false);
    setEditingZaehler({});
  };

  return {
    zaehler,
    editingZaehler,
    isDialogOpen,
    setEditingZaehler,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  };
}
