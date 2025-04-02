import { useState } from "react";
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { ZaehlerstandForm } from "@/components/zaehlerstand/ZaehlerstandForm";
import { getZaehlerstandColumns } from "@/components/zaehlerstand/ZaehlerstandColumns";
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
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog";
import { Column } from "@/components/common/DataTable";

const ZaehlerstaendePage = () => {
  const {
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
  } = useZaehlerstand();
  
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [zaehlerstandToDelete, setZaehlerstandToDelete] = useState<Zaehlerstand | null>(null);
  const [photoDialogOpen, setPhotoDialogOpen] = useState(false);
  const [currentPhoto, setCurrentPhoto] = useState<string | null>(null);
  
  const columns = getZaehlerstandColumns();
  
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
  
  const openPhotoDialog = (photoUrl: string) => {
    setCurrentPhoto(photoUrl);
    setPhotoDialogOpen(true);
  };
  
  const enhancedColumns: Column<Zaehlerstand>[] = [
    ...columns.slice(0, -1),
    {
      header: "Foto",
      accessorKey: (row: Zaehlerstand) => row.foto || "none",
      cell: (row: Zaehlerstand) => {
        if (!row.foto) return "Nicht vorhanden";
        return (
          <button 
            onClick={() => openPhotoDialog(row.foto as string)}
            className="text-marina-600 hover:text-marina-800 underline"
          >
            Anzeigen
          </button>
        );
      }
    }
  ];
  
  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Zählerstände verwalten</h1>
        
        <DataTable
          data={zaehlerstaende}
          columns={enhancedColumns}
          onAdd={handleAdd}
          onEdit={handleEdit}
          onDelete={confirmDelete}
          searchable
        />
        
        <ZaehlerstandForm
          zaehlerstand={editingZaehlerstand}
          isOpen={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSave={handleSave}
          onZaehlerstandChange={setEditingZaehlerstand}
          availableZaehler={availableZaehler}
        />
        
        <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Zählerstand löschen</AlertDialogTitle>
              <AlertDialogDescription>
                Möchten Sie den Zählerstand vom {zaehlerstandToDelete?.datum} für Zähler 
                {zaehlerstandToDelete?.zaehler?.zaehlernummer ? ` "${zaehlerstandToDelete.zaehler.zaehlernummer}"` : ""} 
                wirklich löschen?
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
        
        <Dialog open={photoDialogOpen} onOpenChange={setPhotoDialogOpen}>
          <DialogContent className="sm:max-w-[600px] flex flex-col items-center justify-center">
            <DialogHeader>
              <DialogTitle>Zählerstand Foto</DialogTitle>
              <DialogDescription>
                Foto vom Zählerstand in Vollansicht
              </DialogDescription>
            </DialogHeader>
            
            {currentPhoto && (
              <div className="w-full max-h-[70vh] overflow-auto mt-2">
                <img 
                  src={currentPhoto} 
                  alt="Zählerstand" 
                  className="max-w-full object-contain"
                />
              </div>
            )}
          </DialogContent>
        </Dialog>
      </main>
    </div>
  );
};

export default ZaehlerstaendePage;
