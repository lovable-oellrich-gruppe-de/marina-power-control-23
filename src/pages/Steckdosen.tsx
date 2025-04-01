
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { SteckdosenForm } from "@/components/steckdosen/SteckdosenForm";
import { Steckdose } from "@/types";

const SteckdosenPage = () => {
  const [steckdosen, setSteckdosen] = useState<Steckdose[]>([
    {
      id: 1,
      nummer: "S-001",
      vergeben: true,
      mieterId: 1,
      zaehlerId: 1,
      bereichId: 1,
      hinweis: "Funktionstüchtig",
      schluesselnummer: "K-123"
    },
    {
      id: 2,
      nummer: "S-002",
      vergeben: false,
      mieterId: null,
      zaehlerId: 2,
      bereichId: 1,
      hinweis: "",
      schluesselnummer: "K-124"
    }
  ]);
  
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [currentSteckdose, setCurrentSteckdose] = useState<Steckdose | undefined>(undefined);
  const { toast } = useToast();
  
  const columns = [
    { header: "ID", accessorKey: "id" as keyof Steckdose },
    { header: "Nummer", accessorKey: "nummer" as keyof Steckdose },
    { 
      header: "Status", 
      accessorKey: (row: Steckdose) => row.vergeben ? "Vergeben" : "Verfügbar"
    },
    { header: "Schlüsselnummer", accessorKey: "schluesselnummer" as keyof Steckdose }
  ];
  
  const handleAdd = () => {
    setCurrentSteckdose(undefined);
    setIsFormOpen(true);
  };
  
  const handleEdit = (steckdose: Steckdose) => {
    setCurrentSteckdose(steckdose);
    setIsFormOpen(true);
  };
  
  const handleDelete = (steckdose: Steckdose) => {
    setSteckdosen(prev => prev.filter(item => item.id !== steckdose.id));
    
    toast({
      title: "Steckdose gelöscht",
      description: `Steckdose ${steckdose.nummer} wurde erfolgreich gelöscht.`,
    });
  };
  
  const handleFormSubmit = (data: Partial<Steckdose>) => {
    if (currentSteckdose) {
      // Bearbeiten einer vorhandenen Steckdose
      setSteckdosen(prev =>
        prev.map(item =>
          item.id === currentSteckdose.id
            ? { ...item, ...data }
            : item
        )
      );
      
      toast({
        title: "Steckdose aktualisiert",
        description: `Steckdose ${data.nummer} wurde erfolgreich aktualisiert.`,
      });
    } else {
      // Hinzufügen einer neuen Steckdose
      const newSteckdose: Steckdose = {
        id: Math.max(0, ...steckdosen.map(s => s.id ?? 0)) + 1,
        nummer: data.nummer!,
        vergeben: data.vergeben ?? false,
        schluesselnummer: data.schluesselnummer || "",
        hinweis: data.hinweis || "",
        mieterId: null,
        zaehlerId: null,
        bereichId: null,
      };
      
      setSteckdosen(prev => [...prev, newSteckdose]);
      
      toast({
        title: "Steckdose hinzugefügt",
        description: `Steckdose ${data.nummer} wurde erfolgreich hinzugefügt.`,
      });
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Steckdosen verwalten</h1>
        
        <DataTable
          data={steckdosen}
          columns={columns}
          onAdd={handleAdd}
          onEdit={handleEdit}
          onDelete={handleDelete}
          searchable
        />
        
        <SteckdosenForm
          open={isFormOpen}
          onOpenChange={setIsFormOpen}
          onSubmit={handleFormSubmit}
          initialData={currentSteckdose}
        />
      </main>
    </div>
  );
};

export default SteckdosenPage;
