
import { Bereich } from "@/types";
import { Column } from "@/components/common/DataTable";

export const getBereichColumns = (): Column<Bereich>[] => [
  { 
    header: "ID", 
    accessorKey: "id" 
  },
  { 
    header: "Name", 
    accessorKey: "name" 
  }
];
