<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <span class="login-logo">CRM</span>
        <p class="login-sub">Create your account</p>
      </div>

      <div class="login-form">
        <div class="field">
          <label>Company Name</label>
          <InputText v-model="form.tenant_name" :invalid="!!errors.tenant_name" fluid />
          <small v-if="errors.tenant_name" class="error-text">{{ errors.tenant_name[0] }}</small>
        </div>

        <div class="field">
          <label>Account URL slug</label>
          <InputGroup>
            <InputGroupAddon>crm.app/</InputGroupAddon>
            <InputText v-model="form.tenant_slug" placeholder="acme-corp" :invalid="!!errors.tenant_slug" />
          </InputGroup>
          <small v-if="errors.tenant_slug" class="error-text">{{ errors.tenant_slug[0] }}</small>
        </div>

        <Divider align="left"><span class="divider-label">Your details</span></Divider>

        <div class="field">
          <label>Full Name</label>
          <InputText v-model="form.name" :invalid="!!errors.name" fluid />
          <small v-if="errors.name" class="error-text">{{ errors.name[0] }}</small>
        </div>

        <div class="field">
          <label>Email</label>
          <InputText v-model="form.email" type="email" :invalid="!!errors.email" fluid />
          <small v-if="errors.email" class="error-text">{{ errors.email[0] }}</small>
        </div>

        <div class="field">
          <label>Password</label>
          <Password v-model="form.password" :feedback="true" :invalid="!!errors.password" fluid toggleMask />
          <small v-if="errors.password" class="error-text">{{ errors.password[0] }}</small>
        </div>

        <div class="field">
          <label>Confirm Password</label>
          <Password v-model="form.password_confirmation" :feedback="false" fluid toggleMask />
        </div>

        <Message v-if="errorMessage" severity="error" :closable="false">{{ errorMessage }}</Message>

        <Button label="Create Account" icon="pi pi-check" :loading="loading" fluid @click="submit" />

        <p class="login-footer">
          Already have an account?
          <RouterLink :to="{ name: 'login' }">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive }    from 'vue'
import { useRouter }        from 'vue-router'
import InputText            from 'primevue/inputtext'
import InputGroup           from 'primevue/inputgroup'
import InputGroupAddon      from 'primevue/inputgroupaddon'
import Password             from 'primevue/password'
import Button               from 'primevue/button'
import Message              from 'primevue/message'
import Divider              from 'primevue/divider'
import { useAuthStore }     from '@/stores/auth'
import { useApi }           from '@/api/useApi'

const router = useRouter()
const auth   = useAuthStore()
const api    = useApi()

const form = reactive({
  tenant_name:           '',
  tenant_slug:           '',
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

const errors       = ref({})
const errorMessage = ref(null)
const loading      = ref(false)

async function submit() {
  errors.value       = {}
  errorMessage.value = null
  loading.value      = true

  const { data, error } = await api.post('/register', form)
  loading.value = false

  if (error) {
    errorMessage.value = error.message
    if (error.errors) errors.value = error.errors
    return
  }

  auth.setSession(data.token, data.user)
  router.push({ name: 'dashboard' })
}
</script>

<style scoped>
.login-page   { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--p-surface-ground); }
.login-card   { background: var(--p-surface-card); border: 1px solid var(--p-surface-border); border-radius: 12px; padding: 2.5rem; width: 100%; max-width: 460px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
.login-header { text-align: center; margin-bottom: 2rem; }
.login-logo   { font-size: 2rem; font-weight: 800; color: var(--p-primary-color); letter-spacing: 0.08em; }
.login-sub    { margin: 0.5rem 0 0; color: var(--p-text-muted-color); font-size: 0.9rem; }
.login-form   { display: flex; flex-direction: column; gap: 1.1rem; }
.field        { display: flex; flex-direction: column; gap: 0.375rem; }
.field label  { font-size: 0.875rem; font-weight: 500; }
.error-text   { color: var(--p-red-500); font-size: 0.8rem; }
.divider-label { font-size: 0.8rem; color: var(--p-text-muted-color); }
.login-footer { text-align: center; font-size: 0.875rem; color: var(--p-text-muted-color); margin: 0; }
.login-footer a { color: var(--p-primary-color); }
</style>