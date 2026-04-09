<template>
  <div>
    <PageHeader title="Customers" :subtitle="`${meta?.total ?? 0} total`">
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
          @click="exportCustomers(toApiParams)"
        />
        <Button label="Add customer" icon="pi pi-plus" @click="openCreate" />
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="filters">
      <InputText
        :model-value="filters.search"
        placeholder="Search name, email, company…"
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

      <!-- Trash toggle — owner/admin only -->
      <div v-if="isOwnerOrAdmin" class="trash-toggle">
        <ToggleSwitch
          :model-value="showingTrash"
          input-id="trash-toggle"
          @update:modelValue="toggleTrash"
        />
        <label for="trash-toggle" class="trash-label">
          <i class="pi pi-trash" />
          Show deleted
        </label>
      </div>
    </div>

    <!-- Trash mode banner -->
    <div v-if="showingTrash" class="trash-banner">
      <i class="pi pi-info-circle" />
      Showing deleted customers. Restore to bring them back, or permanently
      delete to remove them forever.
    </div>

    <!-- Table -->
    <DataTable
      :value="customers"
      :loading="loading"
      lazy
      striped-rows
      :rows="15"
      class="crm-table"
    >
      <template #empty>
        <EmptyState
          :icon="showingTrash ? 'pi pi-check-circle' : 'pi pi-users'"
          :title="showingTrash ? 'No deleted customers' : 'No customers yet'"
          :description="showingTrash
            ? 'There are no deleted customers to show.'
            : 'Add your first customer or adjust your filters to see results.'"
        >
          <template v-if="!showingTrash" #action>
            <Button label="Add customer" icon="pi pi-plus" @click="openCreate" />
          </template>
        </EmptyState>
      </template>

      <Column field="name" header="Name">
        <template #body="{ data }">
          <RouterLink
            v-if="!showingTrash"
            :to="{ name: 'customer-detail', params: { id: data.id } }"
            class="row-link"
          >
            {{ data.name }}
          </RouterLink>
          <span v-else class="deleted-name">{{ data.name }}</span>
        </template>
      </Column>
      <Column field="email"   header="Email"   />
      <Column field="company" header="Company" />
      <Column field="status"  header="Status">
        <template #body="{ data }">
          <CustomerStatusBadge :status="data.status" />
        </template>
      </Column>
      <Column field="created_at" header="Created">
        <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
      </Column>

      <!-- Deleted at column — only visible in trash mode -->
      <Column v-if="showingTrash" field="deleted_at" header="Deleted">
        <template #body="{ data }">{{ formatDate(data.deleted_at) }}</template>
      </Column>

      <!-- Actions column -->
      <Column header="" :style="showingTrash ? 'width:110px' : 'width:100px'">
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

    <!-- Pagination -->
    <Paginator
      v-if="meta && meta.last_page > 1"
      :rows="meta.per_page"
      :total-records="meta.total"
      :first="(meta.current_page - 1) * meta.per_page"
      @page="e => setFilter('page', e.page + 1)"
    />

    <!-- Create / Edit dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Edit customer' : 'Add customer'"
      :style="{ width: '480px' }"
      modal
    >
      <CustomerForm
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
import ToggleSwitch             from 'primevue/toggleswitch'
import PageHeader               from '@/components/shared/PageHeader.vue'
import EmptyState               from '@/components/shared/EmptyState.vue'
import CustomerForm             from '@/components/customers/CustomerForm.vue'
import CustomerStatusBadge      from '@/components/customers/CustomerStatusBadge.vue'
import { useCustomerStore }     from '@/stores/customers'
import { useExport }            from '@/composables/useExport'
import { useRole }              from '@/composables/useRole'
import { useUrlFilters }        from '@/composables/useUrlFilters'

const store   = useCustomerStore()
const toast   = useToast()
const confirm = useConfirm()
const { exporting, exportCustomers } = useExport()
const { isOwnerOrAdmin } = useRole()

const { customers, meta, loading } = storeToRefs(store)

const { filters, setFilter, resetFilters, toApiParams } = useUrlFilters({
  search:      '',
  status:      null,
  only_trashed: null,
  page:        1,
})

// Derived: are we currently in trash view?
const showingTrash = computed(() => filters.only_trashed === 'true' || filters.only_trashed === true)

const hasActiveFilters = computed(() =>
  !!filters.search || !!filters.status
)

watch(toApiParams, fetchCustomers, { immediate: true })

const statusOptions = [
  { label: 'Active',   value: 'active'   },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Lead',     value: 'lead'     },
]

const dialogVisible = ref(false)
const editing       = ref(false)
const saving        = ref(false)
const formData      = ref({})
const formErrors    = ref({})

async function fetchCustomers() {
  await store.fetchAll(toApiParams.value)
}

function toggleTrash(val) {
  // Reset page and status filter when switching modes
  setFilter('status', null)
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

function openEdit(customer) {
  editing.value       = true
  formData.value      = { ...customer }
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
    summary:  editing.value ? 'Customer updated' : 'Customer created',
    life:     3000,
  })
  dialogVisible.value = false
  await fetchCustomers()
}

// -------------------------------------------------------------------------
// Soft delete
// -------------------------------------------------------------------------
function confirmDelete(customer) {
  const id   = customer.id
  const name = customer.name

  confirm.require({
    message:     `Delete "${name}"? They'll appear in the trash and can be restored.`,
    header:      'Delete customer',
    icon:        'pi pi-exclamation-triangle',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      const { error } = await store.remove(id)
      if (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
      } else {
        toast.add({ severity: 'success', summary: `"${name}" moved to trash.`, life: 3000 })
      }
    },
  })
}

// -------------------------------------------------------------------------
// Restore
// -------------------------------------------------------------------------
function confirmRestore(customer) {
  const id   = customer.id
  const name = customer.name

  confirm.require({
    message:     `Restore "${name}"? They'll be active again immediately.`,
    header:      'Restore customer',
    icon:        'pi pi-replay',
    rejectLabel: 'Cancel',
    acceptLabel: 'Restore',
    accept: async () => {
      const { error } = await store.restore(id)
      if (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
      } else {
        toast.add({ severity: 'success', summary: `"${name}" restored.`, life: 3000 })
      }
    },
  })
}

// -------------------------------------------------------------------------
// Force delete
// -------------------------------------------------------------------------
function confirmForceDelete(customer) {
  const id   = customer.id
  const name = customer.name

  confirm.require({
    message:     `Permanently delete "${name}"? This cannot be undone and will remove all associated data.`,
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
          summary:  `"${name}" permanently deleted.`,
          life:     4000,
        })
      }
    },
  })
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