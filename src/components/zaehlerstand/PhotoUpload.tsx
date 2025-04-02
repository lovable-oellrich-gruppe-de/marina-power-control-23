
import { useState, useRef } from "react";
import { Button } from "@/components/ui/button";
import { Camera, ImageIcon, Upload, X } from "lucide-react";

interface PhotoUploadProps {
  initialImage?: string;
  onImageChange: (base64: string | null) => void;
}

export const PhotoUpload: React.FC<PhotoUploadProps> = ({ 
  initialImage, 
  onImageChange 
}) => {
  const [preview, setPreview] = useState<string | undefined>(initialImage);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const base64 = reader.result as string;
        setPreview(base64);
        onImageChange(base64);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
  };

  const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    const file = e.dataTransfer.files?.[0];
    
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const base64 = reader.result as string;
        setPreview(base64);
        onImageChange(base64);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSelectFile = () => {
    fileInputRef.current?.click();
  };

  const handleClearImage = () => {
    setPreview(undefined);
    onImageChange(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  return (
    <div className="space-y-2">
      <input
        type="file"
        accept="image/*"
        className="hidden"
        ref={fileInputRef}
        onChange={handleFileChange}
      />
      
      {!preview ? (
        <div
          className="border-2 border-dashed border-gray-300 rounded-md p-6 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer"
          onClick={handleSelectFile}
          onDragOver={handleDragOver}
          onDrop={handleDrop}
        >
          <ImageIcon className="h-10 w-10 text-gray-400 mb-2" />
          <p className="text-sm text-gray-500">
            Klicken Sie oder ziehen Sie ein Bild hierher
          </p>
        </div>
      ) : (
        <div className="relative">
          <img 
            src={preview} 
            alt="Vorschau" 
            className="max-h-[200px] rounded-md mx-auto" 
          />
          <button 
            type="button"
            onClick={handleClearImage}
            className="absolute top-1 right-1 bg-black bg-opacity-60 text-white rounded-full p-1 hover:bg-opacity-80 transition-opacity"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
      )}
      
      <div className="flex space-x-2">
        <Button 
          type="button" 
          variant="outline" 
          size="sm" 
          onClick={handleSelectFile}
          className="flex-1"
        >
          <Upload className="h-4 w-4 mr-2" />
          Bild hochladen
        </Button>
        
        <Button 
          type="button" 
          variant="outline" 
          size="sm" 
          onClick={() => {
            // Diese Funktion würde die Kamera öffnen
            // Für jetzt nur ein Platzhalter
            alert("Kamerafunktion wird in einer späteren Version implementiert.");
          }}
          className="flex-1"
        >
          <Camera className="h-4 w-4 mr-2" />
          Kamera
        </Button>
      </div>
    </div>
  );
};
