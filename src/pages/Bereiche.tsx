
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { BereichForm } from "@/components/bereich/BereichForm";
import { getBereichColumns } from "@/components/bereich/BereichColumns";
import { useBereich } from "@/hooks/useBereich";

const BereichePage = () => {
  const {
    bereiche,
    editingBereich,
    isDialogOpen,
    setEditingBereich,
    setIsDialogOpen,
    handleAdd,
    handleEdit,
    handleDelete,
    handleSave
  } = useBereich();
  
  const columns = getBereichColumns();
  
  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Bereiche verwalten</h1>
        
        <DataTable
          data={bereiche}
          columns={columns}
          onAdd={handleAdd}
          onEdit={handleEdit}
          onDelete={handleDelete}
          searchable
        />
        
        <BereichForm
          bereich={editingBereich}
          isOpen={isDialogOpen}
          onOpenChange={setIsDialogOpen}
          onSave={handleSave}
          onBereichChange={setEditingBereich}
        />
      </main>
    </div>
  );
};

export default BereichePage;
