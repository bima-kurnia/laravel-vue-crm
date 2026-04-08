<template>
  <slot v-if="!error" />

  <div v-else class="error-boundary">
    <div class="error-card">
      <div class="error-icon">
        <i class="pi pi-exclamation-circle" />
      </div>

      <h2 class="error-title">Something went wrong</h2>

      <p class="error-description">
        An unexpected error occurred. If this keeps happening, please
        contact support.
      </p>

      <code v-if="showDetail" class="error-detail">{{ error.message }}</code>

      <div class="error-actions">
        <Button
          label="Try again"
          icon="pi pi-refresh"
          @click="reset"
        />
        <Button
          label="Go to dashboard"
          icon="pi pi-home"
          severity="secondary"
          text
          @click="goHome"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onErrorCaptured } from 'vue'
import { useRouter }            from 'vue-router'
import Button                   from 'primevue/button'

const router = useRouter()
const error  = ref(null)

// Show raw error message in non-production environments only
const showDetail = import.meta.env.DEV

onErrorCaptured((err) => {
  error.value = err

  // Return false to stop the error propagating further up the tree.
  // Without this, Vue also logs it to the console as an unhandled error.
  return false
})

function reset() {
  error.value = null
}

function goHome() {
  error.value = null
  
  // router.push({ name: 'dashboard' }) doesn't work.
  window.location.href = '/dashboard' 
}
</script>

<style scoped>
.error-boundary {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--p-surface-ground);
  padding: 2rem;
}
.error-card {
  background: var(--p-surface-card);
  border: 1px solid var(--p-surface-border);
  border-radius: 12px;
  padding: 2.5rem 2rem;
  max-width: 440px;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.75rem;
}
.error-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--p-red-50);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.25rem;
}
.error-icon i {
  font-size: 1.5rem;
  color: var(--p-red-500);
}
.error-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--p-text-color);
}
.error-description {
  margin: 0;
  font-size: 0.875rem;
  color: var(--p-text-muted-color);
  line-height: 1.6;
}
.error-detail {
  font-family: monospace;
  font-size: 0.78rem;
  background: var(--p-surface-hover);
  border: 1px solid var(--p-surface-border);
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
  color: var(--p-red-600);
  word-break: break-word;
  text-align: left;
  width: 100%;
}
.error-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
  flex-wrap: wrap;
  justify-content: center;
}
</style>