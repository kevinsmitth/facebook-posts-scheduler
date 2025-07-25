<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Dashboard
                    </h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">
                            Olá, {{ authStore.userName }}!
                        </span>
                        <button @click="handleLogout" class="btn btn-outline">
                            Sair
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ totalPosts }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total de Posts</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ totalPosts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ scheduledCount }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Agendados</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ scheduledCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ sentCount }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Enviados</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ sentCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ failedCount }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Falharam</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ failedCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-medium text-gray-900">Ações Rápidas</h2>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <router-link to="/posts/create" class="btn btn-primary block text-center">
                                Nova Postagem
                            </router-link>
                            <router-link to="/posts" class="btn btn-outline block text-center">
                                Ver Todas as Postagens
                            </router-link>
                            <router-link to="/posts/logs" class="btn btn-outline block text-center">
                                Histórico de Envios
                            </router-link>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-medium text-gray-900">Próximas Postagens</h2>
                    </div>
                    <div class="card-body">
                        <div v-if="loading" class="text-center py-4">
                            <p class="text-gray-500">Carregando...</p>
                        </div>
                        <div v-else-if="scheduledPosts.length === 0" class="text-center py-4">
                            <p class="text-gray-500">Nenhuma postagem agendada</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="post in scheduledPosts.slice(0, 5)" :key="post.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ post.title }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ formatDateTime(post.scheduled_at) }}
                                    </p>
                                </div>
                                <span class="badge-scheduled">
                                    Agendado
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePostsStore } from '@/stores/posts'
import { useNotifications } from '@/composables/useNotifications'
import { formatDateTime } from '@/utils/dateUtils'

const authStore = useAuthStore()
const postsStore = usePostsStore()
const router = useRouter()
const { notifySuccess } = useNotifications()

const totalPosts = computed(() => postsStore.totalPosts)
const scheduledPosts = computed(() => postsStore.scheduledPosts)
const scheduledCount = computed(() => postsStore.scheduledPosts.length)
const sentCount = computed(() => postsStore.sentPosts.length)
const failedCount = computed(() => postsStore.failedPosts.length)
const loading = computed(() => postsStore.loading)

const handleLogout = async (): Promise<void> => {
    await authStore.logout()
    notifySuccess('Logout realizado com sucesso!')
    await router.push('/login')
}

onMounted(async () => {
    await postsStore.fetchPosts()
})
</script>