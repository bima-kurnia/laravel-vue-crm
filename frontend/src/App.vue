<template>
  <Toast position="top-right" />

  <ConfirmDialog />

  <ErrorBoundary>
    <RouterView />
  </ErrorBoundary>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { getCurrentInstance }     from 'vue'
import Toast                      from 'primevue/toast'
import ConfirmDialog              from 'primevue/confirmdialog'
import ErrorBoundary              from '@/components/shared/ErrorBoundary.vue'

const instance = getCurrentInstance()

// -------------------------------------------------------------------------
// Vue component tree errors
// Catches errors thrown inside setup(), lifecycle hooks, template
// expressions, and event handlers within any component.
// -------------------------------------------------------------------------
instance.appContext.app.config.errorHandler = (err, vm, info) => {
  console.error('[Vue error]', info, err)
  // ErrorBoundary.onErrorCaptured handles the UI fallback —
  // this handler just ensures non-component errors are also captured.
}

// -------------------------------------------------------------------------
// Outside-Vue errors — async store calls, raw fetch, setTimeout callbacks
// that throw outside a component's lifecycle
// -------------------------------------------------------------------------
function onUnhandledError(event) {
  console.error('[Unhandled error]', event.error ?? event.reason)
  // We do not push these into ErrorBoundary because they originate
  // outside the component tree and cannot be cleanly recovered.
  // Logging them here is sufficient — add your error tracking SDK
  // (Sentry, Highlight, etc.) call here if needed.
}

onMounted(() => {
  window.addEventListener('error', onUnhandledError)
  window.addEventListener('unhandledrejection', onUnhandledError)
})

onUnmounted(() => {
  window.removeEventListener('error', onUnhandledError)
  window.removeEventListener('unhandledrejection', onUnhandledError)
})
</script>