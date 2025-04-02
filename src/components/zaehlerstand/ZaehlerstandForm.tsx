
import React, { useEffect, useState } from "react";
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
import { PhotoUpload } from "./PhotoUpload";

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

  // Handle photo change
  const handlePhotoChange = (photoUrl: string | null) => {
    setPreviewUrl(photoUrl);
    form.setValue("foto", photoUrl);
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
              render={() => (
                <PhotoUpload 
                  photoUrl={previewUrl} 
                  onChange={handlePhotoChange}
                />
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
