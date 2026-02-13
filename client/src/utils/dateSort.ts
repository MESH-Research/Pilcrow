/**
 * Compare two date strings for sorting in descending order (newest first).
 */
export function compareDatesDesc(a: string | Date, b: string | Date): number {
  return new Date(b).getTime() - new Date(a).getTime()
}
