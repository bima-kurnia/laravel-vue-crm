<template>
  <div class="form-body">
    <div class="field">
      <label>Name *</label>
      <InputText v-model="form.name" :invalid="!!errors.name" fluid />
      <small v-if="errors.name" class="error-text">{{ errors.name[0] }}</small>
    </div>
    <div class="field">
      <label>Email</label>
      <InputText v-model="form.email" type="email" fluid />
    </div>
    <div class="field">
      <label>Phone</label>
      <InputText v-model="form.phone" fluid />
    </div>
    <div class="field">
      <label>Company</label>
      <InputText v-model="form.company" fluid />
    </div>
    <div class="field">
      <label>Status</label>
      <Select
        v-model="form.status"
        :options="statusOptions"
        option-label="label"
        option-value="value"
        fluid
      />
    </div>

    <Divider />

    <CustomFieldsEditor v-model="form.custom_data" />

    <div class="form-actions">
      <Button label="Cancel" severity="secondary" text @click="$emit('cancel')" />
      <Button label="Save" :loading="loading" @click="submit" />
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import InputText from 'primevue/inputtext'
import Select    from 'primevue/select'
import Button    from 'primevue/button'
import Divider   from 'primevue/divider'
import CustomFieldsEditor from '@/components/shared/CustomFieldsEditor.vue'

const props = defineProps({
  initial: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  errors:  { type: Object,  default: () => ({}) },
})

const emit = defineEmits(['submit', 'cancel'])

const statusOptions = [
  { label: 'Active',   value: 'active'   },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Lead',     value: 'lead'     },
]

const form = reactive({
  name:    '',
  email:   '',
  phone:   '',
  company: '',
  status:  'active',
  custom_data: {}, 
})

// Sync when initial changes (e.g. opening edit dialog)
watch(() => props.initial, (val) => {
  Object.assign(form, {
    name:    val.name    ?? '',
    email:   val.email   ?? '',
    phone:   val.phone   ?? '',
    company: val.company ?? '',
    status:  val.status  ?? 'active',
    custom_data: val.custom_data ?? {},
  })
}, { immediate: true })

function submit() {
  // Strip empty strings to allow nullable fields
  const payload = Object.fromEntries(
    Object.entries(form).filter(([, v]) => v !== '')
  )
  emit('submit', payload)
}
</script>

<style scoped>
.form-body { display: flex; flex-direction: column; gap: 1rem; padding: 0.25rem 0; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.875rem; font-weight: 500; }
.error-text  { color: var(--p-red-500); font-size: 0.8rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; padding-top: 0.5rem; }
</style>