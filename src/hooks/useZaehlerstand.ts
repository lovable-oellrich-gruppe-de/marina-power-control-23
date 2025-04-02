
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { Zaehlerstand, User } from "@/types";
import { useAuth } from "@/hooks/useAuth";

export const useZaehlerstand = () => {
  // Beispiel-/Testdaten für Zählerstände
  const [zaehlerstaende, setZaehlerstaende] = useState<Zaehlerstand[]>([
    { 
      id: 1, 
      zaehlerId: 1, 
      steckdoseId: 1, 
      datum: "2023-12-15", 
      stand: 1250.5, 
      vorherigerId: null, 
      verbrauch: null, 
      abgelesenVonId: "user_1", 
      fotoUrl: null, 
      istAbgerechnet: false, 
      hinweis: "Erstablesung"
    },
    { 
      id: 2, 
      zaehlerId: 1, 
      steckdoseId: 1, 
      datum: "2024-01-15", 
      stand: 1320.8, 
      vorherigerId: 1, 
      verbrauch: 70.3, 
      abgelesenVonId: "user_1", 
      fotoUrl: null, 
      istAbgerechnet: false, 
      hinweis: ""
    },
    { 
      id: 3, 
      zaehlerId: 2, 
      steckdoseId: 2, 
      datum: "2024-01-10", 
      stand: 55.2, 
      vorherigerId: null, 
      verbrauch: null, 
      abgelesenVonId: "user_2", 
      fotoUrl: null, 
      istAbgerechnet: false, 
      hinweis: "Neuer Zähler"
    }
  ]);

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingZaehlerstand, setEditingZaehlerstand] = useState<Zaehlerstand | undefined>(undefined);
  const { toast } = useToast();
  const { user } = useAuth();

  const handleAdd = () => {
    const today = new Date().toISOString().split('T')[0];
    
    setEditingZaehlerstand({
      datum: today,
      stand: 0,
      zaehlerId: null,
      steckdoseId: null,
      vorherigerId: null,
      verbrauch: null,
      abgelesenVonId: user?.id || null,
      fotoUrl: null,
      istAbgerechnet: false,
      hinweis: ""
    });
    setIsDialogOpen(true);
  };

  const handleEdit = (zaehlerstand: Zaehlerstand) => {
    setEditingZaehlerstand(zaehlerstand);
    setIsDialogOpen(true);
  };

  const handleDelete = (zaehlerstand: Zaehlerstand) => {
    if (zaehlerstand.istAbgerechnet) {
      toast({
        title: "Fehler",
        description: "Abgerechnete Zählerstände können nicht gelöscht werden.",
        variant: "destructive"
      });
      return;
    }
    
    setZaehlerstaende(current => current.filter(item => item.id !== zaehlerstand.id));
    toast({
      title: "Zählerstand gelöscht",
      description: `Zählerstand mit ID ${zaehlerstand.id} wurde erfolgreich gelöscht.`
    });
  };

  const berechneDeltaZuVorherigem = (zaehlerstand: Zaehlerstand): number | null => {
    if (!zaehlerstand.vorherigerId) return null;
    
    const vorheriger = zaehlerstaende.find(z => z.id === zaehlerstand.vorherigerId);
    if (!vorheriger) return null;
    
    return zaehlerstand.stand - vorheriger.stand;
  };

  const handleSave = (data: Zaehlerstand) => {
    if (editingZaehlerstand?.id) {
      // Bearbeiten eines vorhandenen Zählerstands
      setZaehlerstaende(current =>
        current.map(item => {
          if (item.id === editingZaehlerstand.id) {
            const updatedItem = { ...data, id: item.id };
            // Verbrauch neu berechnen
            updatedItem.verbrauch = berechneDeltaZuVorherigem(updatedItem);
            return updatedItem;
          }
          return item;
        })
      );
      toast({
        title: "Zählerstand aktualisiert",
        description: `Zählerstand für Datum ${data.datum} wurde erfolgreich aktualisiert.`
      });
    } else {
      // Hinzufügen eines neuen Zählerstands
      const newId = Math.max(0, ...zaehlerstaende.map(z => z.id || 0)) + 1;
      
      // Vorherigen Zählerstand finden (neuester Zählerstand für denselben Zähler)
      let vorherigerId = null;
      let verbrauch = null;
      
      if (data.zaehlerId) {
        const zaehlerstaendeDesZaehlers = zaehlerstaende
          .filter(z => z.zaehlerId === data.zaehlerId)
          .sort((a, b) => new Date(b.datum).getTime() - new Date(a.datum).getTime());
        
        if (zaehlerstaendeDesZaehlers.length > 0) {
          vorherigerId = zaehlerstaendeDesZaehlers[0].id;
          verbrauch = data.stand - zaehlerstaendeDesZaehlers[0].stand;
        }
      }
      
      const newZaehlerstand = { 
        ...data, 
        id: newId, 
        vorherigerId, 
        verbrauch 
      };
      
      setZaehlerstaende(current => [...current, newZaehlerstand]);
      toast({
        title: "Zählerstand hinzugefügt",
        description: `Neuer Zählerstand für Datum ${data.datum} wurde erfolgreich hinzugefügt.`
      });
    }
    setIsDialogOpen(false);
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
};
