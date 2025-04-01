
import { useEffect, useState } from "react";
import { 
  Card, 
  CardContent, 
  CardFooter, 
  CardHeader, 
  CardTitle 
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Power, Users, Gauge, MapPin, Zap, ArrowRight } from "lucide-react";
import NavBar from "@/components/layout/NavBar";
import { Link } from "react-router-dom";

interface DashboardStat {
  title: string;
  value: number;
  icon: React.ReactNode;
  link: string;
  color: string;
}

const Dashboard = () => {
  const [stats, setStats] = useState<DashboardStat[]>([
    {
      title: "Mieter",
      value: 0,
      icon: <Users className="h-8 w-8" />,
      link: "/mieter",
      color: "bg-blue-50 text-blue-700",
    },
    {
      title: "Steckdosen",
      value: 0,
      icon: <Power className="h-8 w-8" />,
      link: "/steckdosen",
      color: "bg-green-50 text-green-700",
    },
    {
      title: "Zähler",
      value: 0,
      icon: <Gauge className="h-8 w-8" />,
      link: "/zaehler",
      color: "bg-amber-50 text-amber-700",
    },
    {
      title: "Bereiche",
      value: 0,
      icon: <MapPin className="h-8 w-8" />,
      link: "/bereiche",
      color: "bg-purple-50 text-purple-700",
    },
    {
      title: "Zählerstände",
      value: 0,
      icon: <Zap className="h-8 w-8" />,
      link: "/zaehlerstaende",
      color: "bg-red-50 text-red-700",
    },
  ]);

  // Simuliere API-Daten für das Dashboard
  useEffect(() => {
    // Hier würden wir echte API-Aufrufe machen
    setTimeout(() => {
      setStats([
        { ...stats[0], value: 25 },
        { ...stats[1], value: 48 },
        { ...stats[2], value: 32 },
        { ...stats[3], value: 6 },
        { ...stats[4], value: 156 },
      ]);
    }, 1000);
  }, []);

  return (
    <div className="min-h-screen bg-gray-50">
      <NavBar />
      <main className="container py-6">
        <h1 className="text-3xl font-bold text-marina-800 mb-6">Dashboard</h1>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {stats.map((stat, index) => (
            <Card key={index} className="overflow-hidden">
              <CardHeader className={`${stat.color} py-4`}>
                <div className="flex items-center justify-between">
                  <CardTitle className="text-lg">{stat.title}</CardTitle>
                  {stat.icon}
                </div>
              </CardHeader>
              <CardContent className="pt-6">
                <p className="text-4xl font-bold">{stat.value}</p>
              </CardContent>
              <CardFooter className="border-t pt-4 pb-4">
                <Link to={stat.link} className="w-full">
                  <Button variant="outline" className="w-full">
                    Details anzeigen
                    <ArrowRight className="h-4 w-4 ml-2" />
                  </Button>
                </Link>
              </CardFooter>
            </Card>
          ))}
        </div>

        <div className="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Letzte Zählerablesungen</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">
                Hier werden die letzten Zählerablesungen angezeigt.
              </p>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader>
              <CardTitle>Nicht zugewiesene Steckdosen</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">
                Hier werden Steckdosen angezeigt, die noch nicht zugewiesen sind.
              </p>
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  );
};

export default Dashboard;
