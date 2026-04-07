<template>
  <Tag :value="label" :severity="severity" />
</template>

<script setup>
import Tag from 'primevue/tag'
import { computed } from 'vue'

const props = defineProps({
  stage: { type: String, required: true },
})

const map = {
  prospecting:   { label: 'Prospecting',   severity: 'secondary' },
  qualification: { label: 'Qualification', severity: 'info'      },
  proposal:      { label: 'Proposal',      severity: 'warn'      },
  negotiation:   { label: 'Negotiation',   severity: 'contrast'  },
  closed:        { label: 'Closed',        severity: 'success'   },
}

const label    = computed(() => map[props.stage]?.label    ?? props.stage)
const severity = computed(() => map[props.stage]?.severity ?? 'secondary')
</script>