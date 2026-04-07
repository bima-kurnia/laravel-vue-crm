<template>
  <div class="empty-state">
    <!-- Icon slot — caller provides a pi-* class or omits entirely -->
    <div v-if="icon" class="empty-icon">
      <i :class="icon" />
    </div>

    <p class="empty-title">{{ title }}</p>
    <p v-if="description" class="empty-description">{{ description }}</p>

    <!-- Optional CTA — fully slotted so callers control the button -->
    <div v-if="$slots.action" class="empty-action">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon:        { type: String, default: null },
  title:       { type: String, required: true },
  description: { type: String, default: null },
})
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 3.5rem 2rem;
  gap: 0.5rem;
}
.empty-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--p-surface-hover);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
}
.empty-icon i {
  font-size: 1.4rem;
  color: var(--p-text-muted-color);
}
.empty-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--p-text-color);
}
.empty-description {
  margin: 0;
  font-size: 0.85rem;
  color: var(--p-text-muted-color);
  max-width: 320px;
  line-height: 1.5;
}
.empty-action {
  margin-top: 0.75rem;
}
</style>