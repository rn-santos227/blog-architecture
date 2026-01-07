import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/home/HomeView.vue'
import PostDetailView from '@/views/post/PostDetailView.vue'
import PostPage from '@/views/post/PostPage.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/posts/:id',
      name: 'post-detail',
      component: PostDetailView,
    },
    {
      path: '/posts/mine',
      name: 'post-mine',
      component: PostPage,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
  ],
})

router.beforeEach((to) => {
  const authStore = useAuthStore()
  authStore.loadFromStorage()

  if (to.name === 'register' && authStore.isAuthenticated) {
    return { name: 'post-mine' }
  }

  if (!to.meta.requiresAuth) return true

  if (!authStore.isAuthenticated) {
    return { name: 'home' }
  }

  return true
})

export default router
