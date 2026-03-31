// Service layer for prompt-generation features only (upload and history).
import { apiGet, apiPostForm } from './apiClient'
import { API_ROOT_URL } from '../utils/constants'

function normalizeImageUrl(imageUrl) {
  if (!imageUrl) return null

  const raw = String(imageUrl).trim()
  if (!raw) return null

  let apiOrigin = ''
  try {
    apiOrigin = new URL(API_ROOT_URL).origin
  } catch {
    apiOrigin = ''
  }

  // Handle relative URLs like /storage/uploads/... from backend resources.
  if (raw.startsWith('/')) {
    return apiOrigin ? `${apiOrigin}${raw}` : raw
  }

  try {
    const parsed = new URL(raw)

    // Signed URLs must remain unchanged or Laravel signature validation fails.
    if (parsed.searchParams.has('signature') && parsed.searchParams.has('expires')) {
      return parsed.toString()
    }

    // If backend returns localhost but app is not running on that same origin,
    // rebuild URL from configured API origin so preview stays reachable.
    const isLocalHost = parsed.hostname === 'localhost' || parsed.hostname === '127.0.0.1'
    if (isLocalHost && apiOrigin) {
      return `${apiOrigin}${parsed.pathname}${parsed.search}`
    }

    return parsed.toString()
  } catch {
    return raw
  }
}

function normalizeItem(item) {
  if (!item || typeof item !== 'object') return item

  return {
    ...item,
    image_url: normalizeImageUrl(item.image_url),
  }
}

// Uploads one image and returns normalized generated item payload.
export async function uploadImageAndGeneratePrompt(file) {
  const formData = new FormData()
  formData.append('image', file)

  const response = await apiPostForm('/prompt-generations', formData)

  // API may return wrapped or direct payload depending on resource formatting.
  return normalizeItem(response?.data || response)
}

// Sort function applied client-side so UI can provide sorting without backend dependency.
function sortItems(items, sortBy, sortDirection) {
  const sorted = [...items]
  const dir = sortDirection === 'asc' ? 1 : -1

  sorted.sort((a, b) => {
    const aValue = a?.[sortBy]
    const bValue = b?.[sortBy]

    if (sortBy === 'created_at') {
      const aTime = new Date((aValue || '').replace(' ', 'T')).getTime() || 0
      const bTime = new Date((bValue || '').replace(' ', 'T')).getTime() || 0
      return (aTime - bTime) * dir
    }

    if (sortBy === 'file_size') {
      return ((Number(aValue) || 0) - (Number(bValue) || 0)) * dir
    }

    return String(aValue || '').localeCompare(String(bValue || '')) * dir
  })

  return sorted
}

// Fetches paginated history from backend and normalizes pagination shape for UI.
export async function fetchPromptHistory({ page, perPage, search, mimeType, sortBy, sortDirection }) {
  const response = await apiGet('/prompt-generations', {
    page,
    per_page: perPage,
    search,
    mime_type: mimeType,
  })

  const items = Array.isArray(response?.data)
    ? response.data.map(normalizeItem)
    : []
  const sortedItems = sortItems(items, sortBy, sortDirection)

  return {
    items: sortedItems,
    currentPage: response?.meta?.current_page || page || 1,
    perPage: response?.meta?.per_page || perPage || 6,
    total: response?.meta?.total || sortedItems.length,
    lastPage: response?.meta?.last_page || 1,
  }
}
