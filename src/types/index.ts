
// User types
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'user';
}

// Bereich types
export interface Bereich {
  id: number;
  name: string;
  description: string;
}

// Mieter types
export interface Mieter {
  id: number;
  vorname: string;
  nachname: string;
  email: string;
  telefon: string;
  mobil?: string;
  strasse?: string;
  hausnummer?: string;
  address: string;
  bootName: string;
  bootsname?: string; // Legacy compatibility
  stellplatzNr: string;
  vertragStart: string;
  vertragEnde: string;
  notes: string;
  hinweis?: string; // Legacy compatibility
}

// Zaehler types
export interface Zaehler {
  id: number;
  zaehlernummer: string;
  installiertAm: string;
  letzteWartung: string;
  notes: string;
  istAusgebaut?: boolean;
  hinweis?: string; // Legacy compatibility
}

// Steckdose types
export interface Steckdose {
  id: number;
  nummer: string;
  vergeben: boolean;
  mieterId: number | null;
  mieter?: Mieter; // Added relation
  zaehlerId: number | null;
  zaehler?: Zaehler; // Added relation
  bereichId: number;
  bereich?: Bereich; // Added relation
  schluesselnummer: string;
  hinweis: string;
}

// Zaehlerstand types
export interface Zaehlerstand {
  id: number;
  zaehlerId: number;
  zaehler?: Zaehler;
  steckdoseId: number;
  steckdose?: Steckdose;
  datum: string;
  stand: number;
  vorherigerId: number | null;
  verbrauch: number | null;
  abgelesenVonId: string;
  abgelesenVon?: User;
  fotoUrl: string | null;
  istAbgerechnet: boolean;
  hinweis: string;
}
