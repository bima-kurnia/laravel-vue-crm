import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApi } from '@/api/useApi'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref([])
  const meta          = ref(null)
  const loading       = ref(false)

  const api = useApi()

  const unreadCount = computed(() =>
    notifications.value.filter(n => !n.is_read).length
  )

  async function fetchAll() {
    loading.value = true

    const { data } = await api.get('/notifications')

    if (data) {
      notifications.value = data.data
      meta.value          = data.meta
    }

    loading.value = false
  }

  async function markRead(id) {
    await api.patch(`/notifications/${id}/read`)

    const n = notifications.value.find(n => n.id === id)

    if (n) {
      n.is_read = true
    }
  }

  async function markAllRead() {
    await api.patch('/notifications/read-all')

    notifications.value.forEach(n => { n.is_read = true })
  }

  return {
    notifications,
    meta,
    loading,
    unreadCount,
    fetchAll,
    markRead,
    markAllRead,
  }
})