
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { Zaehlerstand, Zaehler } from "@/types";

export const useZaehlerstand = () => {
  // Beispiel-/Testdaten für Zählerstände
  const [zaehlerstaende, setZaehlerstaende] = useState<Zaehlerstand[]>([
    { 
      id: 1, 
      zaehlerId: 1, 
      zaehler: { 
        id: 1, 
        zaehlernummer: "Z-001", 
        installiertAm: "2023-01-15", 
        letzteWartung: "2023-12-01", 
        hinweis: "" 
      },
      datum: "2023-12-15", 
      foto: null, 
      kommentar: "Erstablesung", 
      stand: "0.00" 
    },
    { 
      id: 2, 
      zaehlerId: 1, 
      zaehler: { 
        id: 1, 
        zaehlernummer: "Z-001", 
        installiertAm: "2023-01-15", 
        letzteWartung: "2023-12-01", 
        hinweis: "" 
      },
      datum: "2024-01-15", 
      foto: "/placeholder.svg", 
      kommentar: "Regelmäßige Ablesung", 
      stand: "150.50" 
    },
    { 
      id: 3, 
      zaehlerId: 2, 
      zaehler: { 
        id: 2, 
        zaehlernummer: "Z-002", 
        installiertAm: "2023-02-20", 
        letzteWartung: "2023-11-15", 
        hinweis: "" 
      },
      datum: "2024-01-15", 
      foto: null, 
      kommentar: "", 
      stand: "75.25" 
    }
  ]);

  // Beispiel-/Testdaten für verfügbare Zähler
  const [availableZaehler, setAvailableZaehler] = useState<Zaehler[]>([
    { 
      id: 1, 
      zaehlernummer: "Z-001", 
      installiertAm: "2023-01-15", 
      letzteWartung: "2023-12-01", 
      hinweis: "" 
    },
    { 
      id: 2, 
      zaehlernummer: "Z-002", 
      installiertAm: "2023-02-20", 
      letzteWartung: "2023-11-15", 
      hinweis: "" 
    },
    { 
      id: 3, 
      zaehlernummer: "Z-003", 
      installiertAm: "2023-05-10", 
      letzteWartung: "2023-10-20", 
      hinweis: "",
      istAusgebaut: false
    }
  ]);

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingZaehlerstand, setEditingZaehlerstand] = useState<Zaehlerstand | undefined>(undefined);
  const { toast } = useToast();

  const handleAdd = () => {
    setEditingZaehlerstand({
      zaehlerId: availableZaehler[0]?.id || 0,
      datum: new Date().toISOString().split('T')[0],
      foto: null,
      kommentar: "",
      stand: "0.00"
    });
    setIsDialogOpen(true);
  };

  const handleEdit = (zaehlerstandItem: Zaehlerstand) => {
    setEditingZaehlerstand(zaehlerstandItem);
    setIsDialogOpen(true);
  };

  const handleDelete = (zaehlerstandItem: Zaehlerstand) => {
    setZaehlerstaende(current => current.filter(item => item.id !== zaehlerstandItem.id));
    toast({
      title: "Zählerstand gelöscht",
      description: `Zählerstand vom ${formatDateString(zaehlerstandItem.datum)} wurde erfolgreich gelöscht.`
    });
  };

  const handleSave = (data: Zaehlerstand) => {
    // Finde den ausgewählten Zähler
    const selectedZaehler = availableZaehler.find(z => z.id === data.zaehlerId);
    
    if (editingZaehlerstand?.id) {
      // Bearbeiten eines vorhandenen Zählerstands
      setZaehlerstaende(current =>
        current.map(item =>
          item.id === editingZaehlerstand.id 
            ? { ...data, id: item.id, zaehler: selectedZaehler } 
            : item
        )
      );
      toast({
        title: "Zählerstand aktualisiert",
        description: `Zählerstand für ${selectedZaehler?.zaehlernummer || ''} vom ${formatDateString(data.datum)} wurde erfolgreich aktualisiert.`
      });
    } else {
      // Hinzufügen eines neuen Zählerstands
      const newId = Math.max(0, ...zaehlerstaende.map(z => z.id || 0)) + 1;
      setZaehlerstaende(current => [
        ...current, 
        { ...data, id: newId, zaehler: selectedZaehler }
      ]);
      toast({
        title: "Zählerstand hinzugefügt",
        description: `Zählerstand für ${selectedZaehler?.zaehlernummer || ''} vom ${formatDateString(data.datum)} wurde erfolgreich hinzugefügt.`
      });
    }
    setIsDialogOpen(false);
  };

  // Hilfsfunktion zur Formatierung von Datumswerten im Toast
  const formatDateString = (dateString: string): string => {
    if (!dateString) return "-";
    
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
    } catch (error) {
      console.error("Fehler beim Formatieren des Datums:", error);
      return dateString;
    }
  };

  return {
    zaehlerstaende,
    availableZaehler,
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
