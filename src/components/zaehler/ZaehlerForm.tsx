
import { useState } from "react";
import { Zaehler } from "@/types";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { toISODateString } from "@/lib/dateUtils";

interface ZaehlerFormProps {
  zaehler?: Zaehler;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  onSave: (data: Zaehler) => void;
  onZaehlerChange: (data: Zaehler | undefined) => void;
}

export function ZaehlerForm({
  zaehler,
  isOpen,
  onOpenChange,
  onSave,
  onZaehlerChange
}: ZaehlerFormProps) {
  const handleFieldChange = (field: keyof Zaehler, value: string | boolean) => {
    if (!zaehler) return;
    
    onZaehlerChange({
      ...zaehler,
      [field]: value
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!zaehler?.zaehlernummer) return;
    
    onSave(zaehler);
  };

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>{zaehler?.id ? "Zähler bearbeiten" : "Neuen Zähler hinzufügen"}</DialogTitle>
        </DialogHeader>
        
        <form onSubmit={handleSubmit} className="space-y-4 pt-4">
          <div className="grid gap-4">
            <div className="grid grid-cols-4 items-center gap-4">
              <Label htmlFor="zaehlernummer" className="text-right">
                Zählernummer
              </Label>
              <Input
                id="zaehlernummer"
                value={zaehler?.zaehlernummer || ""}
                onChange={e => handleFieldChange("zaehlernummer", e.target.value)}
                placeholder="Z-001"
                className="col-span-3"
                required
              />
            </div>
            
            <div className="grid grid-cols-4 items-center gap-4">
              <Label htmlFor="installiertAm" className="text-right">
                Installiert am
              </Label>
              <Input
                id="installiertAm"
                type="date"
                value={zaehler?.installiertAm || ""}
                onChange={e => handleFieldChange("installiertAm", e.target.value)}
                className="col-span-3"
                required
              />
            </div>
            
            <div className="grid grid-cols-4 items-center gap-4">
              <Label htmlFor="letzteWartung" className="text-right">
                Letzte Wartung
              </Label>
              <Input
                id="letzteWartung"
                type="date"
                value={zaehler?.letzteWartung || ""}
                onChange={e => handleFieldChange("letzteWartung", e.target.value)}
                className="col-span-3"
                required
              />
            </div>
            
            <div className="grid grid-cols-4 items-center gap-4">
              <Label htmlFor="ausgebaut" className="text-right">
                Ausgebaut
              </Label>
              <div className="col-span-3 flex items-center">
                <Switch
                  id="ausgebaut"
                  checked={zaehler?.istAusgebaut || false}
                  onCheckedChange={checked => handleFieldChange("istAusgebaut", checked)}
                />
                <span className="ml-2">
                  {zaehler?.istAusgebaut ? "Ja" : "Nein"}
                </span>
              </div>
            </div>
            
            <div className="grid grid-cols-4 items-center gap-4">
              <Label htmlFor="hinweis" className="text-right">
                Hinweis
              </Label>
              <Input
                id="hinweis"
                value={zaehler?.hinweis || ""}
                onChange={e => handleFieldChange("hinweis", e.target.value)}
                placeholder="Zusätzliche Informationen"
                className="col-span-3"
              />
            </div>
          </div>
          
          <div className="flex justify-end gap-2">
            <Button 
              type="button" 
              variant="outline" 
              onClick={() => onOpenChange(false)}
            >
              Abbrechen
            </Button>
            <Button type="submit">Speichern</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
