import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type {
  User,
  LoginCredentials,
  RegisterData,
  AuthResponse,
  ApiResponse,
  ApiSuccess,
  ApiError,
} from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<User | null>(
    localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')!) : null,
  )
  const loading = ref<boolean>(false)

  const isAuthenticated = computed<boolean>(() => !!token.value)
  const userName = computed<string>(() => user.value?.name || '')
  const userEmail = computed<string>(() => user.value?.email || '')

  const login = async (
    credentials: LoginCredentials,
  ): Promise<ApiSuccess<AuthResponse> | ApiError> => {
    loading.value = true
    try {
      const { data } = await api.post('/login', credentials)

      if (!data.access_token || !data.user) {
        return {
          success: false,
          message: data.status || 'Erro ao fazer login',
        }
      }

      token.value = data.access_token
      user.value = data.user

      localStorage.setItem('token', data.access_token)
      localStorage.setItem('user', JSON.stringify(data.user))

      return {
        success: true,
        data: data,
        message: data.status || 'Login realizado com sucesso!',
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao fazer login',
        errors: error.errors || {},
      }
    } finally {
      loading.value = false
    }
  }

  const register = async (userData: RegisterData): Promise<ApiSuccess<AuthResponse> | ApiError> => {
    loading.value = true
    try {
      const { data } = await api.post('/register', userData)

      if (!data.access_token || !data.user) {
        return {
          success: false,
          message: 'Erro ao registrar',
        }
      }

      token.value = data.access_token
      user.value = data.user

      localStorage.setItem('token', data.access_token)
      localStorage.setItem('user', JSON.stringify(data.user))

      return {
        success: true,
        data: data,
        message: 'Registro realizado com sucesso!',
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao registrar',
        errors: error.errors || {},
      }
    } finally {
      loading.value = false
    }
  }

  const logout = async (): Promise<void> => {
    try {
      await api.post('/logout')
    } catch (error) {
      console.warn('Erro ao fazer logout no servidor:', error)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  const fetchUser = async (): Promise<ApiSuccess<User> | ApiError> => {
    if (!token.value) {
      return {
        success: false,
        message: 'Token não encontrado',
      }
    }

    try {
      const { data } = await api.get<ApiResponse<User>>('/user')

      if (!data.success || !data.data) {
        await logout()
        return {
          success: false,
          message: data.message || 'Erro ao buscar usuário',
        }
      }

      user.value = data.data
      localStorage.setItem('user', JSON.stringify(data.data))

      return {
        success: true,
        data: data.data,
      }
    } catch (error: any) {
      await logout()
      return {
        success: false,
        message: error.message || 'Erro ao buscar usuário',
      }
    }
  }

  const updateProfile = async (
    profileData: Partial<User>,
  ): Promise<ApiSuccess<User> | ApiError> => {
    loading.value = true
    try {
      const { data } = await api.put<ApiResponse<User>>('/profile', profileData)

      if (!data.success || !data.data) {
        return {
          success: false,
          message: data.message || 'Erro ao atualizar perfil',
        }
      }

      user.value = data.data
      localStorage.setItem('user', JSON.stringify(data.data))

      return {
        success: true,
        data: data.data,
        message: 'Perfil atualizado com sucesso!',
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao atualizar perfil',
        errors: error.errors || {},
      }
    } finally {
      loading.value = false
    }
  }

  return {
    token,
    user,
    loading,
    isAuthenticated,
    userName,
    userEmail,
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
  }
})
