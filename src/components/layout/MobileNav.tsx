
import { ReactNode, useState } from "react";
import { Button } from "@/components/ui/button";
import { Menu, Settings, LogOut, Users } from "lucide-react";
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { useLocation } from "react-router-dom";
import { NavItem } from "./NavItem";
import { User } from "@/types";
import { navItems } from "./navConfig";

interface MobileNavProps {
  user: User | null;
  onProfileClick: () => void;
  onLogout: () => void;
  onUserManagementClick: () => void;
}

export const MobileNav = ({ 
  user, 
  onProfileClick, 
  onLogout, 
  onUserManagementClick 
}: MobileNavProps) => {
  const location = useLocation();
  const [isOpen, setIsOpen] = useState(false);
  
  return (
    <div className="flex items-center gap-2">
      <Sheet open={isOpen} onOpenChange={setIsOpen}>
        <SheetTrigger asChild>
          <Button variant="ghost" size="icon">
            <Menu className="h-6 w-6" />
          </Button>
        </SheetTrigger>
        <SheetContent side="left" className="p-0">
          <div className="flex h-16 items-center border-b px-6">
            <h2 className="text-lg font-semibold text-marina-700">Marina Power Control</h2>
          </div>
          <nav className="flex flex-col gap-1 p-2">
            {navItems.map((item) => (
              <NavItem
                key={item.path}
                path={item.path}
                name={item.name}
                icon={item.icon}
                isActive={location.pathname === item.path}
                onClick={() => setIsOpen(false)}
              />
            ))}
            {user?.role === 'admin' && (
              <NavItem
                path="/users"
                name="Benutzerverwaltung"
                icon={<Users className="h-5 w-5" />}
                isActive={location.pathname === '/users'}
                onClick={() => setIsOpen(false)}
              />
            )}
            <div className="mt-4 border-t pt-4">
              <button
                onClick={() => {
                  setIsOpen(false);
                  onProfileClick();
                }}
                className="flex w-full items-center gap-3 rounded-md px-3 py-2 text-foreground hover:bg-marina-50"
              >
                <Settings className="h-5 w-5" />
                <span>Profil</span>
              </button>
              <button
                onClick={() => {
                  setIsOpen(false);
                  onLogout();
                }}
                className="flex w-full items-center gap-3 rounded-md px-3 py-2 text-destructive hover:bg-destructive/10"
              >
                <LogOut className="h-5 w-5" />
                <span>Abmelden</span>
              </button>
            </div>
          </nav>
        </SheetContent>
      </Sheet>
    </div>
  );
};
