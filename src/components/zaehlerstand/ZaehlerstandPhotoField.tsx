
import { FormField, FormItem, FormLabel, FormControl, FormMessage } from "@/components/ui/form";
import { Control } from "react-hook-form";
import { PhotoUpload } from "@/components/zaehlerstand/PhotoUpload";
import { ZaehlerstandFormValues } from "./ZaehlerstandFormSchema";

interface ZaehlerstandPhotoFieldProps {
  control: Control<ZaehlerstandFormValues>;
  onPhotoChange: (base64: string | null) => void;
}

export function ZaehlerstandPhotoField({ control, onPhotoChange }: ZaehlerstandPhotoFieldProps) {
  return (
    <FormField
      control={control}
      name="fotoUrl"
      render={({ field }) => (
        <FormItem>
          <FormLabel>Foto vom Zählerstand</FormLabel>
          <FormControl>
            <PhotoUpload 
              initialImage={field.value || undefined} 
              onImageChange={onPhotoChange} 
            />
          </FormControl>
          <FormMessage />
        </FormItem>
      )}
    />
  );
}
