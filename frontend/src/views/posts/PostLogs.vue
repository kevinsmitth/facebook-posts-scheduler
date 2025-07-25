<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4">
                    <router-link to="/posts" class="text-gray-400 hover:text-gray-600">
                        ← Voltar
                    </router-link>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Histórico de Envios
                    </h1>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="card mb-6">
                <div class="card-body">
                    <div class="flex items-center space-x-4">
                        <button @click="refreshLogs" :disabled="loading" class="btn btn-primary">
                            <span v-if="loading">Carregando...</span>
                            <span v-else>Atualizar</span>
                        </button>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-600">Filtrar por status:</label>
                            <select v-model="statusFilter" class="form-input w-auto">
                                <option value="">Todos</option>
                                <option value="success">Sucesso</option>
                                <option value="failed">Falha</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="loading && logs.length === 0" class="text-center py-8">
                <p class="text-gray-500">Carregando histórico...</p>
            </div>

            <div v-else-if="filteredLogs.length === 0" class="text-center py-8">
                <div class="card">
                    <div class="card-body">
                        <p class="text-gray-500">Nenhum log encontrado</p>
                        <p class="text-sm text-gray-400 mt-2">
                            Os logs aparecerão aqui quando as postagens forem enviadas para o Twitter
                        </p>
                    </div>
                </div>
            </div>

            <div v-else class="space-y-4">
                <div v-for="log in filteredLogs" :key="log?.id || Math.random()" class="card">
                    <div v-if="log" class="card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ log.post?.title || 'Postagem removida' }}
                                    </h3>
                                    <span :class="getLogStatusClass(log)"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ getLogStatusText(log) }}
                                    </span>
                                </div>

                                <div v-if="log.post" class="mb-3">
                                    <p class="text-gray-600 text-sm line-clamp-2">
                                        {{ log.post.content }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500">
                                    <div>
                                        <span class="font-medium">Tentativa de envio:</span>
                                        {{ formatDateTime(log.sent_at || log.created_at) }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Data de criação:</span>
                                        {{ log.post ? formatDateTime(log.post.created_at) : 'N/A' }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Post ID:</span>
                                        #{{ log.post_id }}
                                    </div>
                                </div>

                                <div v-if="log.response" class="mt-4">
                                    <p v-if="getLogStatus(log) === 'failed'"
                                        class="text-sm font-medium text-red-700 mb-2">
                                        Mensagem de Erro:
                                    </p>
                                    <p v-else class="text-sm font-medium text-green-700 mb-2">
                                        Resposta do Twitter:
                                    </p>
                                    <div class="text-sm p-3 rounded-lg border" :class="getLogStatus(log) === 'failed'
                                        ? 'text-red-700 bg-red-50 border-red-200'
                                        : 'text-green-700 bg-green-50 border-green-200'">
                                        {{ log.response.replace(/"/g, '') }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-end space-y-3">
                                <div class="text-xs text-gray-400">
                                    ID: {{ log.id }}
                                </div>
                                <button v-if="getLogStatus(log) === 'failed' && log.post"
                                    @click="retryPost(log.post.id)"
                                    class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition-colors cursor-pointer">
                                    Tentar Novamente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredLogs.length > 0" class="mt-8">
                <div class="card">
                    <div class="card-body">
                        <div class="flex justify-between items-center text-sm text-gray-600">
                            <span>
                                Exibindo {{ filteredLogs.length }} de {{ logs.length }} logs
                            </span>
                            <div class="flex space-x-4">
                                <span class="text-green-600">
                                    ✓ {{filteredLogs.filter(log => getLogStatus(log) === 'success').length}} sucessos
                                </span>
                                <span class="text-red-600">
                                    ✗ {{filteredLogs.filter(log => getLogStatus(log) === 'failed').length}} falhas
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
import { ref, computed, onMounted } from 'vue'
import { usePostsStore } from '@/stores/posts'
import { useNotifications } from '@/composables/useNotifications'
import { formatDateTime } from '@/utils/dateUtils'
import type { SendLog } from '@/types'

const postsStore = usePostsStore()
const { notifyApiResponse } = useNotifications()

const logs = ref<SendLog[]>([])
const loading = ref(false)
const statusFilter = ref('')

const filteredLogs = computed(() => {
    const validLogs = logs.value.filter(log => log != null)

    if (!statusFilter.value) {
        return validLogs
    }

    return validLogs.filter(log => getLogStatus(log) === statusFilter.value)
})

const getLogStatusClass = (log: any): string => {
    const status = getLogStatus(log)
    return status === 'failed'
        ? 'bg-red-100 text-red-800'
        : 'bg-green-100 text-green-800'
}

const getLogStatusText = (log: any): string => {
    const status = getLogStatus(log)
    return status === 'failed' ? 'Falha' : 'Sucesso'
}

const getLogStatus = (log: any): string => {
    if (!log || !log.response) return 'unknown'
    const response = String(log.response).toLowerCase()
    const isError = response.includes('exception') || response.includes('error') || response.includes('expired')
    return isError ? 'failed' : 'success'
}

const refreshLogs = async (): Promise<void> => {
    loading.value = true
    try {
        const result = await postsStore.fetchSendLogs()

        if (result.success && result.data) {
            const logsArray = result.data.data || result.data
            logs.value = Array.isArray(logsArray) ? logsArray.filter(log => log != null) : []
        } else {
            logs.value = []
            notifyApiResponse(result)
        }
    } catch (error) {
        logs.value = []
    } finally {
        loading.value = false
    }
}

const retryPost = async (postId: number): Promise<void> => {
    const result = await postsStore.retrySendPost(postId)
    notifyApiResponse(result)

    if (result.success) {
        await refreshLogs()
    }
}

onMounted(async () => {
    await refreshLogs()
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