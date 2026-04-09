<template>
  <div class="bell-wrap" ref="bellRef">
    <!-- Bell button -->
    <button 
      class="bell-btn" 
      :class="{ 'has-unread': unreadCount > 0 }" @click="toggle"
    >
      <i class="pi pi-bell" />

      <span v-if="unreadCount > 0" class="bell-badge">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown panel -->
    <Transition name="panel">
      <div v-if="open" class="notif-panel">
        <div class="notif-header">
          <span class="notif-title">Notifications</span>
          <button
            v-if="unreadCount > 0"
            class="notif-read-all"
            @click="handleMarkAllRead"
          >
            Mark all read
          </button>
        </div>

        <div v-if="loading" class="notif-loading">
          <ProgressSpinner style="width:28px;height:28px" />
        </div>

        <div
          v-else-if="notifications.length === 0"
          class="notif-empty"
        >
          <i class="pi pi-check-circle notif-empty-icon" />
          <span>All caught up</span>
        </div>

        <ul v-else class="notif-list">
          <li
            v-for="n in notifications"
            :key="n.id"
            class="notif-item"
            :class="{ unread: !n.is_read }"
            @click="handleRead(n)"
          >
            <div class="notif-dot-wrap">
              <span class="notif-dot" :class="{ visible: !n.is_read }" />
            </div>
            <div class="notif-body">
              <p class="notif-item-title">{{ n.title }}</p>
              <p v-if="n.body" class="notif-item-body">{{ n.body }}</p>
              <p class="notif-item-time">{{ timeAgo(n.created_at) }}</p>
            </div>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { storeToRefs }                 from 'pinia'
import ProgressSpinner                 from 'primevue/progressspinner'
import { useNotificationStore }        from '@/stores/notifications'

const store = useNotificationStore()
const { notifications, loading, unreadCount } = storeToRefs(store)

const open    = ref(false)
const bellRef = ref(null)

// Fetch on mount, then poll every 60s
let pollInterval = null

onMounted(() => {
  store.fetchAll()

  pollInterval = setInterval(store.fetchAll, 60_000)

  document.addEventListener('click', onClickOutside)
})

onUnmounted(() => {
  clearInterval(pollInterval)

  document.removeEventListener('click', onClickOutside)
})

function toggle() {
  open.value = !open.value
}

function onClickOutside(e) {
  if (bellRef.value && !bellRef.value.contains(e.target)) {
    open.value = false
  }
}

async function handleRead(notification) {
  if (!notification.is_read) {
    await store.markRead(notification.id)
  }

  // Navigate to subject if data contains an id
  open.value = false
}

async function handleMarkAllRead() {
  await store.markAllRead()
}

function timeAgo(iso) {
  if (!iso) return ''

  const diff = Date.now() - new Date(iso).getTime()

  const m = Math.floor(diff / 60_000)
  if (m < 1)   return 'just now'
  if (m < 60)  return `${m}m ago`

  const h = Math.floor(m / 60)
  if (h < 24)  return `${h}h ago`

  return `${Math.floor(h / 24)}d ago`
}
</script>

<style scoped>
.bell-wrap { position: relative; }

.bell-btn {
  position: relative;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--p-text-muted-color);
  transition: background .15s, color .15s;
}
.bell-btn:hover,
.bell-btn.has-unread { color: var(--p-primary-color); }
.bell-btn:hover      { background: var(--p-surface-hover); }

.bell-btn i { font-size: 1.1rem; }

.bell-badge {
  position: absolute;
  top: 3px;
  right: 3px;
  min-width: 16px;
  height: 16px;
  border-radius: 8px;
  background: var(--p-red-500);
  color: #fff;
  font-size: 10px;
  font-weight: 600;
  line-height: 16px;
  text-align: center;
  padding: 0 3px;
}

.notif-panel {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 340px;
  max-height: 480px;
  background: var(--p-surface-card, #fff);
  border: 1px solid var(--p-surface-border);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,.08);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 1000;
}

.notif-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .875rem 1rem .75rem;
  border-bottom: 1px solid var(--p-surface-border);
  flex-shrink: 0;
}
.notif-title    { font-size: .875rem; font-weight: 600; color: var(--p-text-color); }
.notif-read-all {
  font-size: .75rem;
  color: var(--p-primary-color);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}
.notif-read-all:hover { text-decoration: underline; }

.notif-loading,
.notif-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: 2.5rem 1rem;
  color: var(--p-text-muted-color);
  font-size: .85rem;
}
.notif-empty-icon { font-size: 1.5rem; color: var(--p-green-400); }

.notif-list {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
  flex: 1;
}

.notif-item {
  display: flex;
  gap: .625rem;
  padding: .75rem 1rem;
  cursor: pointer;
  transition: background .12s;
  border-bottom: 1px solid var(--p-surface-border);
}
.notif-item:last-child   { border-bottom: none; }
.notif-item:hover        { background: var(--p-surface-hover); }
.notif-item.unread       { background: var(--p-primary-50); }
.notif-item.unread:hover { background: var(--p-primary-100); }

.notif-dot-wrap { padding-top: 5px; flex-shrink: 0; }
.notif-dot {
  display: block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: transparent;
}
.notif-dot.visible { background: var(--p-primary-color); }

.notif-body           { flex: 1; min-width: 0; }
.notif-item-title     { margin: 0; font-size: .825rem; font-weight: 600; color: var(--p-text-color); }
.notif-item-body      { margin: .15rem 0 0; font-size: .775rem; color: var(--p-text-muted-color); line-height: 1.4; }
.notif-item-time      { margin: .3rem 0 0; font-size: .725rem; color: var(--p-text-muted-color); }

.panel-enter-active,
.panel-leave-active  { transition: opacity .15s, transform .15s; }
.panel-enter-from,
.panel-leave-to      { opacity: 0; transform: translateY(-6px); }
</style>