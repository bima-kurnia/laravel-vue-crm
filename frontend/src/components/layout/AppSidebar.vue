<template>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <span class="logo-text">CRM Web</span>
    </div>

    <nav class="sidebar-nav">
      <RouterLink
        v-for="item in navItems"
        :key="item.name"
        :to="{ name: item.name }"
        class="nav-item"
        :class="{ 
          'nav-item--active': item.exact 
            ? $route.name === item.name 
            : $route.path.startsWith(`/${item.name}`)
        }"
      >
        <i :class="item.icon" />
        <span>{{ item.label }}</span>
      </RouterLink>
    </nav>
  </aside>
</template>

<script setup>
import { useRole } from '@/composables/useRole'

const { isOwnerOrAdmin } = useRole()

const navItems = [
  { name: 'dashboard',     label: 'Dashboard',  icon: 'pi pi-home', exact: true },
  { name: 'customers',     label: 'Customers',  icon: 'pi pi-users' },
  { name: 'deals',         label: 'Deals',      icon: 'pi pi-briefcase' },
  { name: 'activities',    label: 'Activities', icon: 'pi pi-list' },

  ...(isOwnerOrAdmin.value
    ? [{ name: 'settings-team', label: 'Team', icon: 'pi pi-user-plus', exact: true }]
    : []),
]
</script>

<style scoped>
.sidebar {
  width: 220px;
  background: var(--p-surface-card);
  border-right: 1px solid var(--p-surface-border);
  display: flex;
  flex-direction: column;
  padding: 0;
  flex-shrink: 0;
}

.sidebar-logo {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--p-surface-border);
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--p-primary-color);
  letter-spacing: 0.05em;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  padding: 0.75rem 0.5rem;
  gap: 0.25rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 1rem;
  border-radius: 8px;
  color: var(--p-text-color);
  text-decoration: none;
  font-size: 0.9rem;
  transition: background 0.15s;
}

.nav-item:hover {
  background: var(--p-surface-hover);
}

.nav-item--active {
  background: var(--p-primary-50);
  color: var(--p-primary-color);
  font-weight: 600;
}
</style>