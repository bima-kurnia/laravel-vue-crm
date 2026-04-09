import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '@/api/useApi'

export const useDealStore = defineStore('deals', () => {
  const deals    = ref([])
  const current  = ref(null)
  const meta     = ref(null)
  const pipeline = ref(null)
  const loading  = ref(false)

  const api = useApi()

  async function fetchAll(params = {}) {
    loading.value = true

    const query = new URLSearchParams(
      Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v != null))
    ).toString()

    const { data, error } = await api.get(`/deals${query ? '?' + query : ''}`)

    if (data) {
      deals.value = data.data
      meta.value  = data.meta
    }

    loading.value = false

    return { data, error }
  }

  async function fetchPipeline() {
    const { data, error } = await api.get('/deals/pipeline')

    if (data) {
      pipeline.value = data.data
    }

    return { data, error }
  }

  async function fetchOne(id) {
    const { data, error } = await api.get(`/deals/${id}`)

    if (data) {
      current.value = data.data
    }

    return { data, error }
  }

  async function create(payload) {
    const { data, error } = await api.post('/deals', payload)

    if (data) {
      deals.value.unshift(data.data)

      // Update total data in view
      if (meta.value) {
        meta.value.total++
      }
    }

    return { data, error }
  }

  async function update(id, payload) {
    const { data, error } = await api.patch(`/deals/${id}`, payload)

    if (data) {
      const index = deals.value.findIndex(d => d.id === id)

      if (index !== -1) {
        deals.value[index] = data.data
      }

      if (current.value?.id === id) {
        current.value = data.data
      }
    }

    return { data, error }
  }

  async function moveStage(id, stage) {
    const { data, error } = await api.patch(`/deals/${id}/stage`, { stage })

    if (data) {
      const index = deals.value.findIndex(d => d.id === id)

      if (index !== -1) {
        deals.value[index] = data.data
      }

      if (current.value?.id === id) {
        current.value = data.data
      }
    }

    return { data, error }
  }

  async function remove(id) {
    const { data, error } = await api.delete(`/deals/${id}`)

    if (!error) {
      deals.value = deals.value.filter(d => d.id !== id)

      // Update total data in view
      if (meta.value) {
        meta.value.total--
      }
    }

    return { data, error }
  }

  async function restore(id) {
    const { data, error } = await api.patch(`/deals/${id}/restore`)

    if (!error) {
      deals.value = deals.value.filter(d => d.id !== id)

      // Update total data in view
      if (meta.value) {
        meta.value.total--
      }
    }

    return { data, error }
  }

  async function forceDelete(id) {
    const { data, error } = await api.delete(`/deals/${id}/force`)

    if (!error) {
      deals.value = deals.value.filter(d => d.id !== id)

      // Update total data in view
      if (meta.value) {
        meta.value.total--
      }
    }
    
    return { data, error }
  }

  return {
    deals,
    current,
    meta,
    pipeline,
    loading,
    fetchAll,
    fetchPipeline,
    fetchOne,
    create,
    update,
    moveStage,
    remove,
    restore, 
    forceDelete,
  }
})