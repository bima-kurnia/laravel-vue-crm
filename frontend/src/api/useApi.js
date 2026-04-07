import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api'

/**
 * Normalise error responses into a consistent shape:
 * { message: string, errors: Record<string, string[]> | null }
 */
async function parseError(response) {
  try {
    const body = await response.json()

    return {
      message: body.message ?? 'An unexpected error occurred.',
      errors:  body.errors  ?? null,
    }
  } catch {
    return { message: 'An unexpected error occurred.', errors: null }
  }
}

/**
 * Core fetch wrapper.
 *
 * - Attaches Bearer token from auth store on every request
 * - Globally handles 401 → clears auth + redirects to /login
 * - Returns { data, error } — never throws
 */
export function useApi() {
  function getHeaders(extra = {}) {
    const auth = useAuthStore()

    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...extra,
    }

    if (auth.token) {
      headers['Authorization'] = `Bearer ${auth.token}`
    }

    return headers
  }

  async function request(method, path, body = null) {
    const options = {
      method,
      headers: getHeaders(),
    }

    if (body !== null) {
      options.body = JSON.stringify(body)
    }

    let response

    try {
      response = await fetch(`${BASE_URL}${path}`, options)
    } catch {
      return {
        data:  null,
        error: { message: 'Network error. Please check your connection.', errors: null },
      }
    }

    // Global 401 handler — token expired or revoked
    if (response.status === 401) {
      const auth = useAuthStore()
      auth.clearSession()
      router.push({ name: 'login' })

      return {
        data:  null,
        error: { message: 'Session expired. Please log in again.', errors: null },
      }
    }

    if (!response.ok) {
      const error = await parseError(response)

      return { data: null, error }
    }

    // 204 No Content
    if (response.status === 204) {
      return { data: null, error: null }
    }

    try {
      const data = await response.json()

      return { data, error: null }
    } catch {
      return { data: null, error: { message: 'Failed to parse server response.', errors: null } }
    }
  }

  return {
    get:    (path)        => request('GET',    path),
    post:   (path, body)  => request('POST',   path, body),
    patch:  (path, body)  => request('PATCH',  path, body),
    delete: (path)        => request('DELETE', path),
  }
}