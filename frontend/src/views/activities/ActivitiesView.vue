<template>
  <div>
    <PageHeader title="Activity Log" subtitle="Immutable audit trail" />

    <div class="filters">
      <Select
        v-model="filters.subject_type"
        :options="typeOptions"
        option-label="label"
        option-value="value"
        placeholder="All types"
        show-clear
        @change="fetchActivities"
      />
      <Select
        v-model="filters.event"
        :options="eventOptions"
        option-label="label"
        option-value="value"
        placeholder="All events"
        show-clear
        @change="fetchActivities"
      />
    </div>

    <DataTable :value="activities" :loading="loading" striped-rows class="crm-table">
      <template #empty>
        <EmptyState
          icon="pi pi-list"
          title="No activity yet"
          description="Activity is recorded automatically when customers and deals are created or updated."
        />
      </template>

      <Column field="event" header="Event">
        <template #body="{ data }">
          <Tag :value="data.event" severity="secondary" />
        </template>
      </Column>
      <Column header="Subject">
        <template #body="{ data }">
          <span class="capitalize">{{ data.subject_type }}</span>
          <span class="subject-id"> #{{ data.subject_id.slice(0, 8) }}</span>
        </template>
      </Column>
      <Column header="Actor">
        <template #body="{ data }">{{ data.user?.name ?? 'System' }}</template>
      </Column>
      <Column header="When">
        <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
      </Column>
    </DataTable>

    <Paginator
      v-if="meta && meta.last_page > 1"
      :rows="meta.per_page"
      :total-records="meta.total"
      :first="(meta.current_page - 1) * meta.per_page"
      @page="onPage"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import DataTable  from 'primevue/datatable'
import Column     from 'primevue/column'
import Select     from 'primevue/select'
import Tag        from 'primevue/tag'
import Paginator  from 'primevue/paginator'
import PageHeader from '@/components/shared/PageHeader.vue'
import EmptyState from '@/components/shared/EmptyState.vue'
import { useApi } from '@/api/useApi'

const api = useApi()

const activities = ref([])
const meta       = ref(null)
const loading    = ref(false)
const filters    = reactive({ subject_type: null, event: null, page: 1 })

const typeOptions = [
  { label: 'Customer', value: 'customer' },
  { label: 'Deal',     value: 'deal'     },
]

const eventOptions = [
  { label: 'Created',       value: 'created'       },
  { label: 'Updated',       value: 'updated'       },
  { label: 'Deleted',       value: 'deleted'       },
  { label: 'Restored',      value: 'restored'      },
  { label: 'Stage Changed', value: 'stage_changed' },
]

onMounted(fetchActivities)

async function fetchActivities() {
  loading.value = true
  const params  = Object.fromEntries(
    Object.entries(filters).filter(([, v]) => v !== null && v !== '')
  )
  const query = new URLSearchParams(params).toString()
  const { data } = await api.get(`/activities${query ? '?' + query : ''}`)
  if (data) {
    activities.value = data.data
    meta.value       = data.meta
  }
  loading.value = false
}

async function onPage(event) {
  filters.page = event.page + 1
  await fetchActivities()
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString() : '—'
}
</script>

<style scoped>
.filters { display: flex; gap: 0.75rem; margin-bottom: 1rem; }
.crm-table { border-radius: 8px; overflow: hidden; }
.capitalize { text-transform: capitalize; }
.subject-id { color: var(--p-text-muted-color); font-size: 0.8rem; }
</style>