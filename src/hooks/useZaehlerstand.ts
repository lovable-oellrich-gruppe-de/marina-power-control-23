
import { useState, useEffect } from 'react';
import { Zaehlerstand } from '@/types';
import { useToast } from '@/hooks/use-toast';

// Mock-Daten für Zählerstände
const initialZaehlerstaende: Zaehlerstand[] = [
  { 
    id: 1, 
    zaehlerId: 1, 
    steckdoseId: 1,
    datum: '2023-11-01',
    stand: 1250.5,
    vorherigerId: null,
    verbrauch: null,
    abgelesenVonId: 'admin1',
    fotoUrl: null,
    istAbgerechnet: true,
    hinweis: 'Erste Ablesung'
  },
  { 
    id: 2, 
    zaehlerId: 1, 
    steckdoseId: 1,
    datum: '2023-12-01',
    stand: 1320.8,
    vorherigerId: 1,
    verbrauch: 70.3,
    abgelesenVonId: 'admin1',
    fotoUrl: null,
    istAbgerechnet: true,
    hinweis: ''
  },
  { 
    id: 3, 
    zaehlerId: 1, 
    steckdoseId: 1,
    datum: '2024-01-01',
    stand: 1390.2,
    vorherigerId: 2,
    verbrauch: 69.4,
    abgelesenVonId: 'admin1',
    fotoUrl: null,
    istAbgerechnet: false,
    hinweis: ''
  },
  { 
    id: 4, 
    zaehlerId: 2, 
    steckdoseId: 2,
    datum: '2023-12-15',
    stand: 450.0,
    vorherigerId: null,
    verbrauch: null,
    abgelesenVonId: 'user1',
    fotoUrl: null,
    istAbgerechnet: false,
    hinweis: 'Installationsablesung'
  },
];

export function useZaehlerstand() {
  const [zaehlerstaende, setZaehlerstaende] = useState<Zaehlerstand[]>(initialZaehlerstaende);
  const [editingZaehlerstand, setEditingZaehlerstand] = useState<Partial<Zaehlerstand>>({});
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const { toast } = useToast();

  // Zählerstände laden
  useEffect(() => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    // Die setTimeout ist nur zur Simulation
    const timeout = setTimeout(() => {
      setZaehlerstaende(initialZaehlerstaende);
    }, 500);

    return () => clearTimeout(timeout);
  }, []);

  // Neuen Zählerstand hinzufügen
  const handleAdd = () => {
    setEditingZaehlerstand({});
    setIsDialogOpen(true);
  };

  // Zählerstand bearbeiten
  const handleEdit = (zaehlerstand: Zaehlerstand) => {
    setEditingZaehlerstand({ ...zaehlerstand });
    setIsDialogOpen(true);
  };

  // Zählerstand löschen
  const handleDelete = (zaehlerstand: Zaehlerstand) => {
    // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
    setZaehlerstaende(prevZaehlerstaende => prevZaehlerstaende.filter(z => z.id !== zaehlerstand.id));
    
    toast({
      title: "Zählerstand gelöscht",
      description: `Zählerstand vom ${new Date(zaehlerstand.datum).toLocaleDateString()} wurde erfolgreich gelöscht.`,
    });
  };

  // Zählerstand speichern (neu oder aktualisiert)
  const handleSave = (zaehlerstand: Zaehlerstand) => {
    // Vorherigen Zählerstand finden für die Verbrauchsberechnung
    const vorherige = zaehlerstaende
      .filter(z => z.zaehlerId === zaehlerstand.zaehlerId)
      .sort((a, b) => new Date(b.datum).getTime() - new Date(a.datum).getTime())[0];
    
    let verbrauch = null;
    let vorherigerId = null;
    
    if (vorherige && zaehlerstand.id !== vorherige.id) {
      verbrauch = zaehlerstand.stand - vorherige.stand;
      vorherigerId = vorherige.id;
    }
    
    const updatedZaehlerstand = {
      ...zaehlerstand,
      verbrauch,
      vorherigerId
    };
    
    if (zaehlerstand.id) {
      // Zählerstand aktualisieren
      setZaehlerstaende(prevZaehlerstaende => 
        prevZaehlerstaende.map(z => z.id === zaehlerstand.id ? updatedZaehlerstand : z)
      );
      
      toast({
        title: "Zählerstand aktualisiert",
        description: `Zählerstand vom ${new Date(zaehlerstand.datum).toLocaleDateString()} wurde erfolgreich aktualisiert.`,
      });
    } else {
      // Neuen Zählerstand hinzufügen
      const newZaehlerstand = {
        ...updatedZaehlerstand,
        id: Math.max(0, ...zaehlerstaende.map(z => z.id || 0)) + 1
      };
      
      setZaehlerstaende(prevZaehlerstaende => [...prevZaehlerstaende, newZaehlerstand]);
      
      toast({
        title: "Zählerstand erfasst",
        description: `Zählerstand vom ${new Date(zaehlerstand.datum).toLocaleDateString()} wurde erfolgreich erfasst.`,
      });
    }
    
    setIsDialogOpen(false);
    setEditingZaehlerstand({});
  };

  return {
    zaehlerstaende,
    editingZaehlerstand,
    isDialogOpen,
    setEditingZaehlerstand,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  };
}
