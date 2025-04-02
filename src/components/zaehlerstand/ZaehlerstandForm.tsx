
import { useState, useEffect } from "react";
import { Zaehlerstand, Zaehler, Steckdose } from "@/types";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Form } from "@/components/ui/form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { Check, X } from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { toISODateString } from "@/lib/dateUtils";
import { zaehlerstandFormSchema, ZaehlerstandFormValues } from "./ZaehlerstandFormSchema";
import { ZaehlerstandDeviceFields } from "./ZaehlerstandDeviceFields";
import { ZaehlerstandDataFields } from "./ZaehlerstandDataFields";
import { ZaehlerstandPhotoField } from "./ZaehlerstandPhotoField";
import { ZaehlerstandAdditionalFields } from "./ZaehlerstandAdditionalFields";

// Testdaten für Zähler und Steckdosen
const dummyZaehler: Zaehler[] = [
  { id: 1, zaehlernummer: "Z-001", installiertAm: "2023-01-15", letzteWartung: "2023-12-01", hinweis: "Neu installiert" },
  { id: 2, zaehlernummer: "Z-002", installiertAm: "2023-02-20", letzteWartung: "2023-11-15", hinweis: "" },
  { id: 3, zaehlernummer: "Z-003", installiertAm: "2023-03-10", letzteWartung: "2023-10-30", hinweis: "Bald zur Wartung" }
];

const dummySteckdosen: Steckdose[] = [
  { id: 1, nummer: "A-01", vergeben: true, mieterId: 1, zaehlerId: 1, bereichId: 1, hinweis: "Nahe am Hauptsteg", schluesselnummer: "S-001" },
  { id: 2, nummer: "A-02", vergeben: false, mieterId: null, zaehlerId: 2, bereichId: 1, hinweis: "", schluesselnummer: "S-002" },
  { id: 3, nummer: "B-01", vergeben: true, mieterId: 2, zaehlerId: null, bereichId: 2, hinweis: "Wartung geplant", schluesselnummer: "S-003" }
];

interface ZaehlerstandFormProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSubmit: (data: Zaehlerstand) => void;
  initialData?: Partial<Zaehlerstand>;
}

export function ZaehlerstandForm({
  open,
  onOpenChange,
  onSubmit,
  initialData
}: ZaehlerstandFormProps) {
  const isEditing = !!initialData?.id;
  const { user } = useAuth();
  const [photoBase64, setPhotoBase64] = useState<string | null>(initialData?.fotoUrl || null);
  
  // Füllt die Steckdose automatisch, wenn der Zähler ausgewählt wird
  const [selectedZaehlerId, setSelectedZaehlerId] = useState<number | null>(initialData?.zaehlerId || null);
  const [automaticSteckdose, setAutomaticSteckdose] = useState<Steckdose | null>(null);

  useEffect(() => {
    if (selectedZaehlerId) {
      // Suche Steckdose mit dem ausgewählten Zähler
      const matchedSteckdose = dummySteckdosen.find(s => s.zaehlerId === selectedZaehlerId);
      setAutomaticSteckdose(matchedSteckdose || null);
    } else {
      setAutomaticSteckdose(null);
    }
  }, [selectedZaehlerId]);
  
  const form = useForm<ZaehlerstandFormValues>({
    resolver: zodResolver(zaehlerstandFormSchema),
    defaultValues: {
      zaehlerId: initialData?.zaehlerId || undefined,
      steckdoseId: initialData?.steckdoseId || null,
      datum: initialData?.datum || toISODateString(new Date()),
      stand: initialData?.stand || 0,
      istAbgerechnet: initialData?.istAbgerechnet || false,
      hinweis: initialData?.hinweis || "",
      fotoUrl: initialData?.fotoUrl || null,
    },
  });

  // Form zurücksetzen, wenn sich initialData ändert
  useEffect(() => {
    if (open) {
      form.reset({
        zaehlerId: initialData?.zaehlerId || undefined,
        steckdoseId: initialData?.steckdoseId || null,
        datum: initialData?.datum || toISODateString(new Date()),
        stand: initialData?.stand || 0,
        istAbgerechnet: initialData?.istAbgerechnet || false,
        hinweis: initialData?.hinweis || "",
        fotoUrl: initialData?.fotoUrl || null,
      });
      setPhotoBase64(initialData?.fotoUrl || null);
      setSelectedZaehlerId(initialData?.zaehlerId || null);
    }
  }, [initialData, open, form]);

  // Wenn der Zähler im Formular geändert wird
  useEffect(() => {
    const subscription = form.watch((value, { name }) => {
      if (name === 'zaehlerId') {
        setSelectedZaehlerId(value.zaehlerId as number || null);
        
        // Automatisch die Steckdose aktualisieren
        if (automaticSteckdose) {
          form.setValue('steckdoseId', automaticSteckdose.id);
        }
      }
    });
    
    return () => subscription.unsubscribe();
  }, [form, automaticSteckdose]);

  const handlePhotoUpload = (base64: string | null) => {
    setPhotoBase64(base64);
    form.setValue('fotoUrl', base64);
  };

  const handleSubmit = (values: ZaehlerstandFormValues) => {
    // Stellen Sie sicher, dass alle erforderlichen Felder gesetzt sind
    const zaehlerstandData: Zaehlerstand = {
      id: initialData?.id,
      zaehlerId: values.zaehlerId, // Da dies ein Pflichtfeld ist, stellen wir sicher, dass es immer gesetzt ist
      steckdoseId: values.steckdoseId || null,
      datum: values.datum,
      stand: values.stand,
      vorherigerId: initialData?.vorherigerId || null,
      verbrauch: initialData?.verbrauch || null,
      abgelesenVonId: user?.id || null,
      fotoUrl: photoBase64,
      istAbgerechnet: values.istAbgerechnet,
      hinweis: values.hinweis || ""
    };
    
    onSubmit(zaehlerstandData);
    onOpenChange(false);
    form.reset();
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[550px]">
        <DialogHeader>
          <DialogTitle>{isEditing ? "Zählerstand bearbeiten" : "Neuen Zählerstand erfassen"}</DialogTitle>
        </DialogHeader>
        
        <Form {...form}>
          <form onSubmit={form.handleSubmit(handleSubmit)} className="space-y-4">
            <ZaehlerstandDeviceFields 
              control={form.control} 
              zaehler={dummyZaehler}
              steckdosen={dummySteckdosen}
              automaticSteckdose={automaticSteckdose}
            />
            
            <ZaehlerstandDataFields control={form.control} />
            
            <ZaehlerstandPhotoField 
              control={form.control}
              onPhotoChange={handlePhotoUpload}
            />
            
            <ZaehlerstandAdditionalFields 
              control={form.control}
              isEditing={isEditing}
            />
            
            <DialogFooter className="pt-4">
              <Button 
                type="button" 
                variant="outline" 
                onClick={() => onOpenChange(false)}
              >
                <X className="mr-2 h-4 w-4" />
                Abbrechen
              </Button>
              <Button type="submit" className="bg-marina-600 hover:bg-marina-700">
                <Check className="mr-2 h-4 w-4" />
                {isEditing ? "Speichern" : "Erfassen"}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}
