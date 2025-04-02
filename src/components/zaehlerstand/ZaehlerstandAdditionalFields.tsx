
import { FormField, FormItem, FormLabel, FormControl, FormMessage } from "@/components/ui/form";
import { Control } from "react-hook-form";
import { ZaehlerstandFormValues } from "./ZaehlerstandFormSchema";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";

interface ZaehlerstandAdditionalFieldsProps {
  control: Control<ZaehlerstandFormValues>;
  isEditing: boolean;
}

export function ZaehlerstandAdditionalFields({ control, isEditing }: ZaehlerstandAdditionalFieldsProps) {
  return (
    <>
      {isEditing && (
        <FormField
          control={control}
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
        control={control}
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
    </>
  );
}
