
import React, { useRef, useState } from "react";
import { FormControl, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Button } from "@/components/ui/button";
import { Camera, X } from "lucide-react";

interface PhotoUploadProps {
  photoUrl: string | null;
  onChange: (base64: string | null) => void;
}

export const PhotoUpload = ({ photoUrl, onChange }: PhotoUploadProps) => {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const cameraInputRef = useRef<HTMLInputElement>(null);

  // Funktion zum Hochladen einer Bilddatei
  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      const result = reader.result as string;
      onChange(result);
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
    onChange(null);
    if (fileInputRef.current) fileInputRef.current.value = "";
    if (cameraInputRef.current) cameraInputRef.current.value = "";
  };

  return (
    <FormItem>
      <FormLabel>Foto</FormLabel>
      <div className="space-y-2">
        {photoUrl ? (
          <div className="relative w-full rounded-md overflow-hidden">
            <img 
              src={photoUrl} 
              alt="Zählerstand Foto" 
              className="w-full h-auto max-h-48 object-contain border rounded-md"
            />
            <button 
              type="button" 
              onClick={removePhoto}
              aria-label="Foto entfernen"
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
          aria-hidden="true"
        />
        
        <input
          type="file"
          accept="image/*"
          capture="environment"
          ref={cameraInputRef}
          onChange={handleFileChange}
          className="hidden"
          aria-hidden="true"
        />
      </div>
      <FormMessage />
    </FormItem>
  );
};
