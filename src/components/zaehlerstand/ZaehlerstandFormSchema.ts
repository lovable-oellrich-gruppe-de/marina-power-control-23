
import * as z from "zod";

// Schema für die Validierung des Formulars
export const zaehlerstandFormSchema = z.object({
  zaehlerId: z.number({
    required_error: "Bitte wählen Sie einen Zähler aus",
  }),
  steckdoseId: z.number().nullable().optional(),
  datum: z.string({
    required_error: "Bitte wählen Sie ein Datum aus",
  }),
  stand: z.coerce.number({
    required_error: "Bitte geben Sie den Zählerstand ein",
    invalid_type_error: "Zählerstand muss eine Zahl sein",
  }).min(0, "Zählerstand kann nicht negativ sein"),
  istAbgerechnet: z.boolean().default(false),
  hinweis: z.string().optional(),
  fotoUrl: z.string().nullable().optional(),
});

export type ZaehlerstandFormValues = z.infer<typeof zaehlerstandFormSchema>;
