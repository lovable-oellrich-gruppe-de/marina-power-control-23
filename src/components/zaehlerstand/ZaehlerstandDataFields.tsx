
import { FormField, FormItem, FormLabel, FormControl, FormMessage } from "@/components/ui/form";
import { Control } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { ZaehlerstandFormValues } from "./ZaehlerstandFormSchema";

interface ZaehlerstandDataFieldsProps {
  control: Control<ZaehlerstandFormValues>;
}

export function ZaehlerstandDataFields({ control }: ZaehlerstandDataFieldsProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      <FormField
        control={control}
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
        control={control}
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
  );
}
