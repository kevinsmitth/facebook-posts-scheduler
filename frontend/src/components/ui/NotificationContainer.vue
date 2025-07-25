<template>
    <Teleport to="body">
        <div v-if="notifications.length > 0" class="fixed top-0 right-0 p-6 space-y-4 z-50 pointer-events-none">
            <TransitionGroup name="notification" tag="div" class="space-y-4">
                <div v-for="notification in notifications" :key="notification.id"
                    :class="getNotificationClass(notification.type)"
                    class="w-80 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div :class="getIconClass(notification.type)"
                                    class="h-6 w-6 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                    {{ getIconText(notification.type) }}
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ notification.title }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ notification.message }}
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <button @click="removeNotification(notification.id)"
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 p-1">
                                    <span class="sr-only">Fechar</span>
                                    <span class="text-lg leading-none">×</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { useNotifications } from "@/composables/useNotifications";
import type { NotificationType } from "@/types";

const { notifications, removeNotification } = useNotifications();

const getNotificationClass = (type: NotificationType): string => {
    const classes = {
        success: "bg-white border-l-4 border-green-400",
        error: "bg-white border-l-4 border-red-400",
        warning: "bg-white border-l-4 border-yellow-400",
        info: "bg-white border-l-4 border-blue-400",
    };
    return classes[type];
};

const getIconClass = (type: NotificationType): string => {
    const classes = {
        success: "bg-green-500",
        error: "bg-red-500",
        warning: "bg-yellow-500",
        info: "bg-blue-500",
    };
    return classes[type];
};

const getIconText = (type: NotificationType): string => {
    const icons = {
        success: "✓",
        error: "!",
        warning: "⚠",
        info: "i",
    };
    return icons[type];
};
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s ease;
}

.notification-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.notification-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.notification-move {
    transition: transform 0.3s ease;
}
</style>
