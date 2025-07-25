import {
  createRouter,
  createWebHistory,
  type RouteRecordRaw,
  type NavigationGuardNext,
  type RouteLocationNormalized,
} from 'vue-router'
import { useAuthStore } from '@/stores/auth'

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    requiresGuest?: boolean
    title?: string
    description?: string
  }
}

const Login = () => import('@/views/auth/Login.vue')
const Register = () => import('@/views/auth/Register.vue')
const Dashboard = () => import('@/views/Dashboard.vue')
const PostsList = () => import('@/views/posts/PostsList.vue')
const PostCreate = () => import('@/views/posts/PostCreate.vue')
const PostEdit = () => import('@/views/posts/PostEdit.vue')
const PostLogs = () => import('@/views/posts/PostLogs.vue')
const Profile = () => import('@/views/Profile.vue')
const NotFound = () => import('@/views/NotFound.vue')

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      requiresGuest: true,
      title: 'Login',
      description: 'Faça login em sua conta',
    },
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: {
      requiresGuest: true,
      title: 'Registro',
      description: 'Crie sua conta gratuita',
    },
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: {
      requiresAuth: true,
      title: 'Dashboard',
      description: 'Visão geral das suas postagens',
    },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: {
      requiresAuth: true,
      title: 'Perfil',
      description: 'Gerencie suas informações pessoais',
    },
  },
  {
    path: '/posts',
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'PostsList',
        component: PostsList,
        meta: {
          title: 'Postagens Agendadas',
          description: 'Gerencie suas postagens agendadas',
        },
      },
      {
        path: 'create',
        name: 'PostCreate',
        component: PostCreate,
        meta: {
          title: 'Nova Postagem',
          description: 'Crie uma nova postagem agendada',
        },
      },
      {
        path: ':id(\\d+)/edit',
        name: 'PostEdit',
        component: PostEdit,
        meta: {
          title: 'Editar Postagem',
          description: 'Edite sua postagem agendada',
        },
        props: (route) => ({
          id: parseInt(route.params.id as string, 10),
        }),
      },
      {
        path: 'logs',
        name: 'PostLogs',
        component: PostLogs,
        meta: {
          title: 'Histórico de Envios',
          description: 'Visualize o histórico de envios das postagens',
        },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: {
      title: 'Página não encontrada',
      description: 'A página que você procura não existe',
    },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  },
})

router.beforeEach(
  async (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
    const authStore = useAuthStore()

    if (authStore.token && !authStore.user) {
      try {
        await authStore.fetchUser()
      } catch (error) {
        console.warn('Erro ao buscar dados do usuário:', error)
      }
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      next({
        name: 'Login',
      })
      return
    }

    if (to.meta.requiresGuest && authStore.isAuthenticated) {
      const redirectTo = (to.query.redirect as string) || '/dashboard'
      next(redirectTo)
      return
    }

    next()
  },
)

router.afterEach((to: RouteLocationNormalized) => {
  const baseTitle = 'Posts Scheduler'
  document.title = to.meta.title ? `${to.meta.title} - ${baseTitle}` : baseTitle

  const metaDescription = document.querySelector('meta[name="description"]')
  if (metaDescription && to.meta.description) {
    metaDescription.setAttribute('content', to.meta.description)
  }
})

router.onError((error: Error) => {
  console.error('Erro de navegação:', error)

  if (import.meta.env.PROD) {
  }
})

export default router
