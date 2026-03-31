// Generic API client for read-only consumption of existing backend endpoints.
import { API_BASE_URL } from '../utils/constants'

const STORAGE_KEY = 'chatyai_access_token'

// Loads persisted bearer token for authenticated calls.
export function getToken() {
  return localStorage.getItem(STORAGE_KEY) || ''
}

// Saves token to localStorage so user does not re-enter on every refresh.
export function setToken(token) {
  localStorage.setItem(STORAGE_KEY, token)
  window.dispatchEvent(new Event('auth-token-changed'))
}

// Clears token when user logs out or wants to reset auth state.
export function clearToken() {
  localStorage.removeItem(STORAGE_KEY)
  window.dispatchEvent(new Event('auth-token-changed'))
}

// Builds Authorization header only when token exists.
export function buildHeaders(customHeaders = {}) {
  const token = getToken()

  return {
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...customHeaders,
  }
}

// Converts query object into URLSearchParams while ignoring empty values.
function toSearchParams(query = {}) {
  const params = new URLSearchParams()

  Object.entries(query).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '') return
    params.set(key, String(value))
  })

  return params
}

// Creates a safe absolute URL from base + path and throws readable error when malformed.
export function buildApiUrl(path, query = {}, baseUrl = API_BASE_URL) {
  const normalizedBase = String(baseUrl || '').trim().replace(/\/+$/, '')
  const normalizedPath = String(path || '').startsWith('/') ? path : `/${path}`

  let url
  try {
    url = new URL(`${normalizedBase}${normalizedPath}`)
  } catch {
    throw {
      status: 0,
      message: 'Invalid API base URL configuration. Check VITE_API_BASE_URL.',
      payload: {},
    }
  }

  const params = toSearchParams(query)
  url.search = params.toString()
  return url.toString()
}

// Safely reads JSON bodies and tolerates empty or non-JSON responses.
export async function safeJson(response) {
  const contentType = response.headers.get('content-type') || ''
  if (!contentType.includes('application/json')) return null

  try {
    return await response.json()
  } catch {
    return null
  }
}

// Normalizes backend errors so UI can show clear messages.
export async function parseError(response) {
  const payload = (await safeJson(response)) || {}

  const validationErrors = payload?.errors
    ? Object.values(payload.errors).flat().join(' ')
    : ''

  return {
    status: response.status,
    message: validationErrors || payload?.message || 'Request failed. Please try again.',
    payload,
  }
}

// GET request helper for history/index endpoints.
export async function apiGet(path, query = {}) {
  const url = buildApiUrl(path, query)

  const response = await fetch(url, {
    method: 'GET',
    headers: buildHeaders(),
  })

  if (!response.ok) {
    throw await parseError(response)
  }

  return (await safeJson(response)) || {}
}

// POST helper for multipart file upload endpoint.
export async function apiPostForm(path, formData) {
  const url = buildApiUrl(path)

  const response = await fetch(url, {
    method: 'POST',
    headers: buildHeaders(),
    body: formData,
  })

  if (!response.ok) {
    throw await parseError(response)
  }

  return (await safeJson(response)) || {}
}

// POST helper for JSON auth endpoints such as login/register/logout.
export async function apiPostJson(path, body = {}, options = {}) {
  const url = buildApiUrl(path, {}, options.baseUrl)

  const response = await fetch(url, {
    method: 'POST',
    headers: buildHeaders({
      'Content-Type': 'application/json',
    }),
    body: JSON.stringify(body),
  })

  if (!response.ok) {
    throw await parseError(response)
  }

  return (await safeJson(response)) || {}
}

// DELETE helper for resource removal endpoints.
export async function apiDelete(path) {
  const url = buildApiUrl(path)

  const response = await fetch(url, {
    method: 'DELETE',
    headers: buildHeaders(),
  })

  if (!response.ok) {
    throw await parseError(response)
  }

  return (await safeJson(response)) || {}
}
