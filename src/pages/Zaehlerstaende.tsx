
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { ZaehlerstandForm } from "@/components/zaehlerstand/ZaehlerstandForm";
import { getZaehlerstandColumns } from "@/components/zaehlerstand/ZaehlerstandColumns";
import { useZaehlerstand } from "@/hooks/useZaehlerstand";

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
  
  const columns = getZaehlerstandColumns();
  
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
          onDelete={handleDelete}
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
      </main>
    </div>
  );
};

export default ZaehlerstaendePage;
