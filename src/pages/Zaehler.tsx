
import { useState } from "react";
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { ZaehlerForm } from "@/components/zaehler/ZaehlerForm";
import { getZaehlerColumns } from "@/components/zaehler/ZaehlerColumns";
import { useZaehler } from "@/hooks/useZaehler";
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
import { Zaehler } from "@/types";

const ZaehlerPage = () => {
  const {
    zaehler,
    editingZaehler,
    isDialogOpen,
    setEditingZaehler,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  } = useZaehler();
  
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [zaehlerToDelete, setZaehlerToDelete] = useState<Zaehler | null>(null);
  
  const columns = getZaehlerColumns();
  
  const confirmDelete = (zaehler: Zaehler) => {
    setZaehlerToDelete(zaehler);
    setDeleteDialogOpen(true);
  };
  
  const handleConfirmDelete = () => {
    if (zaehlerToDelete) {
      handleDelete(zaehlerToDelete);
    }
    setDeleteDialogOpen(false);
    setZaehlerToDelete(null);
  };
  
  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Zähler verwalten</h1>
        
        <DataTable
          data={zaehler}
          columns={columns}
          onAdd={handleAdd}
          onEdit={handleEdit}
          onDelete={confirmDelete}
          searchable
        />
        
        <ZaehlerForm
          zaehler={editingZaehler as Zaehler}
          isOpen={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSave={handleSave}
          onZaehlerChange={setEditingZaehler}
        />
        
        <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Zähler löschen</AlertDialogTitle>
              <AlertDialogDescription>
                Möchten Sie den Zähler "{zaehlerToDelete?.zaehlernummer}" wirklich löschen?
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

export default ZaehlerPage;
