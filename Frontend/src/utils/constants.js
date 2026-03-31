// Shared constants used across pages/components to keep rules centralized and easy to update.

// Backend API base URL. You can override this via Vite env if needed.
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://prompty-main-d04zpb.laravel.cloud/api/v1'

// Root API URL used by auth endpoints that are not under /v1.
export const API_ROOT_URL = API_BASE_URL.replace(/\/v1\/?$/, '')

// Frontend validation: accepted image MIME types.
export const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/jpg']

// Frontend validation: max image size in bytes.
// Backend validation allows 10 MB.
export const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024

// Frontend validation: image dimensions aligned to backend rules.
export const MIN_IMAGE_WIDTH = 100
export const MIN_IMAGE_HEIGHT = 100
export const MAX_IMAGE_WIDTH = 10000
export const MAX_IMAGE_HEIGHT = 10000

// History UI options for per-page pagination control.
export const PER_PAGE_OPTIONS = [3, 6, 9, 12, 15]

// Sorting options are applied client-side after fetch to avoid requiring backend changes.
export const SORT_OPTIONS = [
  { label: 'Newest', value: 'created_at:desc' },
  { label: 'Oldest', value: 'created_at:asc' },
  { label: 'File Size High -> Low', value: 'file_size:desc' },
  { label: 'File Size Low -> High', value: 'file_size:asc' },
  { label: 'MIME A -> Z', value: 'mime_type:asc' },
  { label: 'MIME Z -> A', value: 'mime_type:desc' },
]
