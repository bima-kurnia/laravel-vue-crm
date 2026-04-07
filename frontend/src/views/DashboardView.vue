<template>
  <div>
    <PageHeader title="Dashboard" subtitle="Pipeline overview" />

    <div v-if="pipeline" class="pipeline-grid">
      <div
        v-for="(data, stage) in pipeline"
        :key="stage"
        class="pipeline-card"
      >
        <div class="pipeline-stage">{{ stage }}</div>
        <div class="pipeline-count">{{ data.total_count }}</div>
        <div class="pipeline-value">{{ formatCurrency(data.total_value) }}</div>
      </div>
    </div>

    <ProgressSpinner v-else />
  </div>
</template>

<script setup>
import { onMounted }      from 'vue'
import { storeToRefs }   from 'pinia'
import ProgressSpinner   from 'primevue/progressspinner'
import PageHeader        from '@/components/shared/PageHeader.vue'
import { useDealStore }  from '@/stores/deals'

const store    = useDealStore()
const { pipeline } = storeToRefs(store)

onMounted(() => store.fetchPipeline())

function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', notation: 'compact' }).format(value)
}
</script>

<style scoped>
.pipeline-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}
.pipeline-card {
  background: var(--p-surface-card);
  border: 1px solid var(--p-surface-border);
  border-radius: 10px;
  padding: 1.25rem 1.5rem;
}
.pipeline-stage {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--p-text-muted-color);
  margin-bottom: 0.5rem;
}
.pipeline-count {
  font-size: 2rem;
  font-weight: 700;
  color: var(--p-text-color);
  line-height: 1;
}
.pipeline-value {
  font-size: 0.9rem;
  color: var(--p-primary-color);
  margin-top: 0.25rem;
}
</style>