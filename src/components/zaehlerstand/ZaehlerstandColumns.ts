
import { Zaehlerstand } from "@/types";
import { formatDateString } from "@/lib/dateUtils";
import { Column } from "@/components/common/DataTable";

export const getZaehlerstandColumns = (): Column<Zaehlerstand>[] => [
  { 
    header: "Zähler", 
    accessorKey: (row: Zaehlerstand) => row.zaehler?.zaehlernummer || "-" 
  },
  { 
    header: "Steckdose", 
    accessorKey: (row: Zaehlerstand) => row.steckdose?.nummer || "-" 
  },
  { 
    header: "Datum", 
    accessorKey: (row: Zaehlerstand) => formatDateString(row.datum) 
  },
  { 
    header: "Stand (kWh)", 
    accessorKey: "stand" 
  },
  { 
    header: "Verbrauch (kWh)", 
    accessorKey: (row: Zaehlerstand) => row.verbrauch?.toFixed(2) || "-" 
  },
  { 
    header: "Abgelesen von", 
    accessorKey: (row: Zaehlerstand) => row.abgelesenVon?.name || "-" 
  },
  { 
    header: "Status", 
    accessorKey: (row: Zaehlerstand) => row.istAbgerechnet ? "Abgerechnet" : "Offen" 
  },
  { 
    header: "Hinweis", 
    accessorKey: "hinweis" 
  }
];
