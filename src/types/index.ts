
// Mieter Typ
export interface Mieter {
  id?: number;
  vorname: string;
  nachname: string;
  strasse: string;
  hausnummer: string;
  email: string;
  telefon: string;
  mobil: string;
  hinweis: string;
  bootsname: string;
}

// Bereich Typ
export interface Bereich {
  id?: number;
  name: string;
}

// Zähler Typ
export interface Zaehler {
  id?: number;
  zaehlernummer: string;
  installiertAm: string;
  letzteWartung: string;
  hinweis: string;
  istAusgebaut?: boolean;
}

// Steckdose Typ
export interface Steckdose {
  id?: number;
  nummer: string;
  vergeben: boolean;
  mieterId: number | null;
  mieter?: Mieter;
  zaehlerId: number | null;
  zaehler?: Zaehler;
  bereichId: number | null;
  bereich?: Bereich;
  hinweis: string;
  schluesselnummer: string;
}

// Zählerstand Typ
export interface Zaehlerstand {
  id?: number;
  zaehlerId: number;
  steckdoseId: number | null;
  datum: string;
  stand: number;
  vorherigerId: number | null;
  verbrauch: number | null;
  abgelesenVonId: string | null;
  fotoUrl: string | null;
  istAbgerechnet: boolean;
  hinweis: string;
  zaehler?: Zaehler;
  steckdose?: Steckdose;
  abgelesenVon?: User;
}

// Benutzer Typ
export interface User {
  id: string;
  email: string;
  name: string;
  role: 'admin' | 'user';
  status: 'active' | 'pending';
  avatar?: string;
}

// API Antwort Typ
export interface ApiResponse<T> {
  data: T;
  status: boolean;
  message?: string;
}

// Paginated Response Typ
export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
  perPage: number;
  totalPages: number;
}
