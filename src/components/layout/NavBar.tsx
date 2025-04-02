import { useLocation, useNavigate } from "react-router-dom";
import { Users } from "lucide-react";
import { useIsMobile } from "@/hooks/use-mobile";
import { useAuth } from "@/hooks/useAuth";
import { NavItem } from "./NavItem";
import { UserMenu } from "./UserMenu";
import { MobileNav } from "./MobileNav";
import { navItems } from "./navConfig";

const NavBar = () => {
  const location = useLocation();
  const isMobile = useIsMobile();
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleProfileClick = () => {
    navigate('/profile');
  };

  const handleLogout = () => {
    logout();
  };

  const handleUserManagementClick = () => {
    navigate('/users');
  };

  if (isMobile) {
    return (
      <div className="sticky top-0 z-50 bg-white shadow-sm">
        <div className="flex h-16 items-center justify-between px-4">
          <div className="flex items-center gap-2">
            <MobileNav 
              user={user} 
              onProfileClick={handleProfileClick} 
              onLogout={handleLogout} 
              onUserManagementClick={handleUserManagementClick} 
            />
            <h1 className="text-xl font-bold text-marina-800">Marina Power</h1>
          </div>
          <div className="flex items-center gap-2">
            <UserMenu 
              user={user} 
              onProfileClick={handleProfileClick} 
              onLogout={handleLogout} 
              onUserManagementClick={handleUserManagementClick} 
            />
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
              <NavItem
                key={item.path}
                path={item.path}
                name={item.name}
                icon={item.icon}
                isActive={location.pathname === item.path}
              />
            ))}
            {user?.role === 'admin' && (
              <NavItem
                path="/users"
                name="Benutzerverwaltung"
                icon={<Users className="h-5 w-5" />}
                isActive={location.pathname === '/users'}
              />
            )}
          </nav>
        </div>
        <div className="flex items-center gap-2">
          <UserMenu 
            user={user} 
            onProfileClick={handleProfileClick} 
            onLogout={handleLogout} 
            onUserManagementClick={handleUserManagementClick} 
          />
        </div>
      </div>
    </div>
  );
};

export default NavBar;
