
import { useState, useEffect } from "react";
import { z } from "zod";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { initializeDbConnection, isConnected, closeDbConnection, DatabaseConfig as DbConfig, getDbConfig } from "@/services/dbService";

// Validierungsschema
const dbConfigSchema = z.object({
  host: z.string().min(1, { message: "Host ist erforderlich" }),
  port: z.coerce.number().int().min(1, { message: "Port muss eine gültige Zahl sein" }).max(65535),
  user: z.string().min(1, { message: "Benutzername ist erforderlich" }),
  password: z.string(),
  database: z.string().min(1, { message: "Datenbankname ist erforderlich" }),
});

type DbConfigFormValues = z.infer<typeof dbConfigSchema>;

export function DatabaseConfig() {
  const [connected, setConnected] = useState<boolean>(false);
  
  // Formular initialisieren
  const form = useForm<DbConfigFormValues>({
    resolver: zodResolver(dbConfigSchema),
    defaultValues: {
      host: "localhost",
      port: 3306,
      user: "",
      password: "",
      database: "marina_power",
    },
  });
  
  // Status beim Laden prüfen
  useEffect(() => {
    setConnected(isConnected());
    
    // Vorhandene Konfiguration laden
    const config = getDbConfig();
    if (config) {
      form.reset({
        host: config.host,
        port: config.port,
        user: config.user,
        password: config.password,
        database: config.database,
      });
    }
  }, [form]);
  
  // Verbindung herstellen
  const onSubmit = async (values: DbConfigFormValues) => {
    const success = await initializeDbConnection(values as DbConfig);
    setConnected(success);
  };
  
  // Verbindung trennen
  const handleDisconnect = async () => {
    await closeDbConnection();
    setConnected(false);
  };
  
  return (
    <Card>
      <CardHeader>
        <CardTitle>Datenbankverbindung</CardTitle>
        <CardDescription>
          Verbindungseinstellungen für die MariaDB-Datenbank
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="host"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Host</FormLabel>
                    <FormControl>
                      <Input placeholder="localhost" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="port"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Port</FormLabel>
                    <FormControl>
                      <Input type="number" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="user"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Benutzername</FormLabel>
                    <FormControl>
                      <Input {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="password"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Passwort</FormLabel>
                    <FormControl>
                      <Input type="password" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            
            <FormField
              control={form.control}
              name="database"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Datenbankname</FormLabel>
                  <FormControl>
                    <Input {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            
            <div className="flex justify-end gap-4">
              {connected && (
                <Button
                  type="button"
                  variant="outline"
                  onClick={handleDisconnect}
                >
                  Verbindung trennen
                </Button>
              )}
              <Button
                type="submit"
                className="bg-marina-600 hover:bg-marina-700"
              >
                {connected ? "Verbindung aktualisieren" : "Verbindung herstellen"}
              </Button>
            </div>
          </form>
        </Form>
      </CardContent>
    </Card>
  );
}
