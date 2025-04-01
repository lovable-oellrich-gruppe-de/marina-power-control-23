
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
  zaehler?: Zaehler;
  datum: string;
  foto: string | null;
  kommentar: string;
  stand: string; // Verwendet als String für präzise Dezimalzahlen
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
