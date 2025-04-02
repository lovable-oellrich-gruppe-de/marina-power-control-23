
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
  address: string;
  bootName: string;
  stellplatzNr: string;
  vertragStart: string;
  vertragEnde: string;
  notes: string;
}

// Zaehler types
export interface Zaehler {
  id: number;
  zaehlernummer: string;
  installiertAm: string;
  letzteWartung: string;
  notes: string;
}

// Steckdose types
export interface Steckdose {
  id: number;
  nummer: string;
  vergeben: boolean;
  mieterId: number | null;
  zaehlerId: number | null;
  bereichId: number;
  schluesselnummer: string;
  hinweis: string;
}

// Zaehlerstand types
export interface Zaehlerstand {
  id: number;
  zaehlerId: number;
  steckdoseId: number;
  datum: string;
  stand: number;
  vorherigerId: number | null;
  verbrauch: number | null;
  abgelesenVonId: string;
  fotoUrl: string | null;
  istAbgerechnet: boolean;
  hinweis: string;
}
