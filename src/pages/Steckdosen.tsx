
import { DataTable } from "@/components/common/DataTable";
import NavBar from "@/components/layout/NavBar";
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { SteckdosenForm } from "@/components/steckdosen/SteckdosenForm";
import { Steckdose, Mieter, Zaehler, Bereich } from "@/types";

const SteckdosenPage = () => {
  // Testdaten für Mieter, Zähler und Bereiche (später durch echte Daten ersetzen)
  const mieter: Mieter[] = [
    { id: 1, vorname: "Max", nachname: "Mustermann", strasse: "Hafenstr.", hausnummer: "1", email: "max@beispiel.de", telefon: "12345", mobil: "67890", hinweis: "", bootsname: "Wellentänzer" },
    { id: 2, vorname: "Erika", nachname: "Musterfrau", strasse: "Seemannsgasse", hausnummer: "42", email: "erika@beispiel.de", telefon: "54321", mobil: "09876", hinweis: "", bootsname: "Windrausch" }
  ];
  
  const zaehler: Zaehler[] = [
    { id: 1, zaehlernummer: "Z-001", installiertAm: "2023-01-15", letzteWartung: "2023-12-01", hinweis: "Neu installiert" },
    { id: 2, zaehlernummer: "Z-002", installiertAm: "2023-02-20", letzteWartung: "2023-11-15", hinweis: "" }
  ];
  
  const bereiche: Bereich[] = [
    { id: 1, name: "Steg A" },
    { id: 2, name: "Steg B" }
  ];

  const [steckdosen, setSteckdosen] = useState<Steckdose[]>([
    {
      id: 1,
      nummer: "S-001",
      vergeben: true,
      mieterId: 1,
      zaehlerId: 1,
      bereichId: 1,
      hinweis: "Funktionstüchtig",
      schluesselnummer: "K-123"
    },
    {
      id: 2,
      nummer: "S-002",
      vergeben: false,
      mieterId: null,
      zaehlerId: 2,
      bereichId: 1,
      hinweis: "",
      schluesselnummer: "K-124"
    }
  ]);
  
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [currentSteckdose, setCurrentSteckdose] = useState<Steckdose | undefined>(undefined);
  const { toast } = useToast();
  
  // Hilfsfunktionen zum Abrufen der Mieter-, Zähler- und Bereichsnamen
  const getMieterName = (mieterId: number | null) => {
    if (!mieterId) return "-";
    const mieterObj = mieter.find(m => m.id === mieterId);
    return mieterObj ? `${mieterObj.vorname} ${mieterObj.nachname}` : "-";
  };
  
  const getZaehlerNummer = (zaehlerId: number | null) => {
    if (!zaehlerId) return "-";
    const zaehlerObj = zaehler.find(z => z.id === zaehlerId);
    return zaehlerObj ? zaehlerObj.zaehlernummer : "-";
  };
  
  const getBereichName = (bereichId: number | null) => {
    if (!bereichId) return "-";
    const bereichObj = bereiche.find(b => b.id === bereichId);
    return bereichObj ? bereichObj.name : "-";
  };
  
  const columns = [
    { header: "ID", accessorKey: "id" as keyof Steckdose },
    { header: "Nummer", accessorKey: "nummer" as keyof Steckdose },
    { 
      header: "Status", 
      accessorKey: (row: Steckdose) => row.vergeben ? "Vergeben" : "Verfügbar"
    },
    { header: "Schlüsselnummer", accessorKey: "schluesselnummer" as keyof Steckdose },
    { 
      header: "Mieter", 
      accessorKey: (row: Steckdose) => getMieterName(row.mieterId)
    },
    { 
      header: "Zähler", 
      accessorKey: (row: Steckdose) => getZaehlerNummer(row.zaehlerId)
    },
    { 
      header: "Bereich", 
      accessorKey: (row: Steckdose) => getBereichName(row.bereichId)
    }
  ];
  
  const handleAdd = () => {
    setCurrentSteckdose(undefined);
    setIsFormOpen(true);
  };
  
  const handleEdit = (steckdose: Steckdose) => {
    setCurrentSteckdose(steckdose);
    setIsFormOpen(true);
  };
  
  const handleDelete = (steckdose: Steckdose) => {
    setSteckdosen(prev => prev.filter(item => item.id !== steckdose.id));
    
    toast({
      title: "Steckdose gelöscht",
      description: `Steckdose ${steckdose.nummer} wurde erfolgreich gelöscht.`,
    });
  };
  
  const handleFormSubmit = (data: Partial<Steckdose>) => {
    console.log("Form data:", data);
    
    if (currentSteckdose) {
      // Bearbeiten einer vorhandenen Steckdose
      setSteckdosen(prev =>
        prev.map(item =>
          item.id === currentSteckdose.id
            ? { ...item, ...data }
            : item
        )
      );
      
      toast({
        title: "Steckdose aktualisiert",
        description: `Steckdose ${data.nummer} wurde erfolgreich aktualisiert.`,
      });
    } else {
      // Hinzufügen einer neuen Steckdose
      const newSteckdose: Steckdose = {
        id: Math.max(0, ...steckdosen.map(s => s.id ?? 0)) + 1,
        nummer: data.nummer!,
        vergeben: data.vergeben ?? false,
        schluesselnummer: data.schluesselnummer || "",
        hinweis: data.hinweis || "",
        mieterId: data.mieterId ?? null,
        zaehlerId: data.zaehlerId ?? null,
        bereichId: data.bereichId ?? null,
      };
      
      setSteckdosen(prev => [...prev, newSteckdose]);
      
      toast({
        title: "Steckdose hinzugefügt",
        description: `Steckdose ${data.nummer} wurde erfolgreich hinzugefügt.`,
      });
    }
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
        
        <SteckdosenForm
          open={isFormOpen}
          onOpenChange={setIsFormOpen}
          onSubmit={handleFormSubmit}
          initialData={currentSteckdose}
        />
      </main>
    </div>
  );
};

export default SteckdosenPage;
