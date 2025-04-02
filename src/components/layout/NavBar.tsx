
import { useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { Menu, X, User, Power, Gauge, MapPin, Home, LogOut, Settings, Users } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { useIsMobile } from "@/hooks/use-mobile";
import { useAuth } from "@/context/AuthContext";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const navItems = [
  { name: "Dashboard", path: "/", icon: <Home className="h-5 w-5" /> },
  { name: "Mieter", path: "/mieter", icon: <User className="h-5 w-5" /> },
  { name: "Steckdosen", path: "/steckdosen", icon: <Power className="h-5 w-5" /> },
  { name: "Zähler", path: "/zaehler", icon: <Gauge className="h-5 w-5" /> },
  { name: "Bereiche", path: "/bereiche", icon: <MapPin className="h-5 w-5" /> },
  { name: "Zählerstände", path: "/zaehlerstaende", icon: <Zap className="h-5 w-5" /> },
];

const NavBar = () => {
  const location = useLocation();
  const isMobile = useIsMobile();
  const [isOpen, setIsOpen] = useState(false);
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const activeLink = "bg-marina-100 text-marina-800 font-medium";
  const inactiveLink = "text-foreground hover:bg-marina-50";

  const handleProfileClick = () => {
    navigate('/profile');
  };

  const handleLogout = () => {
    logout();
  };

  const handleUserManagementClick = () => {
    navigate('/users');
  };

  // Admin-spezifische Menüpunkte
  const renderAdminMenuItems = () => {
    if (user?.role !== 'admin') return null;

    if (isMobile) {
      return (
        <button
          onClick={() => {
            setIsOpen(false);
            handleUserManagementClick();
          }}
          className="flex w-full items-center gap-3 rounded-md px-3 py-2 text-foreground hover:bg-marina-50"
        >
          <Users className="h-5 w-5" />
          <span>Benutzerverwaltung</span>
        </button>
      );
    }

    return (
      <Link
        to="/users"
        className={`flex items-center gap-2 rounded-md px-3 py-2 ${
          location.pathname === '/users' ? activeLink : inactiveLink
        }`}
      >
        <Users className="h-5 w-5" />
        <span>Benutzerverwaltung</span>
      </Link>
    );
  };

  if (isMobile) {
    return (
      <div className="sticky top-0 z-50 bg-white shadow-sm">
        <div className="flex h-16 items-center justify-between px-4">
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
                    <Link
                      key={item.path}
                      to={item.path}
                      onClick={() => setIsOpen(false)}
                      className={`flex items-center gap-3 rounded-md px-3 py-2 ${
                        location.pathname === item.path ? activeLink : inactiveLink
                      }`}
                    >
                      {item.icon}
                      <span>{item.name}</span>
                    </Link>
                  ))}
                  {user?.role === 'admin' && renderAdminMenuItems()}
                  <div className="mt-4 border-t pt-4">
                    <button
                      onClick={() => {
                        setIsOpen(false);
                        handleProfileClick();
                      }}
                      className="flex w-full items-center gap-3 rounded-md px-3 py-2 text-foreground hover:bg-marina-50"
                    >
                      <Settings className="h-5 w-5" />
                      <span>Profil</span>
                    </button>
                    <button
                      onClick={() => {
                        setIsOpen(false);
                        handleLogout();
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
            <h1 className="text-xl font-bold text-marina-800">Marina Power</h1>
          </div>
          <div className="flex items-center gap-2">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                  <Avatar className="h-8 w-8">
                    <AvatarFallback className="bg-marina-600 text-white">
                      {user?.name?.charAt(0) || 'A'}
                    </AvatarFallback>
                  </Avatar>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <div className="flex items-center justify-start gap-2 p-2">
                  <div className="flex flex-col space-y-1 leading-none">
                    <p className="font-medium">{user?.name || 'Anonym'}</p>
                    <p className="text-xs text-muted-foreground">
                      {user?.email || 'keine E-Mail'}
                    </p>
                  </div>
                </div>
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={handleProfileClick}>
                  <Settings className="mr-2 h-4 w-4" />
                  <span>Profil</span>
                </DropdownMenuItem>
                {user?.role === 'admin' && (
                  <DropdownMenuItem onClick={handleUserManagementClick}>
                    <Users className="mr-2 h-4 w-4" />
                    <span>Benutzerverwaltung</span>
                  </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={handleLogout} className="text-destructive focus:text-destructive">
                  <LogOut className="mr-2 h-4 w-4" />
                  <span>Abmelden</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="sticky top-0 z-50 bg-white shadow-sm">
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <div className="flex items-center gap-8">
          <h1 className="text-xl font-bold text-marina-800">Marina Power Control</h1>
          <nav className="flex items-center gap-1">
            {navItems.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                className={`flex items-center gap-2 rounded-md px-3 py-2 ${
                  location.pathname === item.path ? activeLink : inactiveLink
                }`}
              >
                {item.icon}
                <span>{item.name}</span>
              </Link>
            ))}
            {user?.role === 'admin' && renderAdminMenuItems()}
          </nav>
        </div>
        <div className="flex items-center gap-2">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                <Avatar className="h-8 w-8">
                  <AvatarFallback className="bg-marina-600 text-white">
                    {user?.name?.charAt(0) || 'A'}
                  </AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <div className="flex items-center justify-start gap-2 p-2">
                <div className="flex flex-col space-y-1 leading-none">
                  <p className="font-medium">{user?.name || 'Anonym'}</p>
                  <p className="text-xs text-muted-foreground">
                    {user?.email || 'keine E-Mail'}
                  </p>
                </div>
              </div>
              <DropdownMenuSeparator />
              <DropdownMenuItem onClick={handleProfileClick}>
                <Settings className="mr-2 h-4 w-4" />
                <span>Profil</span>
              </DropdownMenuItem>
              {user?.role === 'admin' && (
                <DropdownMenuItem onClick={handleUserManagementClick}>
                  <Users className="mr-2 h-4 w-4" />
                  <span>Benutzerverwaltung</span>
                </DropdownMenuItem>
              )}
              <DropdownMenuSeparator />
              <DropdownMenuItem onClick={handleLogout} className="text-destructive focus:text-destructive">
                <LogOut className="mr-2 h-4 w-4" />
                <span>Abmelden</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </div>
  );
};

export default NavBar;
