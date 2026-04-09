<template>
  <div>
    <PageHeader title="Team" subtitle="Manage members and pending invitations">
      <template #actions>
        <Button label="Invite member" icon="pi pi-user-plus" @click="dialogVisible = true" />
      </template>
    </PageHeader>

    <!-- Pending invitations table -->
    <h3 class="section-title">Pending invitations</h3>

    <DataTable :value="invitations" :loading="loading" class="crm-table">
      <template #empty>
        <EmptyState
          icon="pi pi-envelope"
          title="No pending invitations"
          description="Invite a team member to get started."
        />
      </template>

      <Column field="email"      header="Email" />
      <Column field="role"       header="Role">
        <template #body="{ data }">
          <Tag :value="data.role" severity="secondary" />
        </template>
      </Column>
      <Column field="invited_by" header="Invited by" />
      <Column field="expires_at" header="Expires">
        <template #body="{ data }">{{ formatDate(data.expires_at) }}</template>
      </Column>
      <Column header="" style="width:80px">
        <template #body="{ data }">
          <Button
            icon="pi pi-times"
            text
            size="small"
            severity="danger"
            @click="confirmRevoke(data)"
          />
        </template>
      </Column>
    </DataTable>

    <!-- Send invite dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      header="Invite team member"
      :style="{ width: '420px' }"
      modal
    >
      <div class="invite-form">
        <div class="field">
          <label>Email address</label>
          <InputText
            v-model="inviteForm.email"
            type="email"
            placeholder="colleague@company.com"
            :invalid="!!formErrors.email"
            fluid
          />
          <small v-if="formErrors.email" class="error-text">
            {{ formErrors.email[0] }}
          </small>
        </div>

        <div class="field">
          <label>Role</label>
          <Select
            v-model="inviteForm.role"
            :options="roleOptions"
            option-label="label"
            option-value="value"
            fluid
          />
        </div>

        <Message v-if="formError" severity="error" :closable="false">
          {{ formError }}
        </Message>
      </div>

      <template #footer>
        <Button label="Cancel"      severity="secondary" text @click="dialogVisible = false" />
        <Button label="Send invite" icon="pi pi-send" :loading="sending" @click="sendInvite" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast }                 from 'primevue/usetoast'
import { useConfirm }               from 'primevue/useconfirm'
import DataTable                    from 'primevue/datatable'
import Column                       from 'primevue/column'
import Button                       from 'primevue/button'
import InputText                    from 'primevue/inputtext'
import Select                       from 'primevue/select'
import Dialog                       from 'primevue/dialog'
import Tag                          from 'primevue/tag'
import Message                      from 'primevue/message'
import PageHeader                   from '@/components/shared/PageHeader.vue'
import EmptyState                   from '@/components/shared/EmptyState.vue'
import { useApi }                   from '@/api/useApi'

const api     = useApi()
const toast   = useToast()
const confirm = useConfirm()

const invitations    = ref([])
const loading        = ref(false)
const dialogVisible  = ref(false)
const sending        = ref(false)
const formErrors     = ref({})
const formError      = ref(null)

const inviteForm = reactive({ email: '', role: 'member' })

const roleOptions = [
  { label: 'Admin',  value: 'admin'  },
  { label: 'Member', value: 'member' },
]

onMounted(fetchInvitations)

async function fetchInvitations() {
  loading.value = true
  const { data } = await api.get('/invitations')
  if (data) invitations.value = data.data
  loading.value = false
}

async function sendInvite() {
  formErrors.value = {}
  formError.value  = null
  sending.value    = true

  const { data, error } = await api.post('/invitations', inviteForm)
  sending.value = false

  if (error) {
    formError.value  = error.message
    if (error.errors) formErrors.value = error.errors
    return
  }

  toast.add({
    severity: 'success',
    summary:  'Invitation sent',
    detail:   `Invite sent to ${inviteForm.email}`,
    life:     4000,
  })

  inviteForm.email  = ''
  inviteForm.role   = 'member'
  dialogVisible.value = false
  await fetchInvitations()
}

function confirmRevoke(invitation) {
  confirm.require({
    message:     `Revoke the invitation for ${invitation.email}?`,
    header:      'Revoke invitation',
    icon:        'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    acceptLabel: 'Revoke',
    rejectLabel: 'Cancel',
    accept: async () => {
      const { error } = await api.delete(`/invitations/${invitation.id}`)
      if (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 4000 })
      } else {
        toast.add({ severity: 'success', summary: 'Invitation revoked', life: 3000 })
        await fetchInvitations()
      }
    },
  })
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString() : '—'
}
</script>

<style scoped>
.section-title { font-size:.9rem; font-weight:600; margin:0 0 .75rem; color:var(--p-text-color); }
.invite-form   { display:flex; flex-direction:column; gap:1rem; padding:.25rem 0; }
.field         { display:flex; flex-direction:column; gap:.375rem; }
.field label   { font-size:.875rem; font-weight:500; }
.error-text    { color:var(--p-red-500); font-size:.8rem; }
.crm-table     { border-radius:8px; overflow:hidden; }
</style>