
import { useState, useEffect } from "react";
import { Zaehlerstand, Zaehler, Steckdose } from "@/types";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { PhotoUpload } from "@/components/zaehlerstand/PhotoUpload";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { Check, X } from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { toISODateString } from "@/lib/dateUtils";

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

// Schema für die Validierung des Formulars
const zaehlerstandFormSchema = z.object({
  zaehlerId: z.number({
    required_error: "Bitte wählen Sie einen Zähler aus",
  }),
  steckdoseId: z.number().nullable().optional(),
  datum: z.string({
    required_error: "Bitte wählen Sie ein Datum aus",
  }),
  stand: z.coerce.number({
    required_error: "Bitte geben Sie den Zählerstand ein",
    invalid_type_error: "Zählerstand muss eine Zahl sein",
  }).min(0, "Zählerstand kann nicht negativ sein"),
  istAbgerechnet: z.boolean().default(false),
  hinweis: z.string().optional(),
  fotoUrl: z.string().nullable().optional(),
});

type ZaehlerstandFormValues = z.infer<typeof zaehlerstandFormSchema>;

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
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="zaehlerId"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Zähler*</FormLabel>
                    <Select 
                      value={field.value?.toString()}
                      onValueChange={(value) => field.onChange(Number(value))}
                    >
                      <FormControl>
                        <SelectTrigger>
                          <SelectValue placeholder="Zähler auswählen" />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {dummyZaehler.map((zaehler) => (
                          <SelectItem key={zaehler.id} value={zaehler.id?.toString() || ""}>
                            {zaehler.zaehlernummer}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="steckdoseId"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Steckdose</FormLabel>
                    <Select 
                      value={field.value?.toString() || ""}
                      onValueChange={(value) => field.onChange(value ? Number(value) : null)}
                      disabled={!!automaticSteckdose}
                    >
                      <FormControl>
                        <SelectTrigger>
                          <SelectValue placeholder={automaticSteckdose ? automaticSteckdose.nummer : "Steckdose auswählen"} />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        <SelectItem value="">Keine Steckdose</SelectItem>
                        {dummySteckdosen.map((steckdose) => (
                          <SelectItem key={steckdose.id} value={steckdose.id?.toString() || ""}>
                            {steckdose.nummer}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="datum"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Datum*</FormLabel>
                    <FormControl>
                      <Input type="date" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="stand"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Zählerstand (kWh)*</FormLabel>
                    <FormControl>
                      <Input 
                        type="number" 
                        step="0.01" 
                        placeholder="0.00" 
                        {...field} 
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            
            <FormField
              control={form.control}
              name="fotoUrl"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Foto vom Zählerstand</FormLabel>
                  <FormControl>
                    <PhotoUpload 
                      initialImage={field.value || undefined} 
                      onImageChange={handlePhotoUpload} 
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            
            {isEditing && (
              <FormField
                control={form.control}
                name="istAbgerechnet"
                render={({ field }) => (
                  <FormItem className="flex flex-row items-center space-x-3 space-y-0">
                    <FormControl>
                      <Checkbox 
                        checked={field.value} 
                        onCheckedChange={field.onChange}
                      />
                    </FormControl>
                    <FormLabel>Abgerechnet</FormLabel>
                    <FormMessage />
                  </FormItem>
                )}
              />
            )}
            
            <FormField
              control={form.control}
              name="hinweis"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Hinweis</FormLabel>
                  <FormControl>
                    <Textarea 
                      placeholder="Optionale Hinweise" 
                      {...field} 
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
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
