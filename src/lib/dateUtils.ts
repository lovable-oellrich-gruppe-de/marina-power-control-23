
/**
 * Formatiert ein Datum von ISO-String in deutsches Format
 */
export const formatDateString = (dateString: string): string => {
  if (!dateString) return "-";
  
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('de-DE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  } catch (error) {
    console.error("Fehler beim Formatieren des Datums:", error);
    return dateString; // Fallback auf den ursprünglichen String
  }
};

/**
 * Wandelt ein Datum in ISO-Format (YYYY-MM-DD) um
 */
export const toISODateString = (date: Date): string => {
  return date.toISOString().split('T')[0];
};
