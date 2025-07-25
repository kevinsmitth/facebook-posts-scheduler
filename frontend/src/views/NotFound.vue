<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-gray-200">404</h1>
                <div class="text-6xl animate-bounce">😵</div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-gray-900">
                Página não encontrada
            </h2>

            <p class="mt-2 text-lg text-gray-600 max-w-md mx-auto">
                Oops! A página que você está procurando não existe ou foi movida para outro lugar.
            </p>

            <div class="mt-8 space-y-4">
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <router-link to="/dashboard" class="btn btn-primary">
                        🏠 Voltar ao Dashboard
                    </router-link>

                    <router-link to="/posts" class="btn btn-outline">
                        📝 Ver Postagens
                    </router-link>
                </div>

                <div class="mt-6">
                    <button @click="goBack" class="text-gray-500 hover:text-gray-700 text-sm">
                        ← Voltar à página anterior
                    </button>
                </div>
            </div>

            <div class="mt-12 max-w-lg mx-auto">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-blue-400 text-xl">💡</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Sugestões
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Verifique se digitou o endereço corretamente</li>
                                    <li>Use a navegação do menu para encontrar o que procura</li>
                                    <li>Entre em contato se acredita que isso é um erro</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400">
                    Erro 404 - Posts Scheduler
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const goBack = (): void => {
    if (window.history.length > 1) {
        router.back()
    } else {
        router.push('/dashboard')
    }
}
</script>

<style scoped>
@keyframes bounce {

    0%,
    20%,
    53%,
    80%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    40%,
    43% {
        transform: translate3d(0, -20px, 0);
    }

    70% {
        transform: translate3d(0, -10px, 0);
    }

    90% {
        transform: translate3d(0, -4px, 0);
    }
}

.animate-bounce {
    animation: bounce 2s infinite;
}
</style>