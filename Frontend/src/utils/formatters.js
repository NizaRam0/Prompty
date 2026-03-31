// Formatting helpers used by multiple UI components.

// Converts bytes to readable labels like KB/MB so history rows are easier to scan.
export function formatBytes(bytes) {
  const valueInBytes = Number(bytes)
  if (!Number.isFinite(valueInBytes) || valueInBytes <= 0) return '0 B'

  const units = ['B', 'KB', 'MB', 'GB']
  const unitIndex = Math.min(Math.floor(Math.log(valueInBytes) / Math.log(1024)), units.length - 1)
  const value = valueInBytes / (1024 ** unitIndex)
  return `${value.toFixed(unitIndex === 0 ? 0 : 2)} ${units[unitIndex]}`
}

// Formats backend datetime strings to locale-aware date/time for user-friendly display.
export function formatDateTime(value) {
  if (!value) return 'N/A'

  const parsed = new Date(value.replace(' ', 'T'))
  if (Number.isNaN(parsed.getTime())) return value

  return parsed.toLocaleString()
}
