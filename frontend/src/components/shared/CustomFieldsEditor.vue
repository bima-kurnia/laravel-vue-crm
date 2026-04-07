<template>
  <div class="cfe">
    <div class="cfe-header">
      <span class="cfe-title">Custom Fields</span>
      <Button icon="pi pi-plus" label="Add Field" size="small" text @click="addRow" />
    </div>

    <div v-if="rows.length === 0" class="cfe-empty">
      No custom fields yet. Click "Add Field" to start.
    </div>

    <TransitionGroup name="cfe-list" tag="div" class="cfe-rows">
      <div v-for="(row, index) in rows" :key="row.uid" class="cfe-row">
        <!-- Key input -->
        <InputText
          v-model="row.key"
          placeholder="field_name"
          class="cfe-key"
          :invalid="!!keyError(index)"
          @update:modelValue="emitChange"
        />

        <!-- Type selector -->
        <Select
          v-model="row.type"
          :options="typeOptions"
          option-label="label"
          option-value="value"
          class="cfe-type"
          @change="onTypeChange(index)"
        />

        <!-- Value input — varies by type -->
        <div class="cfe-value">
          <InputText
            v-if="row.type === 'text'"
            v-model="row.value"
            placeholder="Value"
            fluid
            @update:modelValue="emitChange"
          />
          <InputNumber
            v-else-if="row.type === 'number'"
            v-model="row.value"
            mode="decimal"
            fluid
            @update:modelValue="emitChange"
          />
          <ToggleSwitch
            v-else-if="row.type === 'boolean'"
            v-model="row.value"
            @update:modelValue="emitChange"
          />
          <DatePicker
            v-else-if="row.type === 'date'"
            v-model="row.value"
            date-format="yy-mm-dd"
            fluid
            @update:modelValue="emitChange"
          />
        </div>

        <!-- Remove button -->
        <Button
          icon="pi pi-times"
          text
          severity="danger"
          size="small"
          class="cfe-remove"
          @click="removeRow(index)"
        />
      </div>
    </TransitionGroup>

    <!-- Key validation summary -->
    <small v-if="hasDuplicateKeys" class="cfe-error">
      Duplicate field names detected. Each field name must be unique.
    </small>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import InputText   from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select      from 'primevue/select'
import DatePicker  from 'primevue/datepicker'
import ToggleSwitch from 'primevue/toggleswitch'
import Button      from 'primevue/button'

const props = defineProps({
  /**
   * The current custom_data object from the model, e.g. { source: "web", score: 8 }
   */
  modelValue: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue'])

// -------------------------------------------------------------------------
// Internal row representation
// Each row: { uid, key, type, value }
// uid is a local stable key for v-for transitions
// -------------------------------------------------------------------------
let uidCounter = 0

const typeOptions = [
  { label: 'Text',    value: 'text'    },
  { label: 'Number',  value: 'number'  },
  { label: 'Boolean', value: 'boolean' },
  { label: 'Date',    value: 'date'    },
]

const rows = ref([])

// Hydrate rows from modelValue when the prop changes (e.g. edit dialog opens)
watch(() => props.modelValue, (incoming) => {
  if (!incoming || typeof incoming !== 'object') return

  // Avoid infinite loop — only reset if content actually changed
  const currentKeys = rows.value.map(r => r.key).sort().join(',')
  const incomingKeys = Object.keys(incoming).sort().join(',')
  if (currentKeys === incomingKeys) return

  rows.value = Object.entries(incoming).map(([key, value]) => ({
    uid:   ++uidCounter,
    key,
    type:  inferType(value),
    value: deserialize(value),
  }))
}, { immediate: true, deep: true })

// -------------------------------------------------------------------------
// Type inference from existing values
// -------------------------------------------------------------------------
function inferType(value) {
  if (typeof value === 'boolean')  return 'boolean'
  if (typeof value === 'number')   return 'number'
  // ISO date strings: "2024-01-15"
  if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) return 'date'
  return 'text'
}

function deserialize(value) {
  if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return new Date(value)
  }
  return value
}

// -------------------------------------------------------------------------
// Row mutations
// -------------------------------------------------------------------------
function addRow() {
  rows.value.push({ uid: ++uidCounter, key: '', type: 'text', value: '' })
}

function removeRow(index) {
  rows.value.splice(index, 1)
  emitChange()
}

function onTypeChange(index) {
  // Reset value when type changes to avoid type mismatches
  const defaults = { text: '', number: 0, boolean: false, date: null }
  rows.value[index].value = defaults[rows.value[index].type]
  emitChange()
}

// -------------------------------------------------------------------------
// Validation
// -------------------------------------------------------------------------
const hasDuplicateKeys = computed(() => {
  const keys = rows.value.map(r => r.key).filter(Boolean)
  return keys.length !== new Set(keys).size
})

function keyError(index) {
  const key = rows.value[index].key
  if (!key) return null
  const others = rows.value.filter((_, i) => i !== index).map(r => r.key)
  return others.includes(key) ? 'Duplicate key' : null
}

// -------------------------------------------------------------------------
// Emit serialised object to parent
// -------------------------------------------------------------------------
function emitChange() {
  if (hasDuplicateKeys.value) return

  const output = {}

  for (const row of rows.value) {
    if (!row.key.trim()) continue // skip blank keys

    let serialized = row.value

    if (row.type === 'date' && row.value instanceof Date) {
      serialized = row.value.toISOString().split('T')[0]
    }

    output[row.key.trim()] = serialized
  }

  emit('update:modelValue', output)
}
</script>

<style scoped>
.cfe          { display: flex; flex-direction: column; gap: 0.75rem; }
.cfe-header   { display: flex; justify-content: space-between; align-items: center; }
.cfe-title    { font-size: 0.875rem; font-weight: 600; color: var(--p-text-color); }
.cfe-empty    { font-size: 0.8rem; color: var(--p-text-muted-color); padding: 0.5rem 0; }
.cfe-rows     { display: flex; flex-direction: column; gap: 0.5rem; }
.cfe-row      { display: grid; grid-template-columns: 160px 110px 1fr 32px; gap: 0.5rem; align-items: center; }
.cfe-key      { font-family: monospace; font-size: 0.8rem; }
.cfe-value    { min-width: 0; }
.cfe-remove   { flex-shrink: 0; }
.cfe-error    { color: var(--p-red-500); font-size: 0.8rem; }

/* TransitionGroup */
.cfe-list-enter-active,
.cfe-list-leave-active  { transition: all 0.2s ease; }
.cfe-list-enter-from    { opacity: 0; transform: translateY(-6px); }
.cfe-list-leave-to      { opacity: 0; transform: translateX(12px); }
</style>