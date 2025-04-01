
import { useState } from "react";
import { Steckdose } from "@/types";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { Check, X } from "lucide-react";

// Schema für die Validierung des Formulars
const steckdosenFormSchema = z.object({
  nummer: z.string().min(1, "Nummer ist erforderlich"),
  vergeben: z.boolean().default(false),
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
      schluesselnummer: initialData?.schluesselnummer || "",
      hinweis: initialData?.hinweis || "",
    },
  });

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
