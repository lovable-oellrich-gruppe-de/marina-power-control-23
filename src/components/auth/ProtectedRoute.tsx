
import { ReactNode } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '@/hooks/useAuth';

interface ProtectedRouteProps {
  children: ReactNode;
  requireAdmin?: boolean;
}

const ProtectedRoute = ({ children, requireAdmin = false }: ProtectedRouteProps) => {
  const { user, loading } = useAuth();
  
  if (loading) {
    // Zeige Ladeindikator während Auth-Status überprüft wird
    return (
      <div className="flex h-screen w-full items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-t-2 border-marina-600"></div>
      </div>
    );
  }
  
  // Wenn kein Benutzer angemeldet ist, zur Login-Seite weiterleiten
  if (!user) {
    return <Navigate to="/login" replace />;
  }
  
  // Wenn Administrator-Rechte erforderlich sind, aber der Benutzer kein Admin ist
  if (requireAdmin && user.role !== 'admin') {
    return <Navigate to="/" replace />;
  }
  
  // Ansonsten den geschützten Inhalt anzeigen
  return <>{children}</>;
};

export default ProtectedRoute;
