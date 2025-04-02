
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useAuth } from '@/hooks/useAuth';
import NavBar from '@/components/layout/NavBar';
import { Button } from '@/components/ui/button';
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { useToast } from '@/hooks/use-toast';

const profileSchema = z.object({
  name: z.string().min(2, 'Name muss mindestens 2 Zeichen lang sein'),
  email: z.string().email('Gültige E-Mail-Adresse erforderlich').optional(),
});

type ProfileFormValues = z.infer<typeof profileSchema>;

const Profile = () => {
  const { user } = useAuth();
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const form = useForm<ProfileFormValues>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      name: user?.name || '',
      email: user?.email || '',
    },
  });

  const onSubmit = async (data: ProfileFormValues) => {
    setIsSubmitting(true);
    try {
      // In einer echten Anwendung würde hier eine API-Anfrage stattfinden
      setTimeout(() => {
        toast({
          title: 'Profil aktualisiert',
          description: 'Ihre Profilinformationen wurden erfolgreich aktualisiert.',
        });
        setIsSubmitting(false);
      }, 1000);
    } catch (error) {
      console.error('Profile update error:', error);
      setIsSubmitting(false);
    }
  };

  if (!user) return null;

  return (
    <div className="flex min-h-screen flex-col">
      <NavBar />
      <main className="flex-1 p-4 md:p-6">
        <div className="mx-auto max-w-3xl space-y-6">
          <div>
            <h1 className="text-2xl font-bold">Mein Profil</h1>
            <p className="text-muted-foreground">Verwalten Sie Ihre Profilinformationen</p>
          </div>

          <div className="rounded-lg border p-6 shadow-sm">
            <Form {...form}>
              <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                <FormField
                  control={form.control}
                  name="name"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Name</FormLabel>
                      <FormControl>
                        <Input {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                
                <FormField
                  control={form.control}
                  name="email"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>E-Mail</FormLabel>
                      <FormControl>
                        <Input {...field} disabled />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                
                <div className="mt-6">
                  <Button
                    type="submit"
                    className="bg-marina-600 hover:bg-marina-700"
                    disabled={isSubmitting}
                  >
                    {isSubmitting ? 'Speichere...' : 'Änderungen speichern'}
                  </Button>
                </div>
              </form>
            </Form>
          </div>
          
          <div className="rounded-lg border p-6 shadow-sm">
            <h2 className="text-xl font-semibold">Kontoinformationen</h2>
            <div className="mt-4 space-y-2">
              <div>
                <span className="font-medium">Rolle:</span>{' '}
                <span className="capitalize">{user.role}</span>
              </div>
              <div>
                <span className="font-medium">Konto-ID:</span> {user.id}
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

export default Profile;
