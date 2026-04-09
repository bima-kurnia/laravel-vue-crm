<template>
  <div>
    <PageHeader title="Deals" :subtitle="`${meta?.total ?? 0} total`">
      <template #actions>
        <Button
          v-if="hasActiveFilters"
          label="Clear filters"
          icon="pi pi-times"
          severity="secondary"
          text
          size="small"
          @click="resetFilters"
        />
        <Button
          label="Export CSV"
          icon="pi pi-download"
          severity="secondary"
          :loading="exporting"
          @click="exportDeals(toApiParams)"
        />
        <Button label="Add deal" icon="pi pi-plus" @click="openCreate" />
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="filters">
      <InputText
        :model-value="filters.search"
        placeholder="Search deals…"
        @update:modelValue="debouncedSearch"
      />
      <Select
        :model-value="filters.status"
        :options="statusOptions"
        option-label="label"
        option-value="value"
        placeholder="All statuses"
        show-clear
        @change="e => setFilter('status', e.value)"
      />
      <Select
        :model-value="filters.stage"
        :options="stageOptions"
        option-label="label"
        option-value="value"
        placeholder="All stages"
        show-clear
        @change="e => setFilter('stage', e.value)"
      />

      <!-- Trash toggle — owner/admin only -->
      <div v-if="isOwnerOrAdmin" class="trash-toggle">
        <ToggleSwitch
          :model-value="showingTrash"
          input-id="trash-toggle-deals"
          @update:modelValue="toggleTrash"
        />
        <label for="trash-toggle-deals" class="trash-label">
          <i class="pi pi-trash" />
          Show deleted
        </label>
      </div>
    </div>

    <!-- Trash mode banner -->
    <div v-if="showingTrash" class="trash-banner">
      <i class="pi pi-info-circle" />
      Showing deleted deals. Restore to bring them back, or permanently
      delete to remove them forever.
    </div>

    <DataTable :value="deals" :loading="loading" striped-rows class="crm-table">
      <template #empty>
        <EmptyState
          :icon="showingTrash ? 'pi pi-check-circle' : 'pi pi-briefcase'"
          :title="showingTrash ? 'No deleted deals' : 'No deals found'"
          :description="showingTrash
            ? 'There are no deleted deals to show.'
            : 'Create your first deal or adjust your filters to see results.'"
        >
          <template v-if="!showingTrash" #action>
            <Button label="Add deal" icon="pi pi-plus" @click="openCreate" />
          </template>
        </EmptyState>
      </template>

      <Column field="title" header="Title">
        <template #body="{ data }">
          <RouterLink
            v-if="!showingTrash"
            :to="{ name: 'deal-detail', params: { id: data.id } }"
            class="row-link"
          >
            {{ data.title }}
          </RouterLink>
          <span v-else class="deleted-name">{{ data.title }}</span>
        </template>
      </Column>
      <Column field="owner" header="Owner">
        <template #body="{ data }">{{ data.owner?.name ?? '—' }}</template>
      </Column>
      <Column field="customer" header="Customer">
        <template #body="{ data }">{{ data.customer?.name ?? '—' }}</template>
      </Column>
      <Column field="value" header="Value">
        <template #body="{ data }">
          {{ formatCurrency(data.value, data.currency) }}
        </template>
      </Column>
      <Column field="stage" header="Stage">
        <template #body="{ data }">
          <DealStageBadge :stage="data.stage" />
        </template>
      </Column>
      <Column field="status" header="Status">
        <template #body="{ data }">
          <Tag :value="data.status" :severity="statusSeverity(data.status)" />
        </template>
      </Column>
      <Column field="expected_close_date" header="Close date">
        <template #body="{ data }">{{ data.expected_close_date ?? '—' }}</template>
      </Column>

      <!-- Deleted at column — only visible in trash mode -->
      <Column v-if="showingTrash" field="deleted_at" header="Deleted">
        <template #body="{ data }">{{ formatDate(data.deleted_at) }}</template>
      </Column>

      <!-- Actions column -->
      <Column header="" :style="showingTrash ? 'width:110px' : 'width:80px'">
        <template #body="{ data }">
          <div class="row-actions">

            <!-- Normal mode: edit + soft delete -->
            <template v-if="!showingTrash">
              <Button
                icon="pi pi-pencil"
                text
                size="small"
                v-tooltip.top="'Edit'"
                @click="openEdit(data)"
              />
              <Button
                v-if="isOwnerOrAdmin"
                icon="pi pi-trash"
                text
                size="small"
                severity="danger"
                v-tooltip.top="'Delete'"
                @click="confirmDelete(data)"
              />
            </template>

            <!-- Trash mode: restore + force delete -->
            <template v-else>
              <Button
                icon="pi pi-replay"
                text
                size="small"
                severity="success"
                v-tooltip.top="'Restore'"
                @click="confirmRestore(data)"
              />
              <Button
                icon="pi pi-times"
                text
                size="small"
                severity="danger"
                v-tooltip.top="'Delete permanently'"
                @click="confirmForceDelete(data)"
              />
            </template>

          </div>
        </template>
      </Column>
    </DataTable>

    <Paginator
      v-if="meta && meta.last_page > 1"
      :rows="meta.per_page"
      :total-records="meta.total"
      :first="(meta.current_page - 1) * meta.per_page"
      @page="e => setFilter('page', e.page + 1)"
    />

    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Edit deal' : 'Add deal'"
      :style="{ width: '520px' }"
      modal
    >
      <DealForm
        :initial="formData"
        :loading="saving"
        :errors="formErrors"
        @submit="handleSubmit"
        @cancel="dialogVisible = false"
      />
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useDebounceFn }        from '@vueuse/core'
import { useToast }             from 'primevue/usetoast'
import { useConfirm }           from 'primevue/useconfirm'
import { storeToRefs }          from 'pinia'
import DataTable                from 'primevue/datatable'
import Column                   from 'primevue/column'
import Button                   from 'primevue/button'
import InputText                from 'primevue/inputtext'
import Select                   from 'primevue/select'
import Dialog                   from 'primevue/dialog'
import Paginator                from 'primevue/paginator'
import Tag                      from 'primevue/tag'
import ToggleSwitch             from 'primevue/toggleswitch'
import PageHeader               from '@/components/shared/PageHeader.vue'
import EmptyState               from '@/components/shared/EmptyState.vue'
import DealForm                 from '@/components/deals/DealForm.vue'
import DealStageBadge           from '@/components/deals/DealStageBadge.vue'
import { useDealStore }         from '@/stores/deals'
import { useExport }            from '@/composables/useExport'
import { useRole }              from '@/composables/useRole'
import { useUrlFilters }        from '@/composables/useUrlFilters'

const store   = useDealStore()
const toast   = useToast()
const confirm = useConfirm()
const { exporting, exportDeals } = useExport()
const { isOwnerOrAdmin } = useRole()

const { deals, meta, loading } = storeToRefs(store)

const { filters, setFilter, resetFilters, toApiParams } = useUrlFilters({
  search:       '',
  status:       null,
  stage:        null,
  only_trashed: null,
  page:         1,
})

const showingTrash = computed(() => filters.only_trashed === 'true' || filters.only_trashed === true)

const hasActiveFilters = computed(() =>
  !!filters.search || !!filters.status || !!filters.stage
)

watch(toApiParams, fetchDeals, { immediate: true })

const statusOptions = [
  { label: 'Open',    value: 'open'    },
  { label: 'Won',     value: 'won'     },
  { label: 'Lost',    value: 'lost'    },
  { label: 'Stalled', value: 'stalled' },
]

const stageOptions = [
  { label: 'Prospecting',   value: 'prospecting'   },
  { label: 'Qualification', value: 'qualification'  },
  { label: 'Proposal',      value: 'proposal'       },
  { label: 'Negotiation',   value: 'negotiation'    },
  { label: 'Closed',        value: 'closed'         },
]

const dialogVisible = ref(false)
const editing       = ref(false)
const saving        = ref(false)
const formData      = ref({})
const formErrors    = ref({})

async function fetchDeals() {
  await store.fetchAll(toApiParams.value)
}

function toggleTrash(val) {
  setFilter('status', null)
  setFilter('stage', null)
  setFilter('only_trashed', val ? true : null)
}

const debouncedSearch = useDebounceFn(
  (value) => setFilter('search', value),
  350,
)

function openCreate() {
  editing.value       = false
  formData.value      = {}
  formErrors.value    = {}
  dialogVisible.value = true
}

function openEdit(deal) {
  editing.value       = true
  formData.value      = {
    ...deal,
    customer_id: deal.customer?.id,
    owner_id:    deal.owner?.id,
  }
  formErrors.value    = {}
  dialogVisible.value = true
}

async function handleSubmit(payload) {
  saving.value     = true
  formErrors.value = {}

  const { error } = editing.value
    ? await store.update(formData.value.id, payload)
    : await store.create(payload)

  saving.value = false

  if (error) {
    if (error.errors) formErrors.value = error.errors
    toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
    return
  }

  toast.add({
    severity: 'success',
    summary:  editing.value ? 'Deal updated' : 'Deal created',
    life:     3000,
  })
  dialogVisible.value = false
  await fetchDeals()
}

// -------------------------------------------------------------------------
// Soft delete
// -------------------------------------------------------------------------
function confirmDelete(deal) {
  const id    = deal.id
  const title = deal.title

  confirm.require({
    message:     `Delete "${title}"? It will appear in the trash and can be restored.`,
    header:      'Delete deal',
    icon:        'pi pi-exclamation-triangle',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      const { error } = await store.remove(id)
      if (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
      } else {
        toast.add({ severity: 'success', summary: `"${title}" moved to trash.`, life: 3000 })
      }
    },
  })
}

// -------------------------------------------------------------------------
// Restore
// -------------------------------------------------------------------------
function confirmRestore(deal) {
  const id    = deal.id
  const title = deal.title

  confirm.require({
    message:     `Restore "${title}"? It will be active again immediately.`,
    header:      'Restore deal',
    icon:        'pi pi-replay',
    rejectLabel: 'Cancel',
    acceptLabel: 'Restore',
    accept: async () => {
      const { error } = await store.restore(id)
      if (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
      } else {
        toast.add({ severity: 'success', summary: `"${title}" restored.`, life: 3000 })
      }
    },
  })
}

// -------------------------------------------------------------------------
// Force delete
// -------------------------------------------------------------------------
function confirmForceDelete(deal) {
  const id    = deal.id
  const title = deal.title

  confirm.require({
    message:     `Permanently delete "${title}"? This cannot be undone and will remove all associated data.`,
    header:      'Permanently delete',
    icon:        'pi pi-exclamation-triangle',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete permanently',
    acceptClass: 'p-button-danger',
    accept: async () => {
      const { error } = await store.forceDelete(id)
      if (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
      } else {
        toast.add({
          severity: 'warn',
          summary:  `"${title}" permanently deleted.`,
          life:     4000,
        })
      }
    },
  })
}

function statusSeverity(status) {
  return { open: 'info', won: 'success', lost: 'danger', stalled: 'warn' }[status] ?? 'secondary'
}

function formatCurrency(value, currency = 'USD') {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value)
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString() : '—'
}
</script>

<style scoped>
.filters {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  align-items: center;
}

.trash-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: auto;
}

.trash-label {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: var(--p-text-muted-color);
  cursor: pointer;
  user-select: none;
}

.trash-label i {
  font-size: 0.85rem;
}

.trash-banner {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--p-orange-50);
  border: 1px solid var(--p-orange-200);
  border-radius: 8px;
  padding: 0.6rem 1rem;
  margin-bottom: 1rem;
  font-size: 0.85rem;
  color: var(--p-orange-800);
}

.deleted-name {
  color: var(--p-text-muted-color);
  text-decoration: line-through;
}

.row-link    { color: var(--p-primary-color); text-decoration: none; font-weight: 500; }
.row-link:hover { text-decoration: underline; }
.row-actions { display: flex; gap: 0.25rem; }
.crm-table   { border-radius: 8px; overflow: hidden; }
</style>
