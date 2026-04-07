import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path:      '/login',
    name:      'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta:      { public: true },
  },
  {
    path:      '/register',
    name:      'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta:      { public: true },
  },
  {
    path:      '/',
    component: () => import('@/components/layout/AppShell.vue'),
    meta:      { requiresAuth: true },
    children: [
      {
        path:      '',
        name:      'dashboard',
        component: () => import('@/views/DashboardView.vue'),
      },
      {
        path:      'customers',
        name:      'customers',
        component: () => import('@/views/customers/CustomersView.vue'),
      },
      {
        path:      'customers/:id',
        name:      'customer-detail',
        component: () => import('@/views/customers/CustomerDetailView.vue'),
      },
      {
        path:      'deals',
        name:      'deals',
        component: () => import('@/views/deals/DealsView.vue'),
      },
      {
        path:      'deals/:id',
        name:      'deal-detail',
        component: () => import('@/views/deals/DealDetailView.vue'),
      },
      {
        path:      'activities',
        name:      'activities',
        component: () => import('@/views/activities/ActivitiesView.vue'),
      },
    ],
  },
  {
    path:     '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // If we have a token but no user yet, rehydrate from /me
  if (auth.token && !auth.user) {
    await auth.fetchMe()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.public && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router