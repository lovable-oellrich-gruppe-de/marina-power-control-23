
import { Zaehlerstand } from "@/types";
import { Column } from "@/components/common/DataTable";
import { formatDateString } from "@/lib/dateUtils";

export const getZaehlerstandColumns = (): Column<Zaehlerstand>[] => [
  { 
    header: "Zählernummer", 
    accessorKey: (row: Zaehlerstand) => row.zaehler?.zaehlernummer || "-"
  },
  { 
    header: "Datum", 
    accessorKey: (row: Zaehlerstand) => formatDateString(row.datum)
  },
  { 
    header: "Stand", 
    accessorKey: "stand"
  },
  { 
    header: "Kommentar", 
    accessorKey: "kommentar"
  },
  { 
    header: "Foto", 
    cell: (row: Zaehlerstand) => row.foto ? "Vorhanden" : "Nicht vorhanden"
  }
];
