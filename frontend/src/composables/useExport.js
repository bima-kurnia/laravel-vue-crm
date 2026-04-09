import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api'

export function useExport() {
  const exporting = ref(false)

  async function download(path, filename, params = {}) {
    exporting.value = true

    const auth  = useAuthStore()
    const query = new URLSearchParams(
      Object.fromEntries(
        Object.entries(params).filter(([, v]) => v !== null && v !== '' && v !== undefined)
      )
    ).toString()

    try {
      const response = await fetch(
        `${BASE_URL}${path}${query ? '?' + query : ''}`,
        {
          headers: {
            Authorization: `Bearer ${auth.token}`,
            Accept:        'text/csv',
          },
        }
      )

      if (!response.ok) {
        throw new Error(`Export failed: ${response.status}`)
      }

      // Stream the blob and trigger a browser download
      const blob = await response.blob()
      const url  = URL.createObjectURL(blob)
      const a    = document.createElement('a')

      a.href     = url
      a.download = filename
      a.click()
      
      URL.revokeObjectURL(url)
    } finally {
      exporting.value = false
    }
  }

  function exportCustomers(params = {}) {
    const date = new Date().toISOString().split('T')[0]

    return download('/customers/export', `customers-${date}.csv`, params)
  }

  function exportDeals(params = {}) {
    const date = new Date().toISOString().split('T')[0]

    return download('/deals/export', `deals-${date}.csv`, params)
  }

  return { exporting, exportCustomers, exportDeals }
}