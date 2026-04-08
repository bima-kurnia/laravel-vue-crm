import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NProgress from 'nprogress'

NProgress.configure({
  showSpinner: false, // bar only, no spinner
  speed: 300,
  minimum: 0.1,
})

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
  NProgress.start()

  const auth = useAuthStore()

  // If we have a token but no user yet, rehydrate from /me
  if (auth.token && !auth.user) {
    await auth.fetchMe()
  }

  // If user is not authenticated
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    NProgress.done()

    return { name: 'login', query: { redirect: to.fullPath } }
  }

  // If user is authenticated
  if (to.meta.public && auth.isAuthenticated) {
    NProgress.done()

    return { name: 'dashboard' }
  }
})

router.afterEach(() => {
  NProgress.done()
})

export default router