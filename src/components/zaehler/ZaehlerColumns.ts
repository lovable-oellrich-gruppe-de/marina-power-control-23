
import { Zaehler } from "@/types";
import { Column } from "@/components/common/DataTable";
import { formatDateString } from "@/lib/dateUtils";

export const getZaehlerColumns = (): Column<Zaehler>[] => [
  { 
    header: "Zählernummer", 
    accessorKey: "zaehlernummer" 
  },
  { 
    header: "Installiert am", 
    accessorKey: (row: Zaehler) => formatDateString(row.installiertAm) 
  },
  { 
    header: "Letzte Wartung", 
    accessorKey: (row: Zaehler) => formatDateString(row.letzteWartung) 
  },
  { 
    header: "Status", 
    accessorKey: (row: Zaehler) => row.istAusgebaut ? "Ausgebaut" : "Installiert" 
  },
  { 
    header: "Hinweis", 
    accessorKey: "hinweis" 
  }
];
