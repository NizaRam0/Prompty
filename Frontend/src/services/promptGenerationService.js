// Service layer for prompt-generation features only (upload and history).
import { apiGet, apiPostForm } from './apiClient'

// Uploads one image and returns normalized generated item payload.
export async function uploadImageAndGeneratePrompt(file) {
  const formData = new FormData()
  formData.append('image', file)

  const response = await apiPostForm('/prompt-generations', formData)

  // API may return wrapped or direct payload depending on resource formatting.
  return response?.data || response
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

  const items = Array.isArray(response?.data) ? response.data : []
  const sortedItems = sortItems(items, sortBy, sortDirection)

  return {
    items: sortedItems,
    currentPage: response?.meta?.current_page || page || 1,
    perPage: response?.meta?.per_page || perPage || 6,
    total: response?.meta?.total || sortedItems.length,
    lastPage: response?.meta?.last_page || 1,
  }
}
