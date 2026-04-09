<template>
  <header class="topbar">
    <div class="topbar-left">
      <span class="page-title">{{ pageTitle }}</span>
    </div>
    <div class="topbar-right">
      <NotificationBell />

      <span class="user-name">{{ auth.user?.name }}</span>

      <Button
        label="Logout"
        icon="pi pi-sign-out"
        severity="secondary"
        text
        size="small"
        @click="handleLogout"
      />
    </div>
  </header>
</template>

<script setup>
import { computed }      from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button            from 'primevue/button'
import NotificationBell  from '@/components/notifications/NotificationBell.vue'
import { useAuthStore }  from '@/stores/auth'

const auth   = useAuthStore()
const route  = useRoute()
const router = useRouter()

const titleMap = {
  dashboard:       'Dashboard',
  customers:       'Customers',
  'customer-detail': 'Customer Detail',
  deals:           'Deals',
  'deal-detail':   'Deal Detail',
  activities:      'Activities',
}

const pageTitle = computed(() => titleMap[route.name] ?? '')

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 2rem;
  background: var(--p-surface-card);
  border-bottom: 1px solid var(--p-surface-border);
}

.page-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--p-text-color);
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-size: 0.875rem;
  color: var(--p-text-muted-color);
}
</style>