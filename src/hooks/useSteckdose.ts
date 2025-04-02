
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { Steckdose } from "@/types";

export const useSteckdose = () => {
  // Beispiel-/Testdaten für Steckdosen
  const [steckdosen, setSteckdosen] = useState<Steckdose[]>([
    { 
      id: 1, 
      nummer: "A-01", 
      vergeben: true, 
      mieterId: 1, 
      zaehlerId: 1, 
      bereichId: 1, 
      hinweis: "Nahe am Hauptsteg",
      schluesselnummer: "S-001"
    },
    { 
      id: 2, 
      nummer: "A-02", 
      vergeben: false, 
      mieterId: null, 
      zaehlerId: 2, 
      bereichId: 1, 
      hinweis: "",
      schluesselnummer: "S-002"
    },
    { 
      id: 3, 
      nummer: "B-01", 
      vergeben: true, 
      mieterId: 2, 
      zaehlerId: null, 
      bereichId: 2, 
      hinweis: "Wartung geplant",
      schluesselnummer: "S-003"
    }
  ]);

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingSteckdose, setEditingSteckdose] = useState<Steckdose | undefined>(undefined);
  const { toast } = useToast();

  const handleAdd = () => {
    setEditingSteckdose({
      nummer: "",
      vergeben: false,
      mieterId: null,
      zaehlerId: null,
      bereichId: null,
      hinweis: "",
      schluesselnummer: ""
    });
    setIsDialogOpen(true);
  };

  const handleEdit = (steckdose: Steckdose) => {
    setEditingSteckdose(steckdose);
    setIsDialogOpen(true);
  };

  const handleDelete = (steckdose: Steckdose) => {
    setSteckdosen(current => current.filter(item => item.id !== steckdose.id));
    toast({
      title: "Steckdose gelöscht",
      description: `Steckdose ${steckdose.nummer} wurde erfolgreich gelöscht.`
    });
  };

  const handleSave = (data: Steckdose) => {
    if (editingSteckdose?.id) {
      // Bearbeiten einer vorhandenen Steckdose
      setSteckdosen(current =>
        current.map(item =>
          item.id === editingSteckdose.id ? { ...data, id: item.id } : item
        )
      );
      toast({
        title: "Steckdose aktualisiert",
        description: `Steckdose ${data.nummer} wurde erfolgreich aktualisiert.`
      });
    } else {
      // Hinzufügen einer neuen Steckdose
      const newId = Math.max(0, ...steckdosen.map(z => z.id || 0)) + 1;
      setSteckdosen(current => [...current, { ...data, id: newId }]);
      toast({
        title: "Steckdose hinzugefügt",
        description: `Steckdose ${data.nummer} wurde erfolgreich hinzugefügt.`
      });
    }
    setIsDialogOpen(false);
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
};
