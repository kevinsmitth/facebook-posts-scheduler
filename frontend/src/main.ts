import { createApp } from 'vue'
import { createPinia } from 'pinia'
import type { App } from 'vue'

import AppComponent from './App.vue'
import router from './router'
import './style.css'

const app: App<Element> = createApp(AppComponent)

const pinia = createPinia()

app.use(pinia)
app.use(router)

if (import.meta.env.DEV) {
  app.config.performance = true
  app.config.devtools = true
}

if (import.meta.env.PROD) {
  app.config.warnHandler = () => null
}

app.config.errorHandler = (error: unknown, instance, info) => {
  console.error('Erro global capturado:', error)
  console.error('Instância:', instance)
  console.error('Info:', info)

  if (import.meta.env.PROD) {
  }
}

app.mount('#app')
