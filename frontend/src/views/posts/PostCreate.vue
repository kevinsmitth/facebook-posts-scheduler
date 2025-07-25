<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4">
                    <router-link to="/posts" class="text-gray-400 hover:text-gray-600">
                        ← Voltar
                    </router-link>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Nova Postagem
                    </h1>
                </div>
            </div>
        </header>

        <main class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <div>
                            <label for="title" class="form-label">
                                Título
                            </label>
                            <input id="title" v-model="formData.title" type="text" required class="form-input"
                                :class="{ 'border-red-500': hasFieldError('title') }"
                                placeholder="Título da postagem" />
                            <p v-if="getFieldError('title')" class="form-error">
                                {{ getFieldError('title') }}
                            </p>
                        </div>

                        <div>
                            <label for="content" class="form-label">
                                Conteúdo
                            </label>
                            <textarea id="content" v-model="formData.content" rows="4" required class="form-input"
                                :class="{ 'border-red-500': hasFieldError('content') }"
                                placeholder="Conteúdo da postagem"></textarea>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ formData.content.length }}/280 caracteres
                            </p>
                            <p v-if="getFieldError('content')" class="form-error">
                                {{ getFieldError('content') }}
                            </p>
                        </div>

                        <div>
                            <label for="scheduled_for" class="form-label">
                                Data e hora de agendamento
                            </label>
                            <input id="scheduled_for" v-model="formData.scheduled_for" type="datetime-local"
                                class="form-input" :class="{ 'border-red-500': hasFieldError('scheduled_for') }" />
                            <p v-if="getFieldError('scheduled_for')" class="form-error">
                                {{ getFieldError('scheduled_for') }}
                            </p>
                        </div>

                        <div>
                            <label for="image" class="form-label">
                                Imagem (opcional)
                            </label>
                            <input id="image" type="file" accept="image/*" class="form-input"
                                @change="handleImageChange" />
                            <p class="text-sm text-gray-500 mt-1">
                                Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB
                            </p>
                            <div v-if="imagePreview" class="mt-3">
                                <img :src="imagePreview" alt="Preview" class="max-w-xs rounded-lg border" />
                                <button type="button" @click="removeImage"
                                    class="ml-2 text-red-600 hover:text-red-800 text-sm">
                                    Remover
                                </button>
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit" :disabled="loading || isValidating" class="btn btn-primary">
                                <span v-if="loading">Criando...</span>
                                <span v-else>Criar Postagem</span>
                            </button>
                            <router-link to="/posts" class="btn btn-outline">
                                Cancelar
                            </router-link>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { usePostsStore } from '@/stores/posts'
import { useFormValidation, validationRules } from '@/composables/useFormValidation'
import { useNotifications } from '@/composables/useNotifications'
import type { CreatePostData } from '@/types'

const postsStore = usePostsStore()
const router = useRouter()
const { notifyApiResponse } = useNotifications()

const loading = ref(false)
const imagePreview = ref<string | null>(null)

const getCurrentDateTime = (): string => {
    const now = new Date()
    const year = now.getFullYear()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')
    const hours = String(now.getHours()).padStart(2, '0')
    const minutes = String(now.getMinutes()).padStart(2, '0')
    return `${year}-${month}-${day}T${hours}:${minutes}`
}

const initialData: CreatePostData = {
    title: '',
    content: '',
    scheduled_for: '',
    image: undefined
}

const rules = {
    title: [
        validationRules.required('Título é obrigatório'),
        validationRules.minLength(3, 'Título deve ter pelo menos 3 caracteres'),
        validationRules.maxLength(100, 'Título deve ter no máximo 100 caracteres')
    ],
    content: [
        validationRules.required('Conteúdo é obrigatório'),
        validationRules.minLength(10, 'Conteúdo deve ter pelo menos 10 caracteres'),
        validationRules.maxLength(280, 'Conteúdo deve ter no máximo 280 caracteres')
    ],
    image: [
        validationRules.fileSize(5, 'Imagem deve ter no máximo 5MB'),
        validationRules.fileType(['image/jpeg', 'image/png', 'image/gif'], 'Formato de imagem inválido')
    ]
}

const {
    formData,
    isValidating,
    validate,
    setErrors,
    getFieldError,
    hasFieldError
} = useFormValidation(initialData, rules)

const characterCount = computed(() => formData.content.length)
const isOverLimit = computed(() => characterCount.value > 280)

const handleImageChange = (event: Event): void => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        const file = target.files[0]
        formData.image = file

        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = (): void => {
    formData.image = undefined
    imagePreview.value = null
    const input = document.getElementById('image') as HTMLInputElement
    if (input) {
        input.value = ''
    }
}

const handleSubmit = async (): Promise<void> => {
    const validation = await validate()

    if (!validation.isValid) {
        return
    }

    loading.value = true
    try {
        const result = await postsStore.createPost(formData)
        notifyApiResponse(result)

        if (result.success) {
            await router.push('/posts')
        } else {
            if ('errors' in result && result.errors) {
                setErrors(result.errors)
            }
        }
    } finally {
        loading.value = false
    }
}
</script>