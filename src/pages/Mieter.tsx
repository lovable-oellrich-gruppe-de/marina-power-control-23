
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { MieterForm } from "@/components/mieter/MieterForm";
import { getMieterColumns } from "@/components/mieter/MieterColumns";
import { useMieter } from "@/hooks/useMieter";
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
  
  const columns = getMieterColumns();
  
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
          onDelete={handleDelete}
          searchable
        />
        
        <MieterForm
          mieter={editingMieter as Mieter}
          isOpen={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSave={handleSave}
          onMieterChange={setEditingMieter}
        />
      </main>
    </div>
  );
};

export default MieterPage;
