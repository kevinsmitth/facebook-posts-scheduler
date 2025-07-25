// User types
export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  created_at: string
  updated_at: string
}

// Auth types
export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  user: User
  token: string
}

// Post types
export interface Post {
  id: number
  title: string
  content: string
  image_url?: string
  scheduled_for: string
  status: PostStatus
  created_at: string
  updated_at: string
  user_id: number
  sent_at?: string
  error_message?: string
}

export type PostStatus = 'scheduled' | 'sent' | 'failed'

export interface CreatePostData {
  title: string
  content: string
  image?: File
  scheduled_for: string
}

export interface UpdatePostData extends Partial<CreatePostData> {
  id: number
}

// Send Log types
export interface SendLog {
  id: number
  post_id: number
  status: 'success' | 'failed'
  response_data?: Record<string, any>
  error_message?: string
  sent_at: string
  created_at: string
  post?: Post
}

// Pagination types
export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

// API Response types
export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  errors?: Record<string, string[]>
}

export interface ApiError {
  success: false
  message: string
  errors?: Record<string, string[]>
}

export interface ApiSuccess<T = any> {
  success: true
  data?: T
  message?: string
}

// Notification types
export type NotificationType = 'success' | 'error' | 'warning' | 'info'

export interface Notification {
  id: number
  type: NotificationType
  title: string
  message: string
  duration: number
}

export interface NotificationInput {
  type?: NotificationType
  title?: string
  message: string
  duration?: number
}

// Filter types
export interface PostFilters {
  status?: PostStatus
  search?: string
  date_from?: string
  date_to?: string
}

// Twitter API types
export interface TwitterAuthConfig {
  client_id: string
  client_secret: string
  redirect_uri: string
}

export interface TwitterTokenResponse {
  access_token: string
  refresh_token?: string
  expires_in: number
  token_type: string
}

// Form validation types
export interface ValidationErrors {
  [key: string]: string[]
}

export interface FormError {
  field: string
  message: string
}
