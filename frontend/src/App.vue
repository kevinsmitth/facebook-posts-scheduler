<template>
    <div id="app">
        <router-view />
        <NotificationContainer />
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import NotificationContainer from '@/components/ui/NotificationContainer.vue'

const authStore = useAuthStore()

onMounted(async () => {
    if (authStore.token && !authStore.user) {
        try {
            await authStore.fetchUser()
        } catch (error) {
            console.warn('Erro ao verificar usuário logado:', error)
        }
    }
})
</script>