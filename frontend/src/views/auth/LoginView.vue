<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <span class="login-logo">CRM</span>
        <p class="login-sub">Sign in to your account</p>
      </div>

      <div class="login-form">
        <div class="field">
          <label for="email">Email</label>
          <InputText
            id="email"
            v-model="form.email"
            type="email"
            placeholder="you@company.com"
            :invalid="!!errors.email"
            fluid
            @keyup.enter="submit"
          />
          <small v-if="errors.email" class="error-text">{{ errors.email[0] }}</small>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <Password
            id="password"
            v-model="form.password"
            placeholder="Your password"
            :feedback="false"
            :invalid="!!errors.password"
            fluid
            toggleMask
            @keyup.enter="submit"
          />
          <small v-if="errors.password" class="error-text">{{ errors.password[0] }}</small>
        </div>

        <Message v-if="errorMessage" severity="error" :closable="false">
          {{ errorMessage }}
        </Message>

        <Button
          label="Sign In"
          icon="pi pi-sign-in"
          :loading="loading"
          fluid
          @click="submit"
        />

        <p class="login-footer">
          Don't have an account?
          <RouterLink :to="{ name: 'register' }">Sign up</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import InputText  from 'primevue/inputtext'
import Password   from 'primevue/password'
import Button     from 'primevue/button'
import Message    from 'primevue/message'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

const form = reactive({ email: '', password: '' })
const errors = ref({})
const errorMessage = ref(null)
const loading = ref(false)

async function submit() {
  errors.value = {}
  errorMessage.value = null
  loading.value = true

  const { error } = await auth.login(form.email, form.password)

  loading.value = false

  if (error) {
    errorMessage.value = error.message
    if (error.errors) errors.value = error.errors
    return
  }

  const redirect = route.query.redirect ?? '/'
  router.push(redirect)
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--p-surface-ground);
}
.login-card {
  background: var(--p-surface-card);
  border: 1px solid var(--p-surface-border);
  border-radius: 12px;
  padding: 2.5rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
}
.login-header {
  text-align: center;
  margin-bottom: 2rem;
}
.login-logo {
  font-size: 2rem;
  font-weight: 800;
  color: var(--p-primary-color);
  letter-spacing: 0.08em;
}
.login-sub {
  margin: 0.5rem 0 0;
  color: var(--p-text-muted-color);
  font-size: 0.9rem;
}
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.login-footer { 
  text-align: center; 
  font-size: 0.875rem; 
  color: var(--p-text-muted-color); 
  margin: 0; 
}
.login-footer a { 
  color: var(--p-primary-color); 
}
.field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}
.field label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--p-text-color);
}
.error-text {
  color: var(--p-red-500);
  font-size: 0.8rem;
}
</style>