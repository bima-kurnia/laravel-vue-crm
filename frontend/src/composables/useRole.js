import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useRole() {
  const auth = useAuthStore()

  const role           = computed(() => auth.user?.role?.toLowerCase()  ?? 'member')
  const isOwner        = computed(() => role.value === 'owner')
  const isAdmin        = computed(() => role.value === 'admin')
  const isOwnerOrAdmin = computed(() => ['owner', 'admin'].includes(role.value))

  return { role, isOwner, isAdmin, isOwnerOrAdmin }
}