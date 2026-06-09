// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'
import Cookies from 'js-cookie'
import TheHomeView from '@/views/TheHomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: TheHomeView
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/RegisterView.vue'),
      meta: { guest: true }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('@/views/AboutView.vue')
    },
    {
      path: '/contents',
      name: 'contents',
      component: () => import('@/views/ContentsView.vue'),
    },
    {
      path: '/contents/:id',
      name: 'content-detail',
      component: () => import('@/views/ContentDetailView.vue'),
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/ProfileView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/auth/github/callback',
      name: 'github-callback',
      component: () => import('@/views/GitHubCallback.vue')
    },
    {
      path: '/auth/google/callback',
      name: 'google-callback',
      component: () => import('@/views/GoogleCallback.vue')
    },
    {
      path: '/admin',
      name: 'admin-dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/contents',
      name: 'admin-contents',
      component: () => import('@/views/AdminContentsView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/contents/create',
      name: 'admin-contents-create',
      component: () => import('@/views/AdminContentFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/contents/edit/:id',
      name: 'admin-contents-edit',
      component: () => import('@/views/AdminContentFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: () => import('@/views/AdminUsersView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/reviews',
      name: 'admin-reviews',
      component: () => import('@/views/AdminReviewsView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue')
    }
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

router.beforeEach(async (to, from) => {
  const userStore = useUserStore()
  const token = Cookies.get('auth_token')

  if (token && !userStore.user) {
    await userStore.fetchUser()
  }

  const isAuthenticated = userStore.isAuthenticated
  const user = userStore.user
  const isAdmin = user?.role_id === 1

  // Проверка админских маршрутов
  if (to.meta.requiresAdmin) {
    if (!isAuthenticated) {
      return { path: '/login', query: { redirect: to.fullPath } }
    } else if (!isAdmin) {
      return { path: '/' }
    }
    return true
  }

  // Редирект админа с главной на админку
  if (isAdmin && to.path === '/') {
    return { path: '/admin' }
  }

  // Защита гостевых маршрутов (login/register)
  if (to.meta.guest && isAuthenticated) {
    return { path: '/' }
  }

  // Проверка авторизации
  if (to.meta.requiresAuth && !isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  // Разрешаем переход
  return true
})

export default router