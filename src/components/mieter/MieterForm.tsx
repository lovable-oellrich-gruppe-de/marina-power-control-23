
import { Mieter } from "@/types";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";

interface MieterFormProps {
  mieter: Mieter | null;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  onSave: () => void;
  onMieterChange: (mieter: Mieter) => void;
}

export function MieterForm({ 
  mieter, 
  isOpen, 
  onOpenChange, 
  onSave, 
  onMieterChange 
}: MieterFormProps) {
  if (!mieter) return null;

  const handleInputChange = (field: keyof Mieter, value: string) => {
    onMieterChange({ ...mieter, [field]: value });
  };

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[550px]">
        <DialogHeader>
          <DialogTitle>
            {mieter.id ? 'Mieter bearbeiten' : 'Neuen Mieter anlegen'}
          </DialogTitle>
        </DialogHeader>
        
        <div className="grid gap-4 py-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="vorname">Vorname *</Label>
              <Input
                id="vorname"
                value={mieter.vorname || ''}
                onChange={(e) => handleInputChange('vorname', e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="nachname">Nachname *</Label>
              <Input
                id="nachname"
                value={mieter.nachname || ''}
                onChange={(e) => handleInputChange('nachname', e.target.value)}
              />
            </div>
          </div>
          
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="strasse">Straße</Label>
              <Input
                id="strasse"
                value={mieter.strasse || ''}
                onChange={(e) => handleInputChange('strasse', e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="hausnummer">Hausnummer</Label>
              <Input
                id="hausnummer"
                value={mieter.hausnummer || ''}
                onChange={(e) => handleInputChange('hausnummer', e.target.value)}
              />
            </div>
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="email">E-Mail *</Label>
            <Input
              id="email"
              type="email"
              value={mieter.email || ''}
              onChange={(e) => handleInputChange('email', e.target.value)}
            />
          </div>
          
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="telefon">Telefon</Label>
              <Input
                id="telefon"
                value={mieter.telefon || ''}
                onChange={(e) => handleInputChange('telefon', e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="mobil">Mobil</Label>
              <Input
                id="mobil"
                value={mieter.mobil || ''}
                onChange={(e) => handleInputChange('mobil', e.target.value)}
              />
            </div>
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="bootsname">Bootsname</Label>
            <Input
              id="bootsname"
              value={mieter.bootsname || ''}
              onChange={(e) => handleInputChange('bootsname', e.target.value)}
            />
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="hinweis">Hinweis</Label>
            <Textarea
              id="hinweis"
              value={mieter.hinweis || ''}
              onChange={(e) => handleInputChange('hinweis', e.target.value)}
            />
          </div>
        </div>
        
        <div className="flex justify-end space-x-4">
          <Button variant="outline" onClick={() => onOpenChange(false)}>
            Abbrechen
          </Button>
          <Button className="bg-marina-600 hover:bg-marina-700" onClick={onSave}>
            Speichern
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}
