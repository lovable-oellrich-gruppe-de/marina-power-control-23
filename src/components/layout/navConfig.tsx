
import { BarChart, Plug, User2, Power, Cpu, Database, Settings } from "lucide-react";

export const navItems = [
  {
    name: "Dashboard",
    path: "/",
    icon: <BarChart className="h-5 w-5" />,
  },
  {
    name: "Mieter",
    path: "/mieter",
    icon: <User2 className="h-5 w-5" />,
  },
  {
    name: "Steckdosen",
    path: "/steckdosen",
    icon: <Plug className="h-5 w-5" />,
  },
  {
    name: "Zähler",
    path: "/zaehler",
    icon: <Power className="h-5 w-5" />,
  },
  {
    name: "Zählerstände",
    path: "/zaehlerstaende",
    icon: <Cpu className="h-5 w-5" />,
  },
  {
    name: "Bereiche",
    path: "/bereiche",
    icon: <Database className="h-5 w-5" />,
  },
  {
    name: "Einstellungen",
    path: "/settings",
    icon: <Settings className="h-5 w-5" />,
  },
];
