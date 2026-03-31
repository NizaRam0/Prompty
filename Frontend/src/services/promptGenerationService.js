// Service layer for prompt-generation features only (upload and history).
import { apiDelete, apiGet, apiPostForm } from './apiClient'
import { API_BASE_URL } from '../utils/constants'

function getApiOrigin() {
  try {
    return new URL(API_BASE_URL).origin
  } catch {
    return ''
  }
}

// Handles absolute, relative, localhost, and legacy filename-only image URLs.
function normalizeImageUrl(rawValue) {
  if (!rawValue) return null

  const value = String(rawValue).trim()
  if (!value) return null

  const apiOrigin = getApiOrigin()

  if (value.startsWith('/')) {
    return apiOrigin ? `${apiOrigin}${value}` : value
  }

  try {
    const parsed = new URL(value)

    // Repoint localhost URLs to the configured API host in cloud/mobile setups.
    if ((parsed.hostname === 'localhost' || parsed.hostname === '127.0.0.1') && apiOrigin) {
      return `${apiOrigin}${parsed.pathname}${parsed.search}`
    }

    return parsed.toString()
  } catch {
    if (!apiOrigin) return value

    const cleaned = value.replace(/^\/+/, '')

    if (cleaned.startsWith('storage/')) {
      return `${apiOrigin}/${cleaned}`
    }

    if (cleaned.startsWith('uploads/')) {
      return `${apiOrigin}/storage/${cleaned}`
    }

    return `${apiOrigin}/storage/uploads/images/${cleaned}`
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
    ? response.data.map((item) => normalizeItem(item))
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

// Deletes one prompt-generation history item by id.
export async function deletePromptHistoryItem(id) {
  if (!id) {
    throw new Error('Missing history item id.')
  }

  return apiDelete(`/prompt-generations/${id}`)
}
