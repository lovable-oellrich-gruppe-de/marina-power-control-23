
import React, { useEffect, useRef, useState } from "react";
import * as z from "zod";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Zaehlerstand, Zaehler } from "@/types";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Camera, X } from "lucide-react";

// Form schema with validation
const zaehlerstandSchema = z.object({
  zaehlerId: z.number({
    required_error: "Zähler ist erforderlich",
    invalid_type_error: "Zähler muss eine Zahl sein",
  }),
  datum: z.string().min(1, { message: "Datum ist erforderlich" }),
  stand: z.string().min(1, { message: "Zählerstand ist erforderlich" }),
  kommentar: z.string().optional(),
  foto: z.string().nullable().optional(),
});

type ZaehlerstandFormValues = z.infer<typeof zaehlerstandSchema>;

interface ZaehlerstandFormProps {
  zaehlerstand: Zaehlerstand | undefined;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  onSave: (data: Zaehlerstand) => void;
  onZaehlerstandChange: (zaehlerstand: Zaehlerstand | undefined) => void;
  availableZaehler: Zaehler[];
}

export const ZaehlerstandForm = ({
  zaehlerstand,
  isOpen,
  onOpenChange,
  onSave,
  onZaehlerstandChange,
  availableZaehler,
}: ZaehlerstandFormProps) => {
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const cameraInputRef = useRef<HTMLInputElement>(null);
  
  // Initialize form with validation
  const form = useForm<ZaehlerstandFormValues>({
    resolver: zodResolver(zaehlerstandSchema),
    defaultValues: {
      zaehlerId: zaehlerstand?.zaehlerId || 0,
      datum: zaehlerstand?.datum || "",
      stand: zaehlerstand?.stand || "",
      kommentar: zaehlerstand?.kommentar || "",
      foto: zaehlerstand?.foto || null,
    },
  });

  // Update form when zaehlerstand changes
  useEffect(() => {
    if (zaehlerstand) {
      form.reset({
        zaehlerId: zaehlerstand.zaehlerId,
        datum: zaehlerstand.datum,
        stand: zaehlerstand.stand,
        kommentar: zaehlerstand.kommentar || "",
        foto: zaehlerstand.foto,
      });
      setPreviewUrl(zaehlerstand.foto);
    } else {
      form.reset({
        zaehlerId: availableZaehler[0]?.id || 0,
        datum: new Date().toISOString().split('T')[0],
        stand: "",
        kommentar: "",
        foto: null,
      });
      setPreviewUrl(null);
    }
  }, [zaehlerstand, form, availableZaehler]);

  // Funktion zum Hochladen einer Bilddatei
  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      const result = reader.result as string;
      setPreviewUrl(result);
      form.setValue("foto", result);
    };
    reader.readAsDataURL(file);
  };

  // Funktion zum Auslösen des Datei-Uploads
  const triggerFileUpload = () => {
    fileInputRef.current?.click();
  };

  // Funktion zum Auslösen der Kamera
  const triggerCamera = () => {
    cameraInputRef.current?.click();
  };

  // Funktion zum Entfernen des Fotos
  const removePhoto = () => {
    setPreviewUrl(null);
    form.setValue("foto", null);
    if (fileInputRef.current) fileInputRef.current.value = "";
    if (cameraInputRef.current) cameraInputRef.current.value = "";
  };

  // Handle form submission
  const onSubmit = (values: ZaehlerstandFormValues) => {
    onSave({
      id: zaehlerstand?.id,
      zaehlerId: values.zaehlerId,
      datum: values.datum,
      stand: values.stand,
      kommentar: values.kommentar || "",
      foto: values.foto,
    });
  };

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>
            {zaehlerstand?.id ? "Zählerstand bearbeiten" : "Neuen Zählerstand hinzufügen"}
          </DialogTitle>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="zaehlerId"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Zähler</FormLabel>
                  <Select
                    value={String(field.value)}
                    onValueChange={(value) => field.onChange(Number(value))}
                  >
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Zähler auswählen" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {availableZaehler.map((zaehler) => (
                        <SelectItem key={zaehler.id} value={String(zaehler.id)}>
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
              name="datum"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Datum</FormLabel>
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
                  <FormLabel>Zählerstand</FormLabel>
                  <FormControl>
                    <Input
                      placeholder="z.B. 123.45"
                      {...field}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="kommentar"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Kommentar</FormLabel>
                  <FormControl>
                    <Input
                      placeholder="Optionaler Kommentar"
                      {...field}
                      value={field.value || ""}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="foto"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Foto</FormLabel>
                  <div className="space-y-2">
                    {previewUrl ? (
                      <div className="relative w-full rounded-md overflow-hidden">
                        <img 
                          src={previewUrl} 
                          alt="Zählerstand" 
                          className="w-full h-auto max-h-48 object-contain border rounded-md"
                        />
                        <button 
                          type="button" 
                          onClick={removePhoto}
                          className="absolute top-2 right-2 bg-destructive text-destructive-foreground p-1 rounded-full hover:bg-destructive/90"
                        >
                          <X size={16} />
                        </button>
                      </div>
                    ) : (
                      <div className="flex gap-2">
                        <Button 
                          type="button" 
                          variant="outline" 
                          onClick={triggerFileUpload}
                          className="flex-1"
                        >
                          Foto auswählen
                        </Button>
                        <Button 
                          type="button" 
                          variant="outline" 
                          onClick={triggerCamera}
                          className="flex-1"
                        >
                          <Camera className="mr-2" size={16} />
                          Foto aufnehmen
                        </Button>
                      </div>
                    )}
                    
                    <input
                      type="file"
                      accept="image/*"
                      ref={fileInputRef}
                      onChange={handleFileChange}
                      className="hidden"
                    />
                    
                    <input
                      type="file"
                      accept="image/*"
                      capture="environment"
                      ref={cameraInputRef}
                      onChange={handleFileChange}
                      className="hidden"
                    />
                  </div>
                  <FormMessage />
                </FormItem>
              )}
            />

            <DialogFooter>
              <Button
                type="button"
                variant="outline"
                onClick={() => onOpenChange(false)}
              >
                Abbrechen
              </Button>
              <Button type="submit" className="bg-marina-600 hover:bg-marina-700">
                Speichern
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};
