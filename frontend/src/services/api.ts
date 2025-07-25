import axios, { AxiosResponse, AxiosError, InternalAxiosRequestConfig } from 'axios'
import type { ApiResponse } from '@/types'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('token')
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error: AxiosError) => {
    return Promise.reject(error)
  },
)

api.interceptors.response.use(
  (response: AxiosResponse<ApiResponse>) => {
    return response
  },
  (error: AxiosError<ApiResponse>) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')

      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login'
      }
    }

    const errorResponse: ApiResponse = {
      success: false,
      message: error.response?.data?.message || error.message || 'Erro desconhecido',
      errors: error.response?.data?.errors || {},
    }

    return Promise.reject(errorResponse)
  },
)

export default api
