<template>
  <div v-if="deal">
    <PageHeader :title="deal.title" :subtitle="`${deal.customer?.name} · ${formatCurrency(deal.value, deal.currency)}`">
      <template #actions>
        <Button label="Edit" icon="pi pi-pencil" @click="dialogVisible = true" />
      </template>
    </PageHeader>

    <div class="detail-grid">
      <div class="detail-card">
        <h3 class="card-title">Deal Info</h3>
        <dl class="info-list">
          <div class="info-row"><dt>Status</dt><dd><Tag :value="deal.status" :severity="statusSeverity(deal.status)" /></dd></div>
          <div class="info-row"><dt>Stage</dt><dd><DealStageBadge :stage="deal.stage" /></dd></div>
          <div class="info-row"><dt>Owner</dt><dd>{{ deal.owner?.name ?? '—' }}</dd></div>
          <div class="info-row"><dt>Close Date</dt><dd>{{ deal.expected_close_date ?? '—' }}</dd></div>
        </dl>

        <h3 class="card-title" style="margin-top:1.5rem">Move Stage</h3>
        <div class="stage-buttons">
          <Button
            v-for="s in stages"
            :key="s.value"
            :label="s.label"
            :outlined="deal.stage !== s.value"
            :disabled="deal.stage === s.value || movingStage"
            size="small"
            @click="promptMoveStage(s)"
          />
        </div>
      </div>

      <!-- Stage history -->
      <div class="detail-card">
        <h3 class="card-title">Stage History</h3>

        <EmptyState
          v-if="stageHistory.length === 0"
          icon="pi pi-arrow-right-arrow-left"
          title="No stage changes yet"
          description="Stage moves will be recorded here as the deal progresses."
        />

        <ul v-else class="activity-feed">
          <li v-for="a in stageHistory" :key="a.id" class="activity-item">
            <span>{{ a.payload.from }} → {{ a.payload.to }}</span>
            <span class="activity-meta">{{ a.user?.name ?? 'System' }} · {{ formatDate(a.created_at) }}</span>
          </li>
        </ul>
      </div>
    </div>

    <Dialog
      v-model:visible="dialogVisible"
      header="Edit Deal"
      :style="{ width: '520px' }"
      modal
    >
      <DealForm
        :initial="{ ...deal, customer_id: deal.customer?.id, owner_id: deal.owner?.id }"
        :loading="saving"
        :errors="formErrors"
        @submit="handleSubmit"
        @cancel="dialogVisible = false"
      />
    </Dialog>

    <!-- Move Stage Confirmation Dialog -->
    <Dialog
      v-model:visible="stageConfirmVisible"
      header="Move Stage"
      :style="{ width: '420px' }"
      modal
    >
      <div class="confirm-body">
        <i class="pi pi-arrow-right-arrow-left confirm-icon" />
        <p class="confirm-message">
          Move this deal from
          <strong>{{ currentStageName }}</strong>
          to
          <strong>{{ pendingStage?.label }}</strong>?
        </p>
        <p class="confirm-sub">This will be recorded in the stage history.</p>
      </div>
      <template #footer>
        <Button
          label="Cancel"
          severity="secondary"
          text
          @click="stageConfirmVisible = false"
        />
        <Button
          label="Move Stage"
          icon="pi pi-check"
          :loading="movingStage"
          @click="confirmMoveStage"
        />
      </template>
    </Dialog>
  </div>

  <div v-else class="loading-state"><ProgressSpinner /></div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute }       from 'vue-router'
import { useToast }       from 'primevue/usetoast'
import Button             from 'primevue/button'
import Dialog             from 'primevue/dialog'
import Tag                from 'primevue/tag'
import ProgressSpinner    from 'primevue/progressspinner'
import PageHeader         from '@/components/shared/PageHeader.vue'
import EmptyState         from '@/components/shared/EmptyState.vue'
import DealStageBadge     from '@/components/deals/DealStageBadge.vue'
import DealForm           from '@/components/deals/DealForm.vue'
import { useDealStore }   from '@/stores/deals'
import { useApi }         from '@/api/useApi'

const route = useRoute()
const toast = useToast()
const store = useDealStore()
const api   = useApi()

const deal                = ref(null)
const stageHistory        = ref([])
const dialogVisible       = ref(false)
const saving              = ref(false)
const formErrors          = ref({})
const stageConfirmVisible = ref(false)
const pendingStage        = ref(null) // { label, value }
const movingStage         = ref(false)

const stages = [
  { label: 'Prospecting',   value: 'prospecting'   },
  { label: 'Qualification', value: 'qualification'  },
  { label: 'Proposal',      value: 'proposal'       },
  { label: 'Negotiation',   value: 'negotiation'    },
  { label: 'Closed',        value: 'closed'         },
]

const currentStageName = computed(
  () => stages.find(s => s.value === deal.value?.stage)?.label ?? deal.value?.stage
)

onMounted(async () => {
  const { data } = await store.fetchOne(route.params.id)
  if (data) deal.value = data.data
  await refreshHistory()
})

async function refreshHistory() {
  const { data } = await api.get(`/activities/deals/${route.params.id}/stage-history`)
  if (data) stageHistory.value = data.data
}

// Step 1 — Open confirmation modal
function promptMoveStage(stage) {
  pendingStage.value       = stage
  stageConfirmVisible.value = true
}

// Step 2 — Confirmed
async function confirmMoveStage() {
  if (!pendingStage.value) return
  movingStage.value = true

  const { data, error } = await store.moveStage(deal.value.id, pendingStage.value.value)

  movingStage.value        = false
  stageConfirmVisible.value = false

  if (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
    return
  }

  deal.value = data.data
  toast.add({ severity: 'success', summary: `Moved to ${pendingStage.value.label}`, life: 2500 })
  pendingStage.value = null
  await refreshHistory()
}

async function handleSubmit(payload) {
  saving.value = true
  formErrors.value = {}

  const { data, error } = await store.update(deal.value.id, payload)
  saving.value = false

  if (error) {
    if (error.errors) formErrors.value = error.errors
    toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
    return
  }

  deal.value = data.data
  toast.add({ severity: 'success', summary: 'Deal updated', life: 3000 })
  dialogVisible.value = false
}

function statusSeverity(s) {
  return { open: 'info', won: 'success', lost: 'danger', stalled: 'warn' }[s] ?? 'secondary'
}

function formatCurrency(value, currency = 'USD') {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value)
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString() : '—'
}
</script>

<style scoped>
.detail-grid { display: grid; grid-template-columns: 360px 1fr; gap: 1.5rem; }
.detail-card { background: var(--p-surface-card); border: 1px solid var(--p-surface-border); border-radius: 10px; padding: 1.5rem; }
.card-title  { font-size: 0.95rem; font-weight: 600; margin: 0 0 1rem; }
.info-list   { display: flex; flex-direction: column; gap: 0.75rem; margin: 0; }
.info-row    { display: flex; justify-content: space-between; font-size: 0.875rem; }
.info-row dt { color: var(--p-text-muted-color); }
.info-row dd { margin: 0; font-weight: 500; }
.stage-buttons { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.activity-feed { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.activity-item { display: flex; justify-content: space-between; font-size: 0.85rem; }
.activity-meta { color: var(--p-text-muted-color); }
.empty-state   { color: var(--p-text-muted-color); font-size: 0.875rem; }
.loading-state { display: flex; justify-content: center; padding: 4rem; }

/* Confirmation dialog */
.confirm-body    { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.5rem; padding: 1rem 0; }
.confirm-icon    { font-size: 2rem; color: var(--p-primary-color); }
.confirm-message { margin: 0; font-size: 1rem; }
.confirm-sub     { margin: 0; font-size: 0.85rem; color: var(--p-text-muted-color); }
</style>