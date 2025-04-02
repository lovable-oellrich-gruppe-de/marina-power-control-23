
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { ZaehlerForm } from "@/components/zaehler/ZaehlerForm";
import { getZaehlerColumns } from "@/components/zaehler/ZaehlerColumns";
import { useZaehler } from "@/hooks/useZaehler";
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
  
  const columns = getZaehlerColumns();
  
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
          onDelete={handleDelete}
          searchable
        />
        
        <ZaehlerForm
          zaehler={editingZaehler as Zaehler}
          isOpen={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSave={handleSave}
          onZaehlerChange={setEditingZaehler}
        />
      </main>
    </div>
  );
};

export default ZaehlerPage;
