
// Datumsformatierungsfunktionen

// Formatiert ein ISO-Datum in deutsches Format (DD.MM.YYYY)
export const formatDateString = (dateString?: string): string => {
  if (!dateString) return '-';
  
  try {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('de-DE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    }).format(date);
  } catch (error) {
    console.error('Datum konnte nicht formatiert werden:', dateString, error);
    return dateString;
  }
};

// Konvertiert ein Date-Objekt in einen ISO-Datumsstring (YYYY-MM-DD)
export const toISODateString = (date: Date): string => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  
  return `${year}-${month}-${day}`;
};

// Formatiert einen ISO-Datumsstring mit Uhrzeit (DD.MM.YYYY, HH:MM)
export const formatDateTimeString = (dateTimeString?: string): string => {
  if (!dateTimeString) return '-';
  
  try {
    const date = new Date(dateTimeString);
    return new Intl.DateTimeFormat('de-DE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date);
  } catch (error) {
    console.error('Datum und Uhrzeit konnten nicht formatiert werden:', dateTimeString, error);
    return dateTimeString;
  }
};
