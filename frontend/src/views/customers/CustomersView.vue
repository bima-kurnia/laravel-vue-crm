<template>
  <div>
    <PageHeader title="Customers" :subtitle="`${meta?.total ?? 0} total`">
      <template #actions>
        <Button label="Add Customer" icon="pi pi-plus" @click="openCreate" />
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="filters">
      <InputText
        v-model="filters.search"
        placeholder="Search name, email, company…"
        @update:modelValue="debouncedFetch"
      />
      <Select
        v-model="filters.status"
        :options="statusOptions"
        option-label="label"
        option-value="value"
        placeholder="All statuses"
        show-clear
        @change="fetchCustomers"
      />
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
      <!-- empty state -->
      <template #empty>
        <EmptyState
          icon="pi pi-users"
          title="No customers yet"
          description="Add your first customer or adjust your filters to see results."
        >
          <template #action>
            <Button label="Add Customer" icon="pi pi-plus" @click="openCreate" />
          </template>
        </EmptyState>
      </template>

      <Column field="name"    header="Name">
        <template #body="{ data }">
          <RouterLink :to="{ name: 'customer-detail', params: { id: data.id } }" class="row-link">
            {{ data.name }}
          </RouterLink>
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
        <template #body="{ data }">
          {{ formatDate(data.created_at) }}
        </template>
      </Column>
      <Column header="" style="width:120px">
        <template #body="{ data }">
          <div class="row-actions">
            <Button icon="pi pi-pencil" text size="small" @click="openEdit(data)" />
            <Button v-if="isOwnerOrAdmin" icon="pi pi-trash" text size="small" severity="danger" @click="confirmDelete(data)" />
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
      @page="onPage"
    />

    <!-- Create / Edit Dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Edit Customer' : 'Add Customer'"
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
import { ref, reactive, onMounted } from 'vue'
import { useDebounceFn }  from '@vueuse/core'
import { useToast }       from 'primevue/usetoast'
import { useConfirm }     from 'primevue/useconfirm'
import DataTable          from 'primevue/datatable'
import Column             from 'primevue/column'
import Button             from 'primevue/button'
import InputText          from 'primevue/inputtext'
import Select             from 'primevue/select'
import Dialog             from 'primevue/dialog'
import Paginator          from 'primevue/paginator'
import PageHeader         from '@/components/shared/PageHeader.vue'
import EmptyState         from '@/components/shared/EmptyState.vue'
import CustomerForm       from '@/components/customers/CustomerForm.vue'
import CustomerStatusBadge from '@/components/customers/CustomerStatusBadge.vue'
import { useCustomerStore } from '@/stores/customers'
import { useRole } from '@/composables/useRole'
const { isOwner, isAdmin, isOwnerOrAdmin } = useRole()
import { storeToRefs }    from 'pinia'

const store   = useCustomerStore()
const toast   = useToast()
const confirm = useConfirm()

const { customers, meta, loading } = storeToRefs(store)

const filters = reactive({ search: '', status: null, page: 1 })
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

onMounted(fetchCustomers)

async function fetchCustomers() {
  await store.fetchAll({ ...filters })
}

const debouncedFetch = useDebounceFn(fetchCustomers, 350)

async function onPage(event) {
  filters.page = event.page + 1
  await fetchCustomers()
}

function openCreate() {
  editing.value     = false
  formData.value    = {}
  formErrors.value  = {}
  dialogVisible.value = true
}

function openEdit(customer) {
  editing.value     = true
  formData.value    = { ...customer }
  formErrors.value  = {}
  dialogVisible.value = true
}

async function handleSubmit(payload) {
  saving.value = true
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
}

function confirmDelete(customer) {
  // Capture values immediately — do not close over the reactive row reference
  const id   = customer.id
  const name = customer.name

  confirm.require({
    message: `Delete ${name}? This action can be undone from the trash.`,
    header:  'Confirm Delete',
    icon:    'pi pi-exclamation-triangle',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      const { error } = await store.remove(id)
      if (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
      } else {
        toast.add({ severity: 'success', summary: `Customer: ${name} deleted`, life: 3000 })
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
}
.row-link {
  color: var(--p-primary-color);
  text-decoration: none;
  font-weight: 500;
}
.row-link:hover { text-decoration: underline; }
.row-actions { display: flex; gap: 0.25rem; }
.crm-table { border-radius: 8px; overflow: hidden; }
</style>