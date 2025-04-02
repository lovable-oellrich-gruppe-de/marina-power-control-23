
import { useState } from "react";
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { MieterForm } from "@/components/mieter/MieterForm";
import { getMieterColumns } from "@/components/mieter/MieterColumns";
import { useMieter } from "@/hooks/useMieter";
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
import { Mieter } from "@/types";

const MieterPage = () => {
  const {
    mieter,
    editingMieter,
    isDialogOpen,
    setEditingMieter,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  } = useMieter();
  
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [mieterToDelete, setMieterToDelete] = useState<Mieter | null>(null);
  
  const columns = getMieterColumns();
  
  const confirmDelete = (mieter: Mieter) => {
    setMieterToDelete(mieter);
    setDeleteDialogOpen(true);
  };
  
  const handleConfirmDelete = () => {
    if (mieterToDelete) {
      handleDelete(mieterToDelete);
    }
    setDeleteDialogOpen(false);
    setMieterToDelete(null);
  };
  
  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Mieter verwalten</h1>
        
        <DataTable
          data={mieter}
          columns={columns}
          onAdd={handleAdd}
          onEdit={handleEdit}
          onDelete={confirmDelete}
          searchable
        />
        
        <MieterForm
          mieter={editingMieter as Mieter}
          isOpen={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSave={handleSave}
          onMieterChange={setEditingMieter}
        />
        
        <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Mieter löschen</AlertDialogTitle>
              <AlertDialogDescription>
                Möchten Sie den Mieter "{mieterToDelete?.vorname} {mieterToDelete?.nachname}" wirklich löschen?
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

export default MieterPage;
