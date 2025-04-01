
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { Zaehler } from "@/types";

export const useZaehler = () => {
  // Beispiel-/Testdaten für Zähler
  const [zaehler, setZaehler] = useState<Zaehler[]>([
    { 
      id: 1, 
      zaehlernummer: "Z-001", 
      installiertAm: "2023-01-15", 
      letzteWartung: "2023-12-01", 
      hinweis: "Neu installiert" 
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
      hinweis: "Gereinigt bei letzter Wartung",
      istAusgebaut: true
    }
  ]);

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingZaehler, setEditingZaehler] = useState<Zaehler | undefined>(undefined);
  const { toast } = useToast();

  const handleAdd = () => {
    setEditingZaehler({
      zaehlernummer: "",
      installiertAm: new Date().toISOString().split('T')[0],
      letzteWartung: new Date().toISOString().split('T')[0],
      hinweis: "",
      istAusgebaut: false
    });
    setIsDialogOpen(true);
  };

  const handleEdit = (zaehlerItem: Zaehler) => {
    setEditingZaehler(zaehlerItem);
    setIsDialogOpen(true);
  };

  const handleDelete = (zaehlerItem: Zaehler) => {
    setZaehler(current => current.filter(item => item.id !== zaehlerItem.id));
    toast({
      title: "Zähler gelöscht",
      description: `Zähler ${zaehlerItem.zaehlernummer} wurde erfolgreich gelöscht.`
    });
  };

  const handleSave = (data: Zaehler) => {
    if (editingZaehler?.id) {
      // Bearbeiten eines vorhandenen Zählers
      setZaehler(current =>
        current.map(item =>
          item.id === editingZaehler.id ? { ...data, id: item.id } : item
        )
      );
      toast({
        title: "Zähler aktualisiert",
        description: `Zähler ${data.zaehlernummer} wurde erfolgreich aktualisiert.`
      });
    } else {
      // Hinzufügen eines neuen Zählers
      const newId = Math.max(0, ...zaehler.map(z => z.id || 0)) + 1;
      setZaehler(current => [...current, { ...data, id: newId }]);
      toast({
        title: "Zähler hinzugefügt",
        description: `Zähler ${data.zaehlernummer} wurde erfolgreich hinzugefügt.`
      });
    }
    setIsDialogOpen(false);
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
};
