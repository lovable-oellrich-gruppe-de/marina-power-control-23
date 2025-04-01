
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";

// This is a placeholder until we implement the actual data fetching
interface Steckdose {
  id: string;
  nummer: string;
  vergeben: boolean;
  mieterId: string | null;
  zaehlerId: string | null;
  bereichId: string | null;
  hinweis: string;
  schluesselnummer: string;
}

const SteckdosenPage = () => {
  const [steckdosen, setSteckdosen] = useState<Steckdose[]>([
    {
      id: "1",
      nummer: "S-001",
      vergeben: true,
      mieterId: "1",
      zaehlerId: "1",
      bereichId: "1",
      hinweis: "Funktionstüchtig",
      schluesselnummer: "K-123"
    },
    {
      id: "2",
      nummer: "S-002",
      vergeben: false,
      mieterId: null,
      zaehlerId: "2",
      bereichId: "1",
      hinweis: "",
      schluesselnummer: "K-124"
    }
  ]);
  
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
    toast({
      title: "Funktion noch nicht implementiert",
      description: "Die Steckdosen-Verwaltung wird in Kürze vervollständigt.",
    });
  };
  
  const handleEdit = (steckdose: Steckdose) => {
    toast({
      title: "Funktion noch nicht implementiert",
      description: "Die Steckdosen-Bearbeitung wird in Kürze vervollständigt.",
    });
  };
  
  const handleDelete = (steckdose: Steckdose) => {
    toast({
      title: "Funktion noch nicht implementiert",
      description: "Das Löschen von Steckdosen wird in Kürze vervollständigt.",
    });
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
      </main>
    </div>
  );
};

export default SteckdosenPage;
