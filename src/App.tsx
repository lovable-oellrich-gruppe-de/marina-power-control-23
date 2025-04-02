
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import { useAuth } from "@/hooks/useAuth";
import ProtectedRoute from "@/components/auth/ProtectedRoute";

import Index from "./pages/Index";
import MieterPage from "./pages/Mieter";
import SteckdosenPage from "./pages/Steckdosen";
import ZaehlerPage from "./pages/Zaehler";
import BereichePage from "./pages/Bereiche";
import ZaehlerstaendePage from "./pages/Zaehlerstaende";
import UserManagementPage from "./pages/UserManagement";
import NotFound from "./pages/NotFound";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Profile from "./pages/Profile";

const queryClient = new QueryClient();

// Komponente zur Weiterleitung basierend auf Auth-Status
const RedirectBasedOnAuth = () => {
  const { isAuthenticated } = useAuth();
  
  if (isAuthenticated) {
    return <Navigate to="/" replace />;
  }
  
  return <Navigate to="/login" replace />;
};

const AppRoutes = () => {
  return (
    <Routes>
      {/* Öffentliche Routen */}
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />
      
      {/* Geschützte Routen */}
      <Route path="/" element={
        <ProtectedRoute>
          <Index />
        </ProtectedRoute>
      } />
      <Route path="/mieter" element={
        <ProtectedRoute>
          <MieterPage />
        </ProtectedRoute>
      } />
      <Route path="/steckdosen" element={
        <ProtectedRoute>
          <SteckdosenPage />
        </ProtectedRoute>
      } />
      <Route path="/zaehler" element={
        <ProtectedRoute>
          <ZaehlerPage />
        </ProtectedRoute>
      } />
      <Route path="/bereiche" element={
        <ProtectedRoute>
          <BereichePage />
        </ProtectedRoute>
      } />
      <Route path="/zaehlerstaende" element={
        <ProtectedRoute>
          <ZaehlerstaendePage />
        </ProtectedRoute>
      } />
      <Route path="/profile" element={
        <ProtectedRoute>
          <Profile />
        </ProtectedRoute>
      } />
      <Route path="/users" element={
        <ProtectedRoute requiredRole="admin">
          <UserManagementPage />
        </ProtectedRoute>
      } />
      
      {/* Fallback-Routen */}
      <Route path="*" element={<NotFound />} />
    </Routes>
  );
};

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <BrowserRouter>
        <AuthProvider>
          <AppRoutes />
        </AuthProvider>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
