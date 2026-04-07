<template>
  <Tag :value="label" :severity="severity" />
</template>

<script setup>
import Tag from 'primevue/tag'
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
})

const map = {
  active:   { label: 'Active',   severity: 'success'   },
  inactive: { label: 'Inactive', severity: 'secondary' },
  lead:     { label: 'Lead',     severity: 'info'      },
}

const label    = computed(() => map[props.status]?.label    ?? props.status)
const severity = computed(() => map[props.status]?.severity ?? 'secondary')
</script>