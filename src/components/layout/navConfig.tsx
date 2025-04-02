
import { Home, User, Power, Gauge, MapPin, Zap } from "lucide-react";
import React from "react";

export const navItems = [
  { name: "Dashboard", path: "/", icon: <Home className="h-5 w-5" /> },
  { name: "Mieter", path: "/mieter", icon: <User className="h-5 w-5" /> },
  { name: "Steckdosen", path: "/steckdosen", icon: <Power className="h-5 w-5" /> },
  { name: "Zähler", path: "/zaehler", icon: <Gauge className="h-5 w-5" /> },
  { name: "Bereiche", path: "/bereiche", icon: <MapPin className="h-5 w-5" /> },
  { name: "Zählerstände", path: "/zaehlerstaende", icon: <Zap className="h-5 w-5" /> },
];
