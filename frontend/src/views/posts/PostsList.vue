<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <router-link to="/dashboard"
                            class="text-gray-400 hover:text-gray-600 text-sm cursor-pointer mr-2">
                            ← Voltar
                        </router-link>
                        Postagens Agendadas
                    </h1>
                    <router-link to="/posts/create" class="btn btn-primary cursor-pointer">
                        Nova Postagem
                    </router-link>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="card mb-6">
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="form-label">Status</label>
                            <select v-model="filters.status" class="form-input">
                                <option value="">Todos</option>
                                <option value="scheduled">Agendados</option>
                                <option value="sent">Enviados</option>
                                <option value="failed">Falharam</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Buscar</label>
                            <input v-model="filters.search" type="text" placeholder="Título ou conteúdo..."
                                class="form-input" />
                        </div>
                        <div>
                            <label class="form-label">Data inicial</label>
                            <input v-model="filters.date_from" type="date" class="form-input" />
                        </div>
                        <div>
                            <label class="form-label">Data final</label>
                            <input v-model="filters.date_to" type="date" class="form-input" />
                        </div>
                    </div>
                    <div class="mt-4 flex space-x-3">
                        <button @click="applyFilters" class="btn btn-primary cursor-pointer">
                            Filtrar
                        </button>
                        <button @click="clearFilters" class="btn btn-outline cursor-pointer">
                            Limpar
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-center py-8">
                <p class="text-gray-500">Carregando postagens...</p>
            </div>

            <div v-else-if="posts.length === 0" class="text-center py-8">
                <p class="text-gray-500">Nenhuma postagem encontrada</p>
                <router-link to="/posts/create" class="btn btn-primary mt-4 inline-block cursor-pointer">
                    Criar primeira postagem
                </router-link>
            </div>

            <div v-else class="space-y-4">
                <div v-for="post in posts" :key="post.id" class="card">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- ID do post -->
                                <p class="text-sm text-gray-600 mb-2">
                                    #{{ post.id }}
                                </p>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    {{ post.title }}
                                </h3>
                                <p class="text-gray-600 mb-4 line-clamp-2">
                                    {{ post.content }}
                                </p>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span v-if="post.scheduled_for">
                                        Agendado para: {{ formatDateTime(post.scheduled_for) }}
                                    </span>
                                    <span v-if="post.sent_at">
                                        Enviado em: {{ formatDateTime(post.sent_at) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <span :class="getStatusBadgeClass(post.status)" class="badge">
                                    {{ getStatusText(post.status) }}
                                </span>
                                <div class="flex space-x-4">
                                    <router-link :to="`/posts/logs`"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        Visualizar Logs
                                    </router-link>
                                    <button v-if="post.status === 'failed'" @click="retryPost(post.id)"
                                        class="text-yellow-600 hover:text-yellow-900 text-sm">
                                        Reenviar
                                    </button>
                                    <button @click="deletePost(post.id)"
                                        class="text-red-600 hover:text-red-900 text-sm">
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center">
                <nav class="flex space-x-2">
                    <button v-if="pagination.current_page > 1" @click="goToPage(pagination.current_page - 1)"
                        class="px-3 py-2 text-sm rounded-md bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                        ← Anterior
                    </button>
                    <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="[
                        'px-3 py-2 text-sm rounded-md',
                        page === pagination.current_page
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-50 cursor-pointer'
                    ]">
                        {{ page }}
                    </button>
                    <button v-if="pagination.current_page < pagination.last_page"
                        @click="goToPage(pagination.current_page + 1)"
                        class="px-3 py-2 text-sm rounded-md bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Próxima →
                    </button>
                </nav>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { usePostsStore } from '@/stores/posts'
import { useNotifications } from '@/composables/useNotifications'
import type { PostFilters, PostStatus } from '@/types'

const postsStore = usePostsStore()
const { notifyApiResponse } = useNotifications()

const filters = ref<PostFilters>({
    status: undefined,
    search: '',
    date_from: '',
    date_to: ''
})

const posts = computed(() => postsStore.posts || [])
const loading = computed(() => postsStore.loading || false)
const pagination = computed(() => postsStore.pagination || { current_page: 1, last_page: 1 })

const formatDateTime = (dateString: string | null): string => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const visiblePages = computed(() => {
    const current = pagination.value.current_page
    const last = pagination.value.last_page
    const pages: number[] = []

    if (last <= 5) {
        for (let i = 1; i <= last; i++) {
            pages.push(i)
        }
    } else {
        const start = Math.max(1, current - 2)
        const end = Math.min(last, current + 2)

        for (let i = start; i <= end; i++) {
            pages.push(i)
        }
    }

    return pages
})

const getStatusBadgeClass = (status: PostStatus): string => {
    const classes = {
        scheduled: 'badge-scheduled',
        sent: 'badge-sent',
        failed: 'badge-failed'
    }
    return classes[status] || 'badge'
}

const getStatusText = (status: PostStatus): string => {
    const texts = {
        scheduled: 'Agendado',
        sent: 'Enviado',
        failed: 'Falhou'
    }
    return texts[status] || status
}

const applyFilters = async (): Promise<void> => {
    await postsStore.fetchPosts(1, filters.value)
}

const clearFilters = async (): Promise<void> => {
    filters.value = {
        status: undefined,
        search: '',
        date_from: '',
        date_to: ''
    }
    await postsStore.fetchPosts()
}

const goToPage = async (page: number): Promise<void> => {
    await postsStore.fetchPosts(page, filters.value)
}

const retryPost = async (id: number): Promise<void> => {
    const result = await postsStore.retrySendPost(id)
    notifyApiResponse(result)

    await postsStore.fetchPosts()
}

const deletePost = async (id: number): Promise<void> => {
    if (!confirm('Tem certeza que deseja excluir esta postagem?')) {
        return
    }

    const result = await postsStore.deletePost(id)
    notifyApiResponse(result)
}

watch(
    () => filters.value.search,
    () => {
        const timeoutId = setTimeout(() => {
            applyFilters()
        }, 500)

        return () => clearTimeout(timeoutId)
    }
)

onMounted(async () => {
    await postsStore.fetchPosts()
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>