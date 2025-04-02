
import { FormField, FormItem, FormLabel, FormControl, FormMessage } from "@/components/ui/form";
import { Control } from "react-hook-form";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ZaehlerstandFormValues } from "./ZaehlerstandFormSchema";
import { Zaehler, Steckdose } from "@/types";

interface ZaehlerstandDeviceFieldsProps {
  control: Control<ZaehlerstandFormValues>;
  zaehler: Zaehler[];
  steckdosen: Steckdose[];
  automaticSteckdose: Steckdose | null;
}

export function ZaehlerstandDeviceFields({ 
  control, 
  zaehler, 
  steckdosen, 
  automaticSteckdose 
}: ZaehlerstandDeviceFieldsProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      <FormField
        control={control}
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
                {zaehler.map((z) => (
                  <SelectItem key={z.id} value={z.id?.toString() || ""}>
                    {z.zaehlernummer}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <FormMessage />
          </FormItem>
        )}
      />
      
      <FormField
        control={control}
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
                {steckdosen.map((steckdose) => (
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
  );
}
