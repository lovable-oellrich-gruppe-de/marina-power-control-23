
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { Toaster } from '@/components/ui/toaster';
import { AuthProvider } from '@/context/AuthContext';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

import Index from '@/pages/Index';
import Login from '@/pages/Login';
import Register from '@/pages/Register';
import Profile from '@/pages/Profile';
import Mieter from '@/pages/Mieter';
import Steckdosen from '@/pages/Steckdosen';
import Zaehler from '@/pages/Zaehler';
import Zaehlerstaende from '@/pages/Zaehlerstaende';
import Bereiche from '@/pages/Bereiche';
import UserManagement from '@/pages/UserManagement';
import Settings from '@/pages/Settings';
import NotFound from '@/pages/NotFound';

function App() {
  return (
    <Router>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <Index />
              </ProtectedRoute>
            }
          />
          <Route
            path="/profile"
            element={
              <ProtectedRoute>
                <Profile />
              </ProtectedRoute>
            }
          />
          <Route
            path="/mieter"
            element={
              <ProtectedRoute>
                <Mieter />
              </ProtectedRoute>
            }
          />
          <Route
            path="/steckdosen"
            element={
              <ProtectedRoute>
                <Steckdosen />
              </ProtectedRoute>
            }
          />
          <Route
            path="/zaehler"
            element={
              <ProtectedRoute>
                <Zaehler />
              </ProtectedRoute>
            }
          />
          <Route
            path="/zaehlerstaende"
            element={
              <ProtectedRoute>
                <Zaehlerstaende />
              </ProtectedRoute>
            }
          />
          <Route
            path="/bereiche"
            element={
              <ProtectedRoute>
                <Bereiche />
              </ProtectedRoute>
            }
          />
          <Route
            path="/users"
            element={
              <ProtectedRoute requireAdmin>
                <UserManagement />
              </ProtectedRoute>
            }
          />
          <Route
            path="/settings"
            element={
              <ProtectedRoute>
                <Settings />
              </ProtectedRoute>
            }
          />
          <Route path="*" element={<NotFound />} />
        </Routes>
      </AuthProvider>
      <Toaster />
    </Router>
  );
}

export default App;
