<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Entre na sua conta
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Ou
                    <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
                        crie uma conta gratuita
                    </router-link>
                </p>
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
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
                        :class="{ 'border-red-500': hasFieldError('password') }" placeholder="Sua senha" />
                    <p v-if="getFieldError('password')" class="form-error">
                        {{ getFieldError('password') }}
                    </p>
                </div>

                <div>
                    <button type="submit" :disabled="isValidating || authStore.loading" class="btn btn-primary w-full">
                        <span v-if="authStore.loading">Entrando...</span>
                        <span v-else>Entrar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFormValidation, validationRules } from '@/composables/useFormValidation'
import { useNotifications } from '@/composables/useNotifications'
import type { LoginCredentials } from '@/types'

const authStore = useAuthStore()
const router = useRouter()
const { notifyApiResponse } = useNotifications()

const initialData: LoginCredentials = {
    email: '',
    password: ''
}

const rules = {
    email: [
        validationRules.required('E-mail é obrigatório'),
        validationRules.email()
    ],
    password: [
        validationRules.required('Senha é obrigatória'),
        validationRules.minLength(6)
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

    const result = await authStore.login(formData)

    notifyApiResponse(result)

    if (result.success) {
        const redirect = router.currentRoute.value.query.redirect as string
        await router.push(redirect || '/dashboard')
    } else {
        if ('errors' in result && result.errors) {
            setErrors(result.errors)
        }
    }
}
</script>