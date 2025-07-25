import { ref, readonly } from 'vue'
import type { Notification, NotificationInput, NotificationType } from '@/types'

const notifications = ref<Notification[]>([])

export function useNotifications() {
  const addNotification = (input: NotificationInput): number => {
    const id = Date.now()
    const notification: Notification = {
      id,
      type: input.type || 'info',
      title: input.title || getDefaultTitle(input.type || 'info'),
      message: input.message,
      duration: input.duration || 5000,
    }

    notifications.value.push(notification)

    if (notification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, notification.duration)
    }

    return id
  }

  const removeNotification = (id: number): void => {
    const index = notifications.value.findIndex((n: Notification) => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const clearAllNotifications = (): void => {
    notifications.value = []
  }

  const getDefaultTitle = (type: NotificationType): string => {
    const titles: Record<NotificationType, string> = {
      success: 'Sucesso',
      error: 'Erro',
      warning: 'Atenção',
      info: 'Informação',
    }
    return titles[type]
  }

  const notifySuccess = (message: string, title?: string): number => {
    return addNotification({
      type: 'success',
      title: title || 'Sucesso',
      message,
      duration: 4000,
    })
  }

  const notifyError = (message: string, title?: string): number => {
    return addNotification({
      type: 'error',
      title: title || 'Erro',
      message,
    })
  }

  const notifyWarning = (message: string, title?: string): number => {
    return addNotification({
      type: 'warning',
      title: title || 'Atenção',
      message,
      duration: 6000,
    })
  }

  const notifyInfo = (message: string, title?: string): number => {
    return addNotification({
      type: 'info',
      title: title || 'Informação',
      message,
      duration: 5000,
    })
  }

  const notifyApiResponse = (response: { success: boolean; message?: string }): number => {
    if (response.success) {
      return notifySuccess(response.message || 'Operação realizada com sucesso!')
    } else {
      return notifyError(response.message || 'Ocorreu um erro inesperado')
    }
  }

  return {
    notifications: readonly(notifications),
    addNotification,
    removeNotification,
    clearAllNotifications,
    notifySuccess,
    notifyError,
    notifyWarning,
    notifyInfo,
    notifyApiResponse,
  }
}
