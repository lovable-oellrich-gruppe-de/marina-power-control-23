
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { Bereich } from "@/types";

export const useBereich = () => {
  // Example/test data for areas
  const [bereiche, setBereiche] = useState<Bereich[]>([
    { id: 1, name: "Steg A" },
    { id: 2, name: "Steg B" },
    { id: 3, name: "Steg C" },
    { id: 4, name: "Nordbereich" },
    { id: 5, name: "Südbereich" }
  ]);

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingBereich, setEditingBereich] = useState<Bereich | undefined>(undefined);
  const { toast } = useToast();

  const handleAdd = () => {
    setEditingBereich({
      name: ""
    });
    setIsDialogOpen(true);
  };

  const handleEdit = (bereichItem: Bereich) => {
    setEditingBereich(bereichItem);
    setIsDialogOpen(true);
  };

  const handleDelete = (bereichItem: Bereich) => {
    setBereiche(current => current.filter(item => item.id !== bereichItem.id));
    toast({
      title: "Bereich gelöscht",
      description: `Bereich ${bereichItem.name} wurde erfolgreich gelöscht.`
    });
  };

  const handleSave = (data: Bereich) => {
    if (editingBereich?.id) {
      // Edit existing area
      setBereiche(current =>
        current.map(item =>
          item.id === editingBereich.id ? { ...data, id: item.id } : item
        )
      );
      toast({
        title: "Bereich aktualisiert",
        description: `Bereich ${data.name} wurde erfolgreich aktualisiert.`
      });
    } else {
      // Add new area
      const newId = Math.max(0, ...bereiche.map(b => b.id || 0)) + 1;
      setBereiche(current => [...current, { ...data, id: newId }]);
      toast({
        title: "Bereich hinzugefügt",
        description: `Bereich ${data.name} wurde erfolgreich hinzugefügt.`
      });
    }
    setIsDialogOpen(false);
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
};
