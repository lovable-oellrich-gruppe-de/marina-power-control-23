
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Settings, Users, LogOut } from "lucide-react";
import { User } from "@/types";

interface UserMenuProps {
  user: User | null;
  onProfileClick: () => void;
  onLogout: () => void;
  onUserManagementClick: () => void;
}

export const UserMenu = ({ 
  user, 
  onProfileClick, 
  onLogout, 
  onUserManagementClick 
}: UserMenuProps) => {
  return (
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
        <DropdownMenuItem onClick={onProfileClick}>
          <Settings className="mr-2 h-4 w-4" />
          <span>Profil</span>
        </DropdownMenuItem>
        {user?.role === 'admin' && (
          <DropdownMenuItem onClick={onUserManagementClick}>
            <Users className="mr-2 h-4 w-4" />
            <span>Benutzerverwaltung</span>
          </DropdownMenuItem>
        )}
        <DropdownMenuSeparator />
        <DropdownMenuItem onClick={onLogout} className="text-destructive focus:text-destructive">
          <LogOut className="mr-2 h-4 w-4" />
          <span>Abmelden</span>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};
