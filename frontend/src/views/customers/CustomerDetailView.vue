<template>
  <div v-if="customer">
    <PageHeader :title="customer.name" :subtitle="customer.company">
      <template #actions>
        <Button label="Edit" icon="pi pi-pencil" @click="openEdit" />
      </template>
    </PageHeader>

    <div class="detail-grid">
      <!-- Info card -->
      <div class="detail-card">
        <h3 class="card-title">Contact Info</h3>
        <dl class="info-list">
          <div class="info-row"><dt>Email</dt><dd>{{ customer.email ?? '—' }}</dd></div>
          <div class="info-row"><dt>Phone</dt><dd>{{ customer.phone ?? '—' }}</dd></div>
          <div class="info-row"><dt>Company</dt><dd>{{ customer.company ?? '—' }}</dd></div>
          <div class="info-row">
            <dt>Status</dt>
            <dd><CustomerStatusBadge :status="customer.status" /></dd>
          </div>
        </dl>
      </div>

      <!-- Activity feed -->
      <div class="detail-card">
        <h3 class="card-title">Activity</h3>
        <div v-if="activities.length === 0" class="empty-state">No activity yet.</div>
        <ul v-else class="activity-feed">
          <li v-for="a in activities" :key="a.id" class="activity-item">
            <span class="activity-event">{{ a.event }}</span>
            <span class="activity-meta">{{ a.user?.name ?? 'System' }} · {{ formatDate(a.created_at) }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Edit dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      header="Edit Customer"
      :style="{ width: '480px' }"
      modal
    >
      <CustomerForm
        :initial="customer"
        :loading="saving"
        :errors="formErrors"
        @submit="handleSubmit"
        @cancel="dialogVisible = false"
      />
    </Dialog>
  </div>

  <div v-else class="loading-state">
    <ProgressSpinner />
  </div>
</template>

<script setup>
import { ref, onMounted }  from 'vue'
import { useRoute }        from 'vue-router'
import { useToast }        from 'primevue/usetoast'
import Button              from 'primevue/button'
import Dialog              from 'primevue/dialog'
import ProgressSpinner     from 'primevue/progressspinner'
import PageHeader          from '@/components/shared/PageHeader.vue'
import CustomerStatusBadge from '@/components/customers/CustomerStatusBadge.vue'
import CustomerForm        from '@/components/customers/CustomerForm.vue'
import { useCustomerStore }from '@/stores/customers'
import { useApi }          from '@/api/useApi'

const route   = useRoute()
const toast   = useToast()
const store   = useCustomerStore()
const api     = useApi()

const customer    = ref(null)
const activities  = ref([])
const dialogVisible = ref(false)
const saving      = ref(false)
const formErrors  = ref({})

onMounted(async () => {
  const { data } = await store.fetchOne(route.params.id)
  if (data) customer.value = data.data

  const { data: actData } = await api.get(
    `/activities/subject/customer/${route.params.id}`
  )
  if (actData) activities.value = actData.data
})

function openEdit() {
  formErrors.value  = {}
  dialogVisible.value = true
}

async function handleSubmit(payload) {
  saving.value = true
  formErrors.value = {}

  const { data, error } = await store.update(customer.value.id, payload)
  saving.value = false

  if (error) {
    if (error.errors) formErrors.value = error.errors
    toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
    return
  }

  customer.value = data.data
  toast.add({ severity: 'success', summary: 'Customer updated', life: 3000 })
  dialogVisible.value = false
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString() : '—'
}
</script>

<style scoped>
.detail-grid {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 1.5rem;
}
.detail-card {
  background: var(--p-surface-card);
  border: 1px solid var(--p-surface-border);
  border-radius: 10px;
  padding: 1.5rem;
}
.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0 0 1rem;
  color: var(--p-text-color);
}
.info-list { display: flex; flex-direction: column; gap: 0.75rem; margin: 0; }
.info-row  { display: flex; justify-content: space-between; font-size: 0.875rem; }
.info-row dt { color: var(--p-text-muted-color); }
.info-row dd { margin: 0; font-weight: 500; }
.activity-feed { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.activity-item { display: flex; justify-content: space-between; font-size: 0.85rem; }
.activity-event { font-weight: 500; text-transform: capitalize; }
.activity-meta  { color: var(--p-text-muted-color); }
.empty-state    { color: var(--p-text-muted-color); font-size: 0.875rem; }
.loading-state  { display: flex; justify-content: center; padding: 4rem; }
</style>