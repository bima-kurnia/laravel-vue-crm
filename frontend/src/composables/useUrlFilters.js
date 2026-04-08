import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useDebounceFn } from '@vueuse/core'

/**
 * Syncs a set of filter keys to/from the URL query string.
 *
 * Usage:
 *   const { filters, setFilter, resetFilters } = useUrlFilters({
 *     search: '',
 *     status: null,
 *     page:   1,
 *   })
 *
 * - filters        reactive object reading from current route.query
 * - setFilter      update one key (resets page to 1 unless key === 'page')
 * - resetFilters   clear all keys back to defaults
 * - toApiParams    returns plain object with null/empty values stripped
 */
export function useUrlFilters(defaults = {}) {
  const route  = useRoute()
  const router = useRouter()

  // Read a single value from the query, coercing to the default's type
  function read(key) {
    const raw = route.query[key]
    const def = defaults[key]

    if (raw === undefined || raw === '') return def

    if (typeof def === 'number')  return Number(raw)
    if (typeof def === 'boolean') return raw === 'true'
    return raw
  }

  // Reactive proxy — always reflects the current URL
  const filters = new Proxy({}, {
    get(_, key) {
      return read(key)
    },
    set(_, key, value) {
      setFilter(key, value)
      return true
    },
    ownKeys() {
      return Object.keys(defaults)
    },
    getOwnPropertyDescriptor(_, key) {
      return { enumerable: true, configurable: true }
    },
  })

  function buildQuery(overrides = {}) {
    const next = {}

    for (const key of Object.keys(defaults)) {
      const value = key in overrides ? overrides[key] : read(key)
      const def   = defaults[key]

      // Omit values that match the default — keeps URLs clean
      if (value === def || value === '' || value === null || value === undefined) {
        continue
      }

      next[key] = String(value)
    }

    return next
  }

  function setFilter(key, value) {
    const overrides = { [key]: value }

    // Any filter change except explicit page navigation resets to page 1
    if (key !== 'page') {
      overrides.page = 1
    }

    router.replace({ query: buildQuery(overrides) })
  }

  function resetFilters() {
    router.replace({ query: {} })
  }

  // Returns a plain object suitable for passing to store.fetchAll()
  // Strips keys that are at their default value
  const toApiParams = computed(() => {
    const params = {}
    for (const key of Object.keys(defaults)) {
      const value = read(key)
      if (value !== null && value !== '' && value !== undefined) {
        params[key] = value
      }
    }
    return params
  })

  return { filters, setFilter, resetFilters, toApiParams }
}