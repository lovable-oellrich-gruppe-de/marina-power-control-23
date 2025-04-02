
import { useState } from "react";
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { useZaehlerstand } from "@/hooks/useZaehlerstand";
import { 
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle
} from "@/components/ui/alert-dialog";
import { Zaehlerstand } from "@/types";
import { ZaehlerstandForm } from "@/components/zaehlerstand/ZaehlerstandForm";
import { formatDateString } from "@/lib/dateUtils";
import { Column } from "@/components/common/DataTable";

// Zählerstände-Spalten für die Datentabelle
const getZaehlerstaendeColumns = (): Column<Zaehlerstand>[] => [
  { 
    header: "Zähler", 
    accessorKey: (row: Zaehlerstand) => row.zaehler?.zaehlernummer || "-" 
  },
  { 
    header: "Steckdose", 
    accessorKey: (row: Zaehlerstand) => row.steckdose?.nummer || "-" 
  },
  { 
    header: "Datum", 
    accessorKey: (row: Zaehlerstand) => formatDateString(row.datum) 
  },
  { 
    header: "Stand (kWh)", 
    accessorKey: "stand" 
  },
  { 
    header: "Verbrauch (kWh)", 
    accessorKey: (row: Zaehlerstand) => row.verbrauch?.toFixed(2) || "-" 
  },
  { 
    header: "Abgelesen von", 
    accessorKey: (row: Zaehlerstand) => row.abgelesenVon?.name || "-" 
  },
  { 
    header: "Status", 
    accessorKey: (row: Zaehlerstand) => row.istAbgerechnet ? "Abgerechnet" : "Offen" 
  },
  { 
    header: "Hinweis", 
    accessorKey: "hinweis" 
  }
];

const ZaehlerstaendePage = () => {
  const {
    zaehlerstaende,
    editingZaehlerstand,
    isDialogOpen,
    setEditingZaehlerstand,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  } = useZaehlerstand();
  
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [zaehlerstandToDelete, setZaehlerstandToDelete] = useState<Zaehlerstand | null>(null);
  
  const columns = getZaehlerstaendeColumns();
  
  const confirmDelete = (zaehlerstand: Zaehlerstand) => {
    setZaehlerstandToDelete(zaehlerstand);
    setDeleteDialogOpen(true);
  };
  
  const handleConfirmDelete = () => {
    if (zaehlerstandToDelete) {
      handleDelete(zaehlerstandToDelete);
    }
    setDeleteDialogOpen(false);
    setZaehlerstandToDelete(null);
  };
  
  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Zählerstände verwalten</h1>
        
        <DataTable
          data={zaehlerstaende}
          columns={columns}
          onAdd={handleAdd}
          onEdit={handleEdit}
          onDelete={confirmDelete}
          searchable
        />
        
        <ZaehlerstandForm
          open={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSubmit={handleSave}
          initialData={editingZaehlerstand}
        />
        
        <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Zählerstand löschen</AlertDialogTitle>
              <AlertDialogDescription>
                Möchten Sie diesen Zählerstand wirklich löschen?
                Dieser Vorgang kann nicht rückgängig gemacht werden.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Abbrechen</AlertDialogCancel>
              <AlertDialogAction onClick={handleConfirmDelete} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                Löschen
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </main>
    </div>
  );
};

export default ZaehlerstaendePage;
