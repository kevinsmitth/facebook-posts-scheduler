# Facebook Posts Scheduler Frontend

Interface web moderna para agendamento automático de postagens no Facebook. Projeto de portfólio desenvolvido com Vue.js 3 + TypeScript + Tailwind CSS.

## 🚀 Começando

Esta aplicação web permite aos usuários fazer login, criar posts, agendar publicações e acompanhar o status das postagens no Facebook através de uma interface moderna e responsiva.

Consulte **Instalação** para saber como executar o projeto.

### 📋 Pré-requisitos

- Node.js ^20
- NPM ^10
- Docker & Docker Compose
- Backend da API rodando na porta 8080

### 🔧 Instalação

Dentro da pasta do projeto frontend, rode os comandos na ordem abaixo.

```bash
cp .env.example .env
```

```bash
docker-compose up -d
```

Aguarde alguns segundos para o Vite inicializar, então acesse a aplicação:

**Frontend:** [http://localhost:5174](http://localhost:5174)

### ⚙️ Configuração

Configure as variáveis de ambiente no arquivo `.env`:

```env
VITE_APP_NAME=Posts Scheduler
VITE_API_URL=http://localhost:8080/api
```

A aplicação se conecta automaticamente com a API backend rodando na porta 8080.

### 🎨 Interface do Usuário

A aplicação possui as seguintes telas principais:

- **Login/Register** - Autenticação de usuários
- **Dashboard** - Visão geral dos posts
- **Criar Post** - Interface para criar e agendar postagens
- **Logs** - Acompanhar status de envios
- **Perfil** - Gerenciar dados do usuário

## 🛠️ Desenvolvimento

### Comandos disponíveis:

**Instalar dependências:**

```bash
npm install
```

**Executar em modo desenvolvimento:**

```bash
npm run dev
```

**Build para produção:**

```bash
npm run build
```

**Preview da build de produção:**

```bash
npm run preview
```

**Verificação de tipos TypeScript:**

```bash
npm run type-check
```

**Linting e formatação:**

```bash
npm run lint
npm run format
```

### 🔧 Comandos Docker:

**Acessar container:**

```bash
docker-compose exec frontend sh
```

**Ver logs do Vite:**

```bash
docker-compose exec frontend npm run dev
```

**Rebuild container:**

```bash
docker-compose build --no-cache frontend
```

## 🏗️ Construído com

- [Vue.js 3.5.18](https://vuejs.org/) - Framework JavaScript progressivo
- [Vite 7.0.0](https://vitejs.dev/) - Build tool extremamente rápido
- [TypeScript 5.8](https://www.typescriptlang.org/) - Tipagem estática
- [Tailwind CSS 4.1.11](https://tailwindcss.com/) - Framework CSS utility-first
- [Pinia 3.0.3](https://pinia.vuejs.org/) - Gerenciamento de estado oficial do Vue
- [Vue Router 4.5.1](https://router.vuejs.org/) - Roteamento oficial do Vue
- [Axios 1.11.0](https://axios-http.com/) - Cliente HTTP para requisições à API
- [date-fns 4.1.0](https://date-fns.org/) - Biblioteca para manipulação de datas
- [@vueuse/core 13.5.0](https://vueuse.org/) - Coleção de composables do Vue

## 📁 Estrutura do Projeto

```
frontend/
├── src/
│   ├── components/          # Componentes reutilizáveis
│   ├── views/              # Páginas da aplicação
│   ├── stores/             # Stores do Pinia (estado global)
│   ├── router/             # Configuração das rotas
│   ├── services/           # Services para API
│   ├── types/              # Tipos TypeScript
│   ├── assets/             # Assets estáticos
│   └── main.ts             # Ponto de entrada da aplicação
├── public/                 # Arquivos públicos
├── Dockerfile              # Container do Vue.js
├── vite.config.js          # Configuração do Vite
├── tailwind.config.js      # Configuração do Tailwind
└── tsconfig.json           # Configuração do TypeScript
```

## 🎯 Funcionalidades

- **SPA (Single Page Application)** - Navegação fluida sem recarregamento
- **Autenticação** - Login/logout com tokens JWT via Sanctum
- **Formulários reativos** - Validação em tempo real
- **Gerenciamento de estado** - Pinia para estado global
- **Responsivo** - Design adaptável para mobile/desktop
- **TypeScript** - Tipagem estática para maior confiabilidade
- **Hot Reload** - Desenvolvimento com atualização instantânea
- **Code Splitting** - Carregamento otimizado de recursos

## 📱 Design Responsivo

A aplicação foi desenvolvida com **mobile-first** usando Tailwind CSS:

- **Mobile** (320px+) - Interface otimizada para smartphones
- **Tablet** (768px+) - Layout adaptado para tablets
- **Desktop** (1024px+) - Interface completa para desktops
- **Wide** (1280px+) - Aproveitamento de telas grandes

## 🔄 Integração com API

A aplicação consome a API backend através do Axios com:

- **Interceptors** para autenticação automática
- **Error handling** centralizado
- **Loading states** para melhor UX
- **Retry automático** em caso de falhas temporárias

### Exemplo de service:

```typescript
// services/api.ts
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 10000,
})

export default api
```

## 🚀 Deploy

Para produção:

1. Configure `VITE_API_URL` com a URL da API em produção
2. Execute `npm run build`
3. Sirva os arquivos da pasta `dist/` em um servidor web
4. Configure HTTPS para segurança
5. Configure CDN para melhor performance

## 💡 IDE Recomendado

**VSCode** com as seguintes extensões:

- [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) - Suporte ao Vue 3
- [TypeScript Vue Plugin](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin) - Melhor integração TS
- [Tailwind CSS IntelliSense](https://marketplace.visualstudio.com/items?itemName=bradlc.vscode-tailwindcss) - Autocomplete do Tailwind
- [ESLint](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint) - Linting
- [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode) - Formatação

## ✒️ Autor

**Kevin Smith** - _Desenvolvimento_ - [LinkedIn](https://www.linkedin.com/in/kevin-smith-130a04154/)

## 📄 Licença

Este projeto está sob a licença MIT - veja o arquivo [LICENSE](../LICENSE) para detalhes.
