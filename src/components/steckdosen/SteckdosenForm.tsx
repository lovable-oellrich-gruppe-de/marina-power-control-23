
import { useState, useEffect } from "react";
import { Steckdose, Mieter, Zaehler, Bereich } from "@/types";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { Check, X } from "lucide-react";

// Testdaten für Mieter, Zähler und Bereiche
const dummyMieter: Mieter[] = [
  { id: 1, vorname: "Max", nachname: "Mustermann", strasse: "Hafenstr.", hausnummer: "1", email: "max@beispiel.de", telefon: "12345", mobil: "67890", hinweis: "", bootsname: "Wellentänzer" },
  { id: 2, vorname: "Erika", nachname: "Musterfrau", strasse: "Seemannsgasse", hausnummer: "42", email: "erika@beispiel.de", telefon: "54321", mobil: "09876", hinweis: "", bootsname: "Windrausch" }
];

const dummyZaehler: Zaehler[] = [
  { id: 1, zaehlernummer: "Z-001", installiertAm: "2023-01-15", letzteWartung: "2023-12-01", hinweis: "Neu installiert" },
  { id: 2, zaehlernummer: "Z-002", installiertAm: "2023-02-20", letzteWartung: "2023-11-15", hinweis: "" },
  { id: 3, zaehlernummer: "Z-003", installiertAm: "2023-03-10", letzteWartung: "2023-10-30", hinweis: "Baldig zur Wartung" }
];

const dummyBereiche: Bereich[] = [
  { id: 1, name: "Steg A" },
  { id: 2, name: "Steg B" },
  { id: 3, name: "Steg C" }
];

// Schema für die Validierung des Formulars
const steckdosenFormSchema = z.object({
  nummer: z.string().min(1, "Nummer ist erforderlich"),
  vergeben: z.boolean().default(false),
  mieterId: z.union([z.number(), z.null()]).nullable(),
  zaehlerId: z.union([z.number(), z.null()]).nullable(),
  bereichId: z.union([z.number(), z.null()]).nullable(),
  schluesselnummer: z.string().optional(),
  hinweis: z.string().optional(),
});

type SteckdosenFormValues = z.infer<typeof steckdosenFormSchema>;

interface SteckdosenFormProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSubmit: (data: SteckdosenFormValues) => void;
  initialData?: Partial<Steckdose>;
}

export function SteckdosenForm({
  open,
  onOpenChange,
  onSubmit,
  initialData
}: SteckdosenFormProps) {
  const isEditing = !!initialData?.id;
  
  const form = useForm<SteckdosenFormValues>({
    resolver: zodResolver(steckdosenFormSchema),
    defaultValues: {
      nummer: initialData?.nummer || "",
      vergeben: initialData?.vergeben || false,
      mieterId: initialData?.mieterId || null,
      zaehlerId: initialData?.zaehlerId || null,
      bereichId: initialData?.bereichId || null,
      schluesselnummer: initialData?.schluesselnummer || "",
      hinweis: initialData?.hinweis || "",
    },
  });

  // Form zurücksetzen, wenn sich initialData ändert
  useEffect(() => {
    if (open) {
      form.reset({
        nummer: initialData?.nummer || "",
        vergeben: initialData?.vergeben || false,
        mieterId: initialData?.mieterId || null,
        zaehlerId: initialData?.zaehlerId || null,
        bereichId: initialData?.bereichId || null,
        schluesselnummer: initialData?.schluesselnummer || "",
        hinweis: initialData?.hinweis || "",
      });
    }
  }, [initialData, open, form]);

  const handleSubmit = (values: SteckdosenFormValues) => {
    onSubmit(values);
    onOpenChange(false);
    form.reset();
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>{isEditing ? "Steckdose bearbeiten" : "Neue Steckdose hinzufügen"}</DialogTitle>
        </DialogHeader>
        
        <Form {...form}>
          <form onSubmit={form.handleSubmit(handleSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="nummer"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Steckdosen-Nummer</FormLabel>
                  <FormControl>
                    <Input placeholder="S-001" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            
            <FormField
              control={form.control}
              name="schluesselnummer"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Schlüsselnummer</FormLabel>
                  <FormControl>
                    <Input placeholder="K-123" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            
            <FormField
              control={form.control}
              name="vergeben"
              render={({ field }) => (
                <FormItem className="flex flex-row items-center space-x-3 space-y-0">
                  <FormControl>
                    <Checkbox 
                      checked={field.value} 
                      onCheckedChange={field.onChange}
                    />
                  </FormControl>
                  <FormLabel>Vergeben</FormLabel>
                  <FormMessage />
                </FormItem>
              )}
            />
            
            <FormField
              control={form.control}
              name="mieterId"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Mieter</FormLabel>
                  <Select 
                    value={field.value?.toString() || ""}
                    onValueChange={(value) => field.onChange(value ? Number(value) : null)}
                  >
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Mieter auswählen" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="null">Keinen Mieter zuweisen</SelectItem>
                      {dummyMieter.map((mieter) => (
                        <SelectItem key={mieter.id} value={mieter.id?.toString() || ""}>
                          {mieter.vorname} {mieter.nachname} - {mieter.bootsname}
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
              name="zaehlerId"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Zähler</FormLabel>
                  <Select 
                    value={field.value?.toString() || ""}
                    onValueChange={(value) => field.onChange(value ? Number(value) : null)}
                  >
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Zähler auswählen" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="null">Keinen Zähler zuweisen</SelectItem>
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
              name="bereichId"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Bereich</FormLabel>
                  <Select 
                    value={field.value?.toString() || ""}
                    onValueChange={(value) => field.onChange(value ? Number(value) : null)}
                  >
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Bereich auswählen" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="null">Keinen Bereich zuweisen</SelectItem>
                      {dummyBereiche.map((bereich) => (
                        <SelectItem key={bereich.id} value={bereich.id?.toString() || ""}>
                          {bereich.name}
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
                {isEditing ? "Speichern" : "Hinzufügen"}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}
