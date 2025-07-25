<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Crie sua conta
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Ou
                    <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                        entre na sua conta existente
                    </router-link>
                </p>
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                <div>
                    <label for="name" class="form-label">
                        Nome completo
                    </label>
                    <input id="name" v-model="formData.name" type="text" required class="form-input"
                        :class="{ 'border-red-500': hasFieldError('name') }" placeholder="Seu nome completo" />
                    <p v-if="getFieldError('name')" class="form-error">
                        {{ getFieldError('name') }}
                    </p>
                </div>

                <div>
                    <label for="email" class="form-label">
                        E-mail
                    </label>
                    <input id="email" v-model="formData.email" type="email" required class="form-input"
                        :class="{ 'border-red-500': hasFieldError('email') }" placeholder="seu@email.com" />
                    <p v-if="getFieldError('email')" class="form-error">
                        {{ getFieldError('email') }}
                    </p>
                </div>

                <div>
                    <label for="password" class="form-label">
                        Senha
                    </label>
                    <input id="password" v-model="formData.password" type="password" required class="form-input"
                        :class="{ 'border-red-500': hasFieldError('password') }" placeholder="Mínimo 6 caracteres" />
                    <p v-if="getFieldError('password')" class="form-error">
                        {{ getFieldError('password') }}
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">
                        Confirmar senha
                    </label>
                    <input id="password_confirmation" v-model="formData.password_confirmation" type="password" required
                        class="form-input" :class="{ 'border-red-500': hasFieldError('password_confirmation') }"
                        placeholder="Digite a senha novamente" />
                    <p v-if="getFieldError('password_confirmation')" class="form-error">
                        {{ getFieldError('password_confirmation') }}
                    </p>
                </div>

                <div>
                    <button type="submit" :disabled="isValidating || authStore.loading" class="btn btn-primary w-full">
                        <span v-if="authStore.loading">Criando conta...</span>
                        <span v-else>Criar conta</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFormValidation, validationRules } from '@/composables/useFormValidation'
import { useNotifications } from '@/composables/useNotifications'
import type { RegisterData } from '@/types'

const authStore = useAuthStore()
const router = useRouter()
const { notifyApiResponse } = useNotifications()

const initialData: RegisterData = {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
}

const rules = {
    name: [
        validationRules.required('Nome é obrigatório'),
        validationRules.minLength(2, 'Nome deve ter pelo menos 2 caracteres')
    ],
    email: [
        validationRules.required('E-mail é obrigatório'),
        validationRules.email()
    ],
    password: [
        validationRules.required('Senha é obrigatória'),
        validationRules.minLength(6, 'Senha deve ter pelo menos 6 caracteres')
    ],
    password_confirmation: [
        validationRules.required('Confirmação de senha é obrigatória'),
        (value) => validationRules.confirmed(formData.password, 'As senhas não coincidem')(value)
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

const handleSubmit = async (): Promise<void> => {
    const validation = await validate()

    if (!validation.isValid) {
        return
    }

    const result = await authStore.register(formData)

    notifyApiResponse(result)

    if (result.success) {
        await router.push('/dashboard')
    } else {
        if ('errors' in result && result.errors) {
            setErrors(result.errors)
        }
    }
}
</script>