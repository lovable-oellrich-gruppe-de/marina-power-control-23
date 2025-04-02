
import React, { useEffect } from "react";
import * as z from "zod";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Bereich } from "@/types";
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

// Form schema with validation
const bereichSchema = z.object({
  name: z.string().min(1, { message: "Name ist erforderlich" }),
  description: z.string().default(""),
});

type BereichFormValues = z.infer<typeof bereichSchema>;

interface BereichFormProps {
  bereich: Bereich | undefined;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  onSave: (data: Bereich) => void;
  onBereichChange: (bereich: Bereich | undefined) => void;
}

export const BereichForm = ({
  bereich,
  isOpen,
  onOpenChange,
  onSave,
  onBereichChange,
}: BereichFormProps) => {
  // Initialize form with validation
  const form = useForm<BereichFormValues>({
    resolver: zodResolver(bereichSchema),
    defaultValues: {
      name: bereich?.name || "",
      description: bereich?.description || "",
    },
  });

  // Update form when bereich changes
  useEffect(() => {
    if (bereich) {
      form.reset({
        name: bereich.name,
        description: bereich.description,
      });
    } else {
      form.reset({
        name: "",
        description: "",
      });
    }
  }, [bereich, form]);

  // Handle form submission
  const onSubmit = (values: BereichFormValues) => {
    onSave({
      id: bereich?.id,
      name: values.name,
      description: values.description,
    });
  };

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{bereich?.id ? "Bereich bearbeiten" : "Neuen Bereich hinzufügen"}</DialogTitle>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="name"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Name</FormLabel>
                  <FormControl>
                    <Input placeholder="Name des Bereichs" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="description"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Beschreibung</FormLabel>
                  <FormControl>
                    <Input placeholder="Beschreibung des Bereichs" {...field} />
                  </FormControl>
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
              <Button 
                type="submit"
                className="bg-marina-600 hover:bg-marina-700"
              >
                Speichern
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};
