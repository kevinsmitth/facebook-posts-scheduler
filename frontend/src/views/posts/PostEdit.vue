<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4">
                    <router-link to="/posts" class="text-gray-400 hover:text-gray-600">
                        ← Voltar
                    </router-link>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Editar Postagem
                    </h1>
                </div>
            </div>
        </header>

        <main class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
            <div v-if="loading && !currentPost" class="text-center py-8">
                <p class="text-gray-500">Carregando...</p>
            </div>

            <div v-else-if="!currentPost" class="text-center py-8">
                <p class="text-red-500">Postagem não encontrada</p>
                <router-link to="/posts" class="btn btn-primary mt-4">
                    Voltar às Postagens
                </router-link>
            </div>

            <div v-else class="card">
                <div class="card-body">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <div>
                            <label for="title" class="form-label">
                                Título
                            </label>
                            <input id="title" v-model="formData.title" type="text" required class="form-input"
                                :class="{ 'border-red-500': hasFieldError('title') }" />
                            <p v-if="getFieldError('title')" class="form-error">
                                {{ getFieldError('title') }}
                            </p>
                        </div>

                        <div>
                            <label for="content" class="form-label">
                                Conteúdo
                            </label>
                            <textarea id="content" v-model="formData.content" rows="4" required class="form-input"
                                :class="{ 'border-red-500': hasFieldError('content') }"></textarea>
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

                            <div v-if="currentPost.image_url && !imagePreview" class="mb-3">
                                <p class="text-sm text-gray-600 mb-2">Imagem atual:</p>
                                <img :src="currentPost.image_url" alt="Imagem atual"
                                    class="max-w-xs rounded-lg border" />
                                <button type="button" @click="removeCurrentImage"
                                    class="ml-2 text-red-600 hover:text-red-800 text-sm">
                                    Remover imagem atual
                                </button>
                            </div>

                            <input id="image" type="file" accept="image/*" class="form-input"
                                @change="handleImageChange" />
                            <p class="text-sm text-gray-500 mt-1">
                                Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB
                            </p>

                            <div v-if="imagePreview" class="mt-3">
                                <p class="text-sm text-gray-600 mb-2">Nova imagem:</p>
                                <img :src="imagePreview" alt="Preview" class="max-w-xs rounded-lg border" />
                                <button type="button" @click="removeNewImage"
                                    class="ml-2 text-red-600 hover:text-red-800 text-sm">
                                    Remover
                                </button>
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit" :disabled="loading || isValidating" class="btn btn-primary">
                                <span v-if="loading">Salvando...</span>
                                <span v-else>Salvar Alterações</span>
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
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePostsStore } from '@/stores/posts'
import { useFormValidation, validationRules } from '@/composables/useFormValidation'
import { useNotifications } from '@/composables/useNotifications'
import { toDateTimeLocalString } from '@/utils/dateUtils'

const route = useRoute()
const router = useRouter()
const postsStore = usePostsStore()
const { notifyApiResponse } = useNotifications()

const props = defineProps<{
    id: number
}>()

const loading = computed(() => postsStore.loading)
const currentPost = computed(() => postsStore.currentPost)

const imagePreview = ref<string | null>(null)
const removeCurrentImageFlag = ref(false)

const initialData = {
    title: '',
    content: '',
    scheduled_for: '',
    image: undefined as File | undefined
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

const removeNewImage = (): void => {
    formData.image = undefined
    imagePreview.value = null
    const input = document.getElementById('image') as HTMLInputElement
    if (input) {
        input.value = ''
    }
}

const removeCurrentImage = (): void => {
    removeCurrentImageFlag.value = true
}

const handleSubmit = async (): Promise<void> => {
    const validation = await validate()

    if (!validation.isValid) {
        return
    }

    if (!currentPost.value) return

    const updateData: any = {
        title: formData.title,
        content: formData.content,
        scheduled_for: formData.scheduled_for
    }

    if (formData.image) {
        updateData.image = formData.image
    }

    if (removeCurrentImageFlag.value) {
        updateData.remove_image = true
    }

    const result = await postsStore.updatePost(currentPost.value.id, updateData)
    notifyApiResponse(result)

    if (result.success) {
        await router.push('/posts')
    } else {
        if ('errors' in result && result.errors) {
            setErrors(result.errors)
        }
    }
}

onMounted(async () => {
    const result = await postsStore.fetchPost(props.id)

    if (result.success && currentPost.value) {
        formData.title = currentPost.value.title
        formData.content = currentPost.value.content
        formData.scheduled_for = toDateTimeLocalString(currentPost.value.scheduled_for) || ''
        formData.image = undefined
    }
})
</script>