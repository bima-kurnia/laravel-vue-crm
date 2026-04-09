<template>
  <div class="login-page">
    <div class="login-card">

      <!-- Loading token validation -->
      <div v-if="validating" class="invite-loading">
        <ProgressSpinner />
        <p>Validating your invitation…</p>
      </div>

      <!-- Invalid / expired token -->
      <div v-else-if="tokenError" class="invite-error">
        <i class="pi pi-times-circle invite-error-icon" />
        <h2>Invalid invitation</h2>
        <p>{{ tokenError }}</p>
        <RouterLink :to="{ name: 'login' }">Back to login</RouterLink>
      </div>

      <!-- Valid — show registration form -->
      <template v-else>
        <div class="login-header">
          <span class="login-logo">CRM</span>
          <p class="login-sub">
            You've been invited to join
            <strong>{{ inviteInfo.tenant_name }}</strong>
            by {{ inviteInfo.invited_by }} as a
            <strong>{{ inviteInfo.role }}</strong>.
          </p>
        </div>

        <div class="login-form">
          <div class="field">
            <label>Email</label>
            <InputText :model-value="inviteInfo.email" disabled fluid />
          </div>

          <div class="field">
            <label>Your name</label>
            <InputText
              v-model="form.name"
              placeholder="Full name"
              :invalid="!!errors.name"
              fluid
            />
            <small v-if="errors.name" class="error-text">{{ errors.name[0] }}</small>
          </div>

          <div class="field">
            <label>Password</label>
            <Password
              v-model="form.password"
              :feedback="true"
              :invalid="!!errors.password"
              fluid
              toggleMask
            />
            <small v-if="errors.password" class="error-text">{{ errors.password[0] }}</small>
          </div>

          <div class="field">
            <label>Confirm password</label>
            <Password
              v-model="form.password_confirmation"
              :feedback="false"
              fluid
              toggleMask
            />
          </div>

          <Message v-if="errorMessage" severity="error" :closable="false">
            {{ errorMessage }}
          </Message>

          <Button
            label="Create account"
            icon="pi pi-check"
            :loading="submitting"
            fluid
            @click="submit"
          />
        </div>
      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter }      from 'vue-router'
import InputText                    from 'primevue/inputtext'
import Password                     from 'primevue/password'
import Button                       from 'primevue/button'
import Message                      from 'primevue/message'
import ProgressSpinner              from 'primevue/progressspinner'
import { useAuthStore }             from '@/stores/auth'
import { useApi }                   from '@/api/useApi'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const api    = useApi()

const token      = route.params.token
const validating = ref(true)
const tokenError = ref(null)
const inviteInfo = ref({})
const submitting = ref(false)
const errors     = ref({})
const errorMessage = ref(null)

const form = reactive({
  name:                  '',
  password:              '',
  password_confirmation: '',
})

onMounted(async () => {
  const { data, error } = await api.get(`/invitations/validate/${token}`)
  validating.value = false

  if (error) {
    tokenError.value = error.message
    
    return
  }

  inviteInfo.value = data
})

async function submit() {
  errors.value       = {}
  errorMessage.value = null
  submitting.value   = true

  const { data, error } = await api.post(
    `/invitations/accept/${token}`,
    form,
  )

  submitting.value = false

  if (error) {
    errorMessage.value = error.message

    if (error.errors) {
      errors.value = error.errors
    }

    return
  }

  auth.setSession(data.token, data.user)

  router.push({ name: 'dashboard' })
}
</script>

<style scoped>
.login-page   { min-height:100vh; display:flex; align-items:center; justify-content:center; background:var(--p-surface-ground); }
.login-card   { background:var(--p-surface-card); border:1px solid var(--p-surface-border); border-radius:12px; padding:2.5rem; width:100%; max-width:440px; box-shadow:0 4px 24px rgba(0,0,0,.06); }
.login-header { text-align:center; margin-bottom:2rem; }
.login-logo   { font-size:2rem; font-weight:800; color:var(--p-primary-color); letter-spacing:.08em; }
.login-sub    { margin:.5rem 0 0; color:var(--p-text-muted-color); font-size:.9rem; line-height:1.5; }
.login-form   { display:flex; flex-direction:column; gap:1.1rem; }
.field        { display:flex; flex-direction:column; gap:.375rem; }
.field label  { font-size:.875rem; font-weight:500; }
.error-text   { color:var(--p-red-500); font-size:.8rem; }

.invite-loading { display:flex; flex-direction:column; align-items:center; gap:1rem; padding:2rem; color:var(--p-text-muted-color); }
.invite-error   { display:flex; flex-direction:column; align-items:center; text-align:center; gap:.75rem; padding:2rem; }
.invite-error-icon { font-size:2.5rem; color:var(--p-red-500); }
.invite-error h2 { margin:0; font-size:1.1rem; }
.invite-error p  { margin:0; color:var(--p-text-muted-color); font-size:.875rem; }
.invite-error a  { color:var(--p-primary-color); font-size:.875rem; }
</style>