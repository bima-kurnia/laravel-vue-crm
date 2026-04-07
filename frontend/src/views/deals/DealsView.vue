<template>
  <div>
    <PageHeader title="Deals" :subtitle="`${meta?.total ?? 0} total`">
      <template #actions>
        <Button label="Add Deal" icon="pi pi-plus" @click="openCreate" />
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="filters">
      <InputText v-model="filters.search" placeholder="Search deals…" @update:modelValue="debouncedFetch" />
      <Select
        v-model="filters.status"
        :options="statusOptions"
        option-label="label"
        option-value="value"
        placeholder="All statuses"
        show-clear
        @change="fetchDeals"
      />
      <Select
        v-model="filters.stage"
        :options="stageOptions"
        option-label="label"
        option-value="value"
        placeholder="All stages"
        show-clear
        @change="fetchDeals"
      />
    </div>

    <DataTable :value="deals" :loading="loading" striped-rows class="crm-table">
      <Column field="title" header="Title">
        <template #body="{ data }">
          <RouterLink :to="{ name: 'deal-detail', params: { id: data.id } }" class="row-link">
            {{ data.title }}
          </RouterLink>
        </template>
      </Column>
      <Column field="owner" header="Owner">
        <template #body="{ data }">{{ data.owner?.name ?? '—' }}</template>
      </Column>
      <Column field="customer" header="Customer">
        <template #body="{ data }">{{ data.customer?.name ?? '—' }}</template>
      </Column>
      <Column field="value" header="Value">
        <template #body="{ data }">{{ formatCurrency(data.value, data.currency) }}</template>
      </Column>
      <Column field="stage" header="Stage">
        <template #body="{ data }"><DealStageBadge :stage="data.stage" /></template>
      </Column>
      <Column field="status" header="Status">
        <template #body="{ data }">
          <Tag :value="data.status" :severity="statusSeverity(data.status)" />
        </template>
      </Column>
      <Column field="expected_close_date" header="Close Date">
        <template #body="{ data }">{{ data.expected_close_date ?? '—' }}</template>
      </Column>
      <Column header="" style="width:80px">
        <template #body="{ data }">
          <Button icon="pi pi-pencil" text size="small" @click="openEdit(data)" />
        </template>
      </Column>
    </DataTable>

    <Paginator
      v-if="meta && meta.last_page > 1"
      :rows="meta.per_page"
      :total-records="meta.total"
      :first="(meta.current_page - 1) * meta.per_page"
      @page="onPage"
    />

    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Edit Deal' : 'Add Deal'"
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
import { ref, reactive, onMounted } from 'vue'
import { useDebounceFn }  from '@vueuse/core'
import { useToast }       from 'primevue/usetoast'
import { storeToRefs }   from 'pinia'
import DataTable         from 'primevue/datatable'
import Column            from 'primevue/column'
import Button            from 'primevue/button'
import InputText         from 'primevue/inputtext'
import Select            from 'primevue/select'
import Dialog            from 'primevue/dialog'
import Paginator         from 'primevue/paginator'
import Tag               from 'primevue/tag'
import PageHeader        from '@/components/shared/PageHeader.vue'
import DealForm          from '@/components/deals/DealForm.vue'
import DealStageBadge    from '@/components/deals/DealStageBadge.vue'
import { useDealStore }  from '@/stores/deals'

const store = useDealStore()
const toast = useToast()

const { deals, meta, loading } = storeToRefs(store)

const filters = reactive({ search: '', status: null, stage: null, page: 1 })

const statusOptions = [
  { label: 'Open',    value: 'open'    },
  { label: 'Won',     value: 'won'     },
  { label: 'Lost',    value: 'lost'    },
  { label: 'Stalled', value: 'stalled' },
]

const stageOptions = [
  { label: 'Prospecting',  value: 'prospecting'  },
  { label: 'Qualification',value: 'qualification' },
  { label: 'Proposal',     value: 'proposal'      },
  { label: 'Negotiation',  value: 'negotiation'   },
  { label: 'Closed',       value: 'closed'        },
]

const dialogVisible = ref(false)
const editing       = ref(false)
const saving        = ref(false)
const formData      = ref({})
const formErrors    = ref({})

onMounted(fetchDeals)

async function fetchDeals() {
  await store.fetchAll({ ...filters })
}

const debouncedFetch = useDebounceFn(fetchDeals, 350)

async function onPage(event) {
  filters.page = event.page + 1
  await fetchDeals()
}

function openCreate() {
  editing.value = false
  formData.value = {}
  formErrors.value = {}
  dialogVisible.value = true
}

function openEdit(deal) {
  editing.value = true
  formData.value = { ...deal, customer_id: deal.customer?.id, owner_id: deal.owner?.id }
  formErrors.value = {}
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

  toast.add({ severity: 'success', summary: editing.value ? 'Deal updated' : 'Deal created', life: 3000 })
  dialogVisible.value = false
}

function statusSeverity(status) {
  return { open: 'info', won: 'success', lost: 'danger', stalled: 'warn' }[status] ?? 'secondary'
}

function formatCurrency(value, currency = 'USD') {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value)
}
</script>

<style scoped>
.filters { display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.row-link { color: var(--p-primary-color); text-decoration: none; font-weight: 500; }
.row-link:hover { text-decoration: underline; }
.crm-table { border-radius: 8px; overflow: hidden; }
</style>