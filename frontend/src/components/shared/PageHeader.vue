<template>
  <div class="page-header">
    <div>
      <h1 class="page-header-title">{{ title }}</h1>
      <p v-if="subtitle" class="page-header-subtitle">{{ subtitle }}</p>
    </div>
    <div class="page-header-actions">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title:    { type: String, required: true },
  subtitle: { type: String, default: null },
})
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}
.page-header-title {
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0;
  color: var(--p-text-color);
}
.page-header-subtitle {
  font-size: 0.875rem;
  color: var(--p-text-muted-color);
  margin: 0.25rem 0 0;
}
.page-header-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}
</style>