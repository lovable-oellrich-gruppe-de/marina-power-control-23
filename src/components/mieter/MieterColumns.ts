
import { Column } from "@/components/common/DataTable";
import { Mieter } from "@/types";

export const getMieterColumns = (): Column<Mieter>[] => [
  { header: "ID", accessorKey: "id" as keyof Mieter },
  { 
    header: "Name", 
    accessorKey: (row: Mieter) => `${row.vorname} ${row.nachname}` 
  },
  { header: "E-Mail", accessorKey: "email" as keyof Mieter },
  { header: "Telefon", accessorKey: "telefon" as keyof Mieter },
  { header: "Bootsname", accessorKey: "bootsname" as keyof Mieter }
];
