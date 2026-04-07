<template>
  <div class="form-body">
    <div class="field">
      <label>Title *</label>
      <InputText v-model="form.title" :invalid="!!errors.title" fluid />
      <small v-if="errors.title" class="error-text">{{ errors.title[0] }}</small>
    </div>

    <!-- Customer autocomplete -->
    <div class="field">
      <label>Customer *</label>
      <AutoComplete
        v-model="customerQuery"
        :suggestions="customerSuggestions"
        option-label="name"
        placeholder="Search customers name, email, or company…"
        :invalid="!!errors.customer_id"
        fluid
        force-selection
        @complete="searchCustomers"
        @option-select="onCustomerSelect"
      >
        <template #option="{ option }">
          <div class="ac-option">
            <span class="ac-name">{{ option.name }}{{ option.company ? ` - ${option.company}` : '' }}</span>
            <span class="ac-sub">{{ option.email }}</span>
          </div>
        </template>
      </AutoComplete>
      <small v-if="errors.customer_id" class="error-text">{{ errors.customer_id[0] }}</small>
    </div>

    <!-- Owner autocomplete -->
    <div class="field">
      <label>Owner *</label>
      <AutoComplete
        v-model="ownerQuery"
        :suggestions="ownerSuggestions"
        option-label="name"
        placeholder="Search owners name or email…"
        :invalid="!!errors.owner_id"
        fluid
        force-selection
        @complete="searchOwners"
        @option-select="onOwnerSelect"
      >
        <template #option="{ option }">
          <div class="ac-option">
            <span class="ac-name">{{ option.name }}</span>
            <span class="ac-sub">{{ option.email }}</span>
          </div>
        </template>
      </AutoComplete>
      <small v-if="errors.owner_id" class="error-text">{{ errors.owner_id[0] }}</small>
    </div>

    <div class="field-row">
      <!-- Value -->
      <div class="field">
        <label>Value</label>
        <InputNumber v-model="form.value" mode="decimal" :min-fraction-digits="2" fluid />
      </div>

      <!-- Currency dropdown -->
      <div class="field">
        <label>Currency</label>
        <Select
          v-model="form.currency"
          :options="currencyOptions"
          option-label="label"
          option-value="value"
          placeholder="Select currency"
          filter
          fluid
        >
          <template #value="{ value }">
            <span v-if="value">{{ value }}</span>
          </template>
          <template #option="{ option }">
            <div class="currency-option">
              <span class="currency-code">{{ option.value }}</span>
              <span class="currency-name">{{ option.label }}</span>
            </div>
          </template>
        </Select>
      </div>
    </div>

    <div class="field">
      <label>Stage</label>
      <Select
        v-model="form.stage"
        :options="stageOptions"
        option-label="label"
        option-value="value"
        fluid
      />
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

    <div class="field">
      <label>Expected Close Date</label>
      <DatePicker v-model="form.expected_close_date" date-format="yy-mm-dd" fluid />
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
import { reactive, ref, watch } from 'vue'
import { useDebounceFn }  from '@vueuse/core'
import InputText          from 'primevue/inputtext'
import InputNumber        from 'primevue/inputnumber'
import Select             from 'primevue/select'
import AutoComplete       from 'primevue/autocomplete'
import DatePicker         from 'primevue/datepicker'
import Button             from 'primevue/button'
import Divider            from 'primevue/divider'
import { useApi }         from '@/api/useApi'
import CustomFieldsEditor from '@/components/shared/CustomFieldsEditor.vue'

const props = defineProps({
  initial: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  errors:  { type: Object,  default: () => ({}) },
})

const emit = defineEmits(['submit', 'cancel'])
const api  = useApi()

// -------------------------------------------------------------------------
// Currency options
// -------------------------------------------------------------------------
const CURRENCIES = {
  USD: 'United States Dollar',
  EUR: 'Euro',
  JPY: 'Japanese Yen',
  GBP: 'British Pound Sterling',
  CNY: 'Chinese Yuan Renminbi',
  AUD: 'Australian Dollar',
  CAD: 'Canadian Dollar',
  CHF: 'Swiss Franc',
  SGD: 'Singapore Dollar',
  HKD: 'Hong Kong Dollar',
  INR: 'Indian Rupee',
  KRW: 'South Korean Won',
  IDR: 'Indonesian Rupiah',
  SAR: 'Saudi Riyal',
  AED: 'United Arab Emirates Dirham',
}

const currencyOptions = Object.entries(CURRENCIES).map(([value, label]) => ({ value, label }))

// -------------------------------------------------------------------------
// Stage / Status options
// -------------------------------------------------------------------------
const stageOptions = [
  { label: 'Prospecting',   value: 'prospecting'   },
  { label: 'Qualification', value: 'qualification'  },
  { label: 'Proposal',      value: 'proposal'       },
  { label: 'Negotiation',   value: 'negotiation'    },
  { label: 'Closed',        value: 'closed'         },
]

const statusOptions = [
  { label: 'Open',    value: 'open'    },
  { label: 'Won',     value: 'won'     },
  { label: 'Lost',    value: 'lost'    },
  { label: 'Stalled', value: 'stalled' },
]

// -------------------------------------------------------------------------
// Form state
// -------------------------------------------------------------------------
const form = reactive({
  title:               '',
  customer_id:         null,
  owner_id:            null,
  value:               0,
  currency:            'USD',
  stage:               'prospecting',
  status:              'open',
  expected_close_date: null,
  custom_data:         {}, 
})

// Autocomplete display models (object or string while typing)
const customerQuery       = ref(null)
const ownerQuery          = ref(null)
const customerSuggestions = ref([])
const ownerSuggestions    = ref([])

// Sync form when initial prop changes (edit mode)
watch(() => props.initial, (val) => {
  Object.assign(form, {
    title:               val.title               ?? '',
    customer_id:         val.customer_id         ?? null,
    owner_id:            val.owner_id            ?? null,
    value:               parseFloat(val.value)   || 0,
    currency:            val.currency            ?? 'USD',
    stage:               val.stage               ?? 'prospecting',
    status:              val.status              ?? 'open',
    expected_close_date: val.expected_close_date ?? null,
    custom_data:         val.custom_data         ?? {},
  })

  // Pre-populate autocomplete display values from the initial object
  customerQuery.value = val.customer ? { id: val.customer_id, name: val.customer.name } : null
  ownerQuery.value    = val.owner    ? { id: val.owner_id,    name: val.owner.name    } : null
}, { immediate: true })

// -------------------------------------------------------------------------
// Customer autocomplete
// -------------------------------------------------------------------------
const debouncedCustomerSearch = useDebounceFn(async (query) => {
  if (!query || query.length < 2) {
    customerSuggestions.value = []
    return
  }
  const { data } = await api.get(`/customers?search=${encodeURIComponent(query)}&per_page=10`)
  customerSuggestions.value = data?.data ?? []
}, 300)

function searchCustomers(event) {
  debouncedCustomerSearch(event.query)
}

function onCustomerSelect(event) {
  form.customer_id  = event.value.id
  customerQuery.value = event.value
}

// -------------------------------------------------------------------------
// Owner (users) autocomplete
// Note: A /api/users?search= endpoint is needed — see backend addition below.
// -------------------------------------------------------------------------
const debouncedOwnerSearch = useDebounceFn(async (query) => {
  if (!query || query.length < 2) {
    ownerSuggestions.value = []
    return
  }
  const { data } = await api.get(`/users?search=${encodeURIComponent(query)}&per_page=10`)
  ownerSuggestions.value = data?.data ?? []
}, 300)

function searchOwners(event) {
  debouncedOwnerSearch(event.query)
}

function onOwnerSelect(event) {
  form.owner_id  = event.value.id
  ownerQuery.value = event.value
}

// -------------------------------------------------------------------------
// Submit
// -------------------------------------------------------------------------
function submit() {
  const payload = { ...form }

  if (payload.expected_close_date instanceof Date) {
    payload.expected_close_date = payload.expected_close_date.toISOString().split('T')[0]
  }

  emit('submit', payload)
}
</script>

<style scoped>
.form-body    { display: flex; flex-direction: column; gap: 1rem; padding: 0.25rem 0; }
.field        { display: flex; flex-direction: column; gap: 0.375rem; flex: 1; }
.field label  { font-size: 0.875rem; font-weight: 500; }
.field-row    { display: flex; gap: 1rem; }
.error-text   { color: var(--p-red-500); font-size: 0.8rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; padding-top: 0.5rem; }

.ac-option    { display: flex; flex-direction: column; gap: 0.1rem; }
.ac-name      { font-weight: 500; font-size: 0.875rem; }
.ac-sub       { font-size: 0.775rem; color: var(--p-text-muted-color); }

.currency-option { display: flex; align-items: center; gap: 0.75rem; }
.currency-code   { font-weight: 600; font-size: 0.875rem; min-width: 2.5rem; }
.currency-name   { font-size: 0.8rem; color: var(--p-text-muted-color); }
</style>