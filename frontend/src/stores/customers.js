import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '@/api/useApi'

export const useCustomerStore = defineStore('customers', () => {
  const customers = ref([])
  const current   = ref(null)
  const meta      = ref(null) // pagination meta
  const loading   = ref(false)

  const api = useApi()

  async function fetchAll(params = {}) {
    loading.value = true

    const query = new URLSearchParams(
      Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v != null))
    ).toString()

    const { data, error } = await api.get(`/customers${query ? '?' + query : ''}`)

    if (data) {
      customers.value = data.data
      meta.value      = data.meta
    }

    loading.value = false

    return { data, error }
  }

  async function fetchOne(id) {
    const { data, error } = await api.get(`/customers/${id}`)

    if (data) {
      current.value = data.data
    }

    return { data, error }
  }

  async function create(payload) {
    const { data, error } = await api.post('/customers', payload)

    if (data) {
      customers.value.unshift(data.data)

      // Update total data in view
      if (meta.value) {
        meta.value.total++
      }
    }

    return { data, error }
  }

  async function update(id, payload) {
    const { data, error } = await api.patch(`/customers/${id}`, payload)

    if (data) {
      const index = customers.value.findIndex(c => c.id === id)

      if (index !== -1) {
        customers.value[index] = data.data
      }

      if (current.value?.id === id) {
        current.value = data.data
      }
    }
    return { data, error }
  }

  async function remove(id) {
    const { data, error } = await api.delete(`/customers/${id}`)

    if (!error) {
      customers.value = customers.value.filter(c => c.id !== id)

      // Update total data in view
      if (meta.value) {
        meta.value.total--
      }
    }

    return { data, error }
  }

  return {
    customers,
    current,
    meta,
    loading,
    fetchAll,
    fetchOne,
    create,
    update,
    remove,
  }
})