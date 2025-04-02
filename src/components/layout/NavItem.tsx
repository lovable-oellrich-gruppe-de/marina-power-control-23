
import { Link } from "react-router-dom";
import { ReactNode } from "react";

interface NavItemProps {
  path: string;
  name: string;
  icon: ReactNode;
  isActive: boolean;
  onClick?: () => void;
}

export const NavItem = ({ path, name, icon, isActive, onClick }: NavItemProps) => {
  const activeLink = "bg-marina-100 text-marina-800 font-medium";
  const inactiveLink = "text-foreground hover:bg-marina-50";

  return (
    <Link
      to={path}
      onClick={onClick}
      className={`flex items-center gap-2 rounded-md px-3 py-2 ${
        isActive ? activeLink : inactiveLink
      }`}
    >
      {icon}
      <span>{name}</span>
    </Link>
  );
};
