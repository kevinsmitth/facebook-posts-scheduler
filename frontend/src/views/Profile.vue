<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4">
                    <router-link to="/dashboard" class="text-gray-400 hover:text-gray-600">
                        ← Dashboard
                    </router-link>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Perfil
                    </h1>
                </div>
            </div>
        </header>

        <main class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-medium text-gray-900">
                            Informações Pessoais
                        </h2>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="handleSubmit" class="space-y-4">
                            <div>
                                <label for="name" class="form-label">
                                    Nome completo
                                </label>
                                <input id="name" v-model="formData.name" type="text" required class="form-input"
                                    :class="{ 'border-red-500': hasFieldError('name') }" />
                                <p v-if="getFieldError('name')" class="form-error">
                                    {{ getFieldError('name') }}
                                </p>
                            </div>

                            <div>
                                <label for="email" class="form-label">
                                    E-mail
                                </label>
                                <input id="email" v-model="formData.email" type="email" required class="form-input"
                                    :class="{ 'border-red-500': hasFieldError('email') }" />
                                <p v-if="getFieldError('email')" class="form-error">
                                    {{ getFieldError('email') }}
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" :disabled="loading || isValidating" class="btn-primary">
                                    <span v-if="loading">Salvando...</span>
                                    <span v-else>Salvar Alterações</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-medium text-gray-900">
                            Alterar Senha
                        </h2>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="handlePasswordSubmit" class="space-y-4">
                            <div>
                                <label for="current_password" class="form-label">
                                    Senha atual
                                </label>
                                <input id="current_password" v-model="passwordData.current_password" type="password"
                                    required class="form-input"
                                    :class="{ 'border-red-500': hasPasswordFieldError('current_password') }" />
                                <p v-if="getPasswordFieldError('current_password')" class="form-error">
                                    {{ getPasswordFieldError('current_password') }}
                                </p>
                            </div>

                            <div>
                                <label for="new_password" class="form-label">
                                    Nova senha
                                </label>
                                <input id="new_password" v-model="passwordData.new_password" type="password" required
                                    class="form-input"
                                    :class="{ 'border-red-500': hasPasswordFieldError('new_password') }" />
                                <p v-if="getPasswordFieldError('new_password')" class="form-error">
                                    {{ getPasswordFieldError('new_password') }}
                                </p>
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="form-label">
                                    Confirmar nova senha
                                </label>
                                <input id="new_password_confirmation" v-model="passwordData.new_password_confirmation"
                                    type="password" required class="form-input"
                                    :class="{ 'border-red-500': hasPasswordFieldError('new_password_confirmation') }" />
                                <p v-if="getPasswordFieldError('new_password_confirmation')" class="form-error">
                                    {{ getPasswordFieldError('new_password_confirmation') }}
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" :disabled="passwordLoading || isPasswordValidating"
                                    class="btn-primary">
                                    <span v-if="passwordLoading">Alterando...</span>
                                    <span v-else>Alterar Senha</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-medium text-gray-900">
                            Integração com Twitter
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">
                                    Status da conexão com Twitter
                                </p>
                                <p class="text-lg font-medium text-green-600">
                                    ✓ Conectado
                                </p>
                            </div>
                            <button class="btn-outline">
                                Reconectar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card border-red-200">
                    <div class="card-header bg-red-50">
                        <h2 class="text-lg font-medium text-red-900">
                            Zona de Perigo
                        </h2>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-gray-600 mb-4">
                            Uma vez que você deletar sua conta, não há como voltar atrás. Por favor, tenha certeza.
                        </p>
                        <button @click="confirmDeleteAccount" class="btn bg-red-600 text-white hover:bg-red-700">
                            Deletar Conta
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useFormValidation, validationRules } from '@/composables/useFormValidation'
import { useNotifications } from '@/composables/useNotifications'
import type { User } from '@/types'

const authStore = useAuthStore()
const { notifyApiResponse, notifyError } = useNotifications()

const loading = ref(false)
const passwordLoading = ref(false)

const formData = ref({
    name: '',
    email: ''
})

const passwordData = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
})

const profileRules = {
    name: [
        validationRules.required('Nome é obrigatório'),
        validationRules.minLength(2, 'Nome deve ter pelo menos 2 caracteres')
    ],
    email: [
        validationRules.required('E-mail é obrigatório'),
        validationRules.email()
    ]
}

const passwordConfirmationRule = computed(() =>
    validationRules.confirmed(
        passwordData.value.new_password,
        'As senhas não coincidem'
    )
)

const passwordRules = {
    current_password: [
        validationRules.required('Senha atual é obrigatória')
    ],
    new_password: [
        validationRules.required('Nova senha é obrigatória'),
        validationRules.minLength(6, 'Nova senha deve ter pelo menos 6 caracteres')
    ],
    new_password_confirmation: [
        validationRules.required('Confirmação de senha é obrigatória'),
        passwordConfirmationRule
    ]
}

const {
    isValidating,
    validate,
    setErrors,
    getFieldError,
    hasFieldError
} = useFormValidation(formData.value, profileRules)

const {
    isValidating: isPasswordValidating,
    validate: validatePassword,
    setErrors: setPasswordErrors,
    getFieldError: getPasswordFieldError,
    hasFieldError: hasPasswordFieldError,
    resetForm: resetPasswordForm
} = useFormValidation(passwordData.value, passwordRules)

const handleSubmit = async (): Promise<void> => {
    const validation = await validate()

    if (!validation.isValid) {
        return
    }

    loading.value = true
    try {
        const result = await authStore.updateProfile(formData.value)
        notifyApiResponse(result)

        if (!result.success && 'errors' in result && result.errors) {
            setErrors(result.errors)
        }
    } finally {
        loading.value = false
    }
}

const handlePasswordSubmit = async (): Promise<void> => {
    const validation = await validatePassword()

    if (!validation.isValid) {
        return
    }

    passwordLoading.value = true
    try {
        notifyError('Funcionalidade de alteração de senha será implementada em breve')
        resetPasswordForm()
    } finally {
        passwordLoading.value = false
    }
}

const confirmDeleteAccount = (): void => {
    if (confirm('Tem certeza que deseja deletar sua conta? Esta ação não pode ser desfeita.')) {
        notifyError('Funcionalidade de exclusão de conta será implementada em breve')
    }
}

onMounted(() => {
    if (authStore.user) {
        formData.value = {
            name: authStore.user.name,
            email: authStore.user.email
        }
    }
})
</script>