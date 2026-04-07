<template>
  <div class="app-shell">
    <AppSidebar />

    <div class="app-main">
      <AppTopbar />

      <main class="app-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import AppSidebar from './AppSidebar.vue'
import AppTopbar  from './AppTopbar.vue'
</script>

<style scoped>
.app-shell {
  display: flex;
  min-height: 100vh;
  background: var(--p-surface-ground);
}

.app-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.app-content {
  flex: 1;
  padding: 1.5rem 2rem;
}
</style>