import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type {
  Post,
  CreatePostData,
  UpdatePostData,
  PostFilters,
  PostStatus,
  SendLog,
  PaginatedResponse,
  PaginationMeta,
  ApiResponse,
  ApiSuccess,
  ApiError,
} from '@/types'

export const usePostsStore = defineStore('posts', () => {
  const posts = ref<Post[]>([])
  const currentPost = ref<Post | null>(null)
  const loading = ref<boolean>(false)
  const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
    has_more_pages: false,
  })

  const scheduledPosts = computed<Post[]>(() =>
    (posts.value || []).filter((post: Post) => post.status === 'scheduled'),
  )

  const sentPosts = computed<Post[]>(() =>
    (posts.value || []).filter((post: Post) => post.status === 'sent'),
  )

  const failedPosts = computed<Post[]>(() =>
    (posts.value || []).filter((post: Post) => post.status === 'failed'),
  )

  const totalPosts = computed<number>(() => (posts.value || []).length)

  const getPostById = computed(() => {
    return (id: number): Post | undefined =>
      (posts.value || []).find((post: Post) => post.id === id)
  })

  const fetchPosts = async (
    page: number = 1,
    filters: PostFilters = {},
  ): Promise<ApiSuccess<any> | ApiError> => {
    loading.value = true
    try {
      const params: any = { page }

      if (filters.status) params.status = filters.status
      if (filters.search) params.search = filters.search
      if (filters.date_from) params.date_from = filters.date_from
      if (filters.date_to) params.date_to = filters.date_to

      const { data } = await api.get('/posts', { params })

      if (!data.success || !data.data) {
        return {
          success: false,
          message: 'Erro ao buscar posts',
        }
      }

      posts.value = data.data || []

      const currentPage = page
      const perPage = data.per_page || 10
      const total = data.total || 0
      const hasMore = data.has_more_pages || false
      const lastPage =
        total > 0 ? Math.ceil(total / perPage) : hasMore ? currentPage + 1 : currentPage

      pagination.value = {
        current_page: currentPage,
        last_page: lastPage,
        per_page: perPage,
        total: total,
        from: (currentPage - 1) * perPage + 1,
        to: Math.min(currentPage * perPage, total),
        has_more_pages: hasMore,
      }

      return {
        success: true,
        data: data,
      }
    } catch (error: any) {
      posts.value = []
      return {
        success: false,
        message: error.message || 'Erro ao buscar posts',
      }
    } finally {
      loading.value = false
    }
  }

  const fetchPost = async (id: number): Promise<ApiSuccess<Post> | ApiError> => {
    loading.value = true
    try {
      const { data } = await api.get(`/posts/${id}`)

      if (!data.success || !data.data) {
        return {
          success: false,
          message: data.message || 'Erro ao buscar post',
        }
      }

      currentPost.value = data.data
      return {
        success: true,
        data: data.data,
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao buscar post',
      }
    } finally {
      loading.value = false
    }
  }

  const createPost = async (postData: CreatePostData): Promise<ApiSuccess<Post> | ApiError> => {
    loading.value = true
    try {
      const formData = new FormData()

      formData.append('title', postData.title)
      formData.append('content', postData.content)
      formData.append('scheduled_for', postData.scheduled_for)

      if (postData.image) {
        formData.append('image', postData.image)
      }

      const { data } = await api.post('/posts', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })

      if (!data.success || !data.data) {
        return {
          success: false,
          message: data.message || 'Erro ao criar post',
          errors: data.errors || {},
        }
      }

      if (!posts.value) posts.value = []
      posts.value.unshift(data.data)

      return {
        success: true,
        data: data.data,
        message: 'Post criado com sucesso!',
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao criar post',
        errors: error.errors || {},
      }
    } finally {
      loading.value = false
    }
  }

  const updatePost = async (
    id: number,
    postData: Partial<CreatePostData>,
  ): Promise<ApiSuccess<Post> | ApiError> => {
    loading.value = true
    try {
      const formData = new FormData()

      if (postData.title) formData.append('title', postData.title)
      if (postData.content) formData.append('content', postData.content)
      if (postData.scheduled_for) formData.append('scheduled_for', postData.scheduled_for)
      if (postData.image) formData.append('image', postData.image)

      formData.append('_method', 'PUT')

      const { data } = await api.post(`/posts/${id}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })

      if (!data.success || !data.data) {
        return {
          success: false,
          message: data.message || 'Erro ao atualizar post',
          errors: data.errors || {},
        }
      }

      const index = (posts.value || []).findIndex((post: Post) => post.id === id)
      if (index !== -1) {
        posts.value[index] = data.data
      }

      currentPost.value = data.data

      return {
        success: true,
        data: data.data,
        message: 'Post atualizado com sucesso!',
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao atualizar post',
        errors: error.errors || {},
      }
    } finally {
      loading.value = false
    }
  }

  const deletePost = async (id: number): Promise<ApiSuccess | ApiError> => {
    loading.value = true
    try {
      const { data } = await api.delete(`/posts/${id}`)

      if (!data.success) {
        return {
          success: false,
          message: data.message || 'Erro ao deletar post',
        }
      }

      posts.value = (posts.value || []).filter((post: Post) => post.id !== id)

      if (currentPost.value?.id === id) {
        currentPost.value = null
      }

      return {
        success: true,
        message: 'Post deletado com sucesso!',
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao deletar post',
      }
    } finally {
      loading.value = false
    }
  }

  const fetchSendLogs = async (postId?: number): Promise<ApiSuccess<SendLog[]> | ApiError> => {
    loading.value = true
    try {
      const url = postId ? `/posts/${postId}/logs` : '/send-logs'
      const { data } = await api.get(url)

      if (!data.success || !data.data) {
        return {
          success: false,
          message: data.message || 'Erro ao buscar logs',
        }
      }

      return {
        success: true,
        data: data.data,
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao buscar logs',
      }
    } finally {
      loading.value = false
    }
  }

  const retrySendPost = async (id: number): Promise<ApiSuccess<Post> | ApiError> => {
    loading.value = true
    try {
      const { data } = await api.post(`/posts/${id}/retry`)

      if (!data.success || !data.data) {
        return {
          success: false,
          message: data.message || 'Erro ao reenviar post',
        }
      }

      const index = (posts.value || []).findIndex((post: Post) => post.id === id)
      if (index !== -1) {
        posts.value[index] = data.data
      }

      return {
        success: true,
        data: data.data,
        message: 'Post reagendado com sucesso!',
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Erro ao reenviar post',
      }
    } finally {
      loading.value = false
    }
  }

  const updatePostStatus = (id: number, status: PostStatus): void => {
    const index = (posts.value || []).findIndex((post: Post) => post.id === id)
    if (index !== -1) {
      posts.value[index].status = status
    }

    if (currentPost.value?.id === id) {
      currentPost.value.status = status
    }
  }

  return {
    posts,
    currentPost,
    loading,
    pagination,
    scheduledPosts,
    sentPosts,
    failedPosts,
    totalPosts,
    getPostById,
    fetchPosts,
    fetchPost,
    createPost,
    updatePost,
    deletePost,
    fetchSendLogs,
    retrySendPost,
    updatePostStatus,
  }
})
