
import { useState } from "react";
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { useSteckdose } from "@/hooks/useSteckdose";
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
import { Steckdose } from "@/types";
import { SteckdosenForm } from "@/components/steckdosen/SteckdosenForm";
import { Column } from "@/components/common/DataTable";

// Steckdosen-Spalten für die Datentabelle
const getSteckdosenColumns = (): Column<Steckdose>[] => [
  { 
    header: "Nummer", 
    accessorKey: "nummer" 
  },
  { 
    header: "Bereich", 
    accessorKey: (row: Steckdose) => row.bereich?.name || "-" 
  },
  { 
    header: "Status", 
    accessorKey: (row: Steckdose) => row.vergeben ? "Vergeben" : "Frei" 
  },
  { 
    header: "Mieter", 
    accessorKey: (row: Steckdose) => 
      row.mieter ? `${row.mieter.vorname} ${row.mieter.nachname}` : "-" 
  },
  { 
    header: "Zähler", 
    accessorKey: (row: Steckdose) => row.zaehler?.zaehlernummer || "-" 
  },
  { 
    header: "Schlüssel-Nr.", 
    accessorKey: "schluesselnummer" 
  },
  { 
    header: "Hinweis", 
    accessorKey: "hinweis" 
  }
];

const SteckdosenPage = () => {
  const {
    steckdosen,
    editingSteckdose,
    isDialogOpen,
    setEditingSteckdose,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  } = useSteckdose();
  
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [steckdoseToDelete, setSteckdoseToDelete] = useState<Steckdose | null>(null);
  
  const columns = getSteckdosenColumns();
  
  const confirmDelete = (steckdose: Steckdose) => {
    setSteckdoseToDelete(steckdose);
    setDeleteDialogOpen(true);
  };
  
  const handleConfirmDelete = () => {
    if (steckdoseToDelete) {
      handleDelete(steckdoseToDelete);
    }
    setDeleteDialogOpen(false);
    setSteckdoseToDelete(null);
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
          onDelete={confirmDelete}
          searchable
        />
        
        <SteckdosenForm
          open={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSubmit={handleSave}
          initialData={editingSteckdose}
        />
        
        <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Steckdose löschen</AlertDialogTitle>
              <AlertDialogDescription>
                Möchten Sie die Steckdose "{steckdoseToDelete?.nummer}" wirklich löschen?
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

export default SteckdosenPage;
